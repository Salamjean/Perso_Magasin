<?php

namespace App\Http\Controllers\Caissier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $recentSales = Sale::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['customer', 'items'])
            ->latest()
            ->paginate(10);

        return view('caissier.returns.index', compact('recentSales'));
    }

    public function cancelSale(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'sale_number' => ['required', 'string'],
            'reason' => ['required', 'string', 'min:3'],
        ], [
            'sale_number.required' => 'Le numéro de vente est obligatoire.',
            'reason.required' => 'Le motif d\'annulation est obligatoire.',
            'reason.min' => 'Le motif d\'annulation doit comporter au moins 3 caractères.',
        ]);

        $sale = Sale::where('sale_number', trim($validated['sale_number']))
            ->orWhere('invoice_number', trim($validated['sale_number']))
            ->first();

        if (! $sale) {
            return back()->with('error', 'Aucun ticket ou vente trouvé avec ce numéro.');
        }

        // Vérifier les droits si caissier
        if ($user->role !== 'admin' && $sale->user_id !== $user->id) {
            return back()->with('error', 'Vous ne pouvez annuler que vos propres ventes.');
        }

        if ($sale->status === 'cancelled') {
            return back()->with('error', "La vente {$sale->sale_number} est déjà annulée.");
        }

        DB::transaction(function () use ($sale, $validated, $user) {
            $reason = $validated['reason'];

            // 1. Mise à jour statut
            $sale->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $user->id,
            ]);

            // 2. Réinjecter les articles en stock
            $sale->loadMissing('items.product');
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $prod = $item->product;
                    if ($prod) {
                        $prevStock = (float) $prod->stock_quantity;
                        $prod->increment('stock_quantity', $item->quantity);
                        $prod->refresh();

                        StockMovement::create([
                            'product_id' => $prod->id,
                            'user_id' => $user->id,
                            'type' => 'return',
                            'quantity' => $item->quantity,
                            'previous_stock' => $prevStock,
                            'new_stock' => $prod->stock_quantity,
                            'reason' => "Annulation vente #{$sale->sale_number} par caissier : {$reason}",
                            'reference' => 'ANNUL-'.$sale->sale_number,
                        ]);
                    }
                }
            }

            // 3. Ajustement tiroir caisse si session ouverte et espèces perçues
            $session = $sale->cashSession;
            if ($session && $session->status === 'open') {
                $isFullCredit = ($sale->payment_method === 'credit');
                $creditAmount = $isFullCredit ? (float) $sale->total_amount : (float) ($sale->credit_amount ?? 0);

                if (! $isFullCredit && $sale->payment_method === 'cash') {
                    $effectiveCashIn = max(0, (float) $sale->total_amount - $creditAmount);

                    if ($effectiveCashIn > 0) {
                        $session->decrement('closing_amount_theory', $effectiveCashIn);

                        CashMovement::create([
                            'cash_session_id' => $session->id,
                            'user_id' => $user->id,
                            'type' => 'out',
                            'amount' => $effectiveCashIn,
                            'reason' => "Remboursement suite à annulation vente #{$sale->sale_number} : {$reason}",
                        ]);
                    }
                }
            }

            // 4. Décrémentation de la dette client si crédit accordé
            $isFullCredit = ($sale->payment_method === 'credit');
            $creditAmount = $isFullCredit ? (float) $sale->total_amount : (float) ($sale->credit_amount ?? 0);

            if ($creditAmount > 0 && $sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $customer->decrement('debt_balance', min((float) $customer->debt_balance, $creditAmount));
                }
            }

            // 5. Journal d'activité
            ActivityLog::log(
                'vente_annulee',
                "Annulation directe de la vente {$sale->sale_number} ({$sale->total_amount} FCFA) par le caissier {$user->full_name}. Motif : {$reason}"
            );
        });

        return back()->with('success', "La vente {$sale->sale_number} a été annulée avec succès et les stocks ont été réintégrés.");
    }
}
