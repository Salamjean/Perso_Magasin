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

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Sale::where('user_id', $user->id)->with(['customer', 'items']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('firstname', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $sales = $query->latest()->paginate(15)->withQueryString();

        return view('caissier.sales.index', compact('sales'));
    }

    public function show(Sale $sale): View
    {
        // Autoriser si le caissier est l'auteur ou admin
        if (auth()->user()->role !== 'admin' && $sale->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette vente.');
        }

        $sale->load(['user', 'customer', 'items.product', 'cashSession.cashRegister']);

        return view('caissier.sales.show', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $user = auth()->user();

        // Vérifier que le caissier annule sa propre vente (sauf si admin)
        if ($user->role !== 'admin' && $sale->user_id !== $user->id) {
            return back()->with('error', 'Vous ne pouvez annuler que les ventes que vous avez vous-même enregistrées.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:3'],
        ], [
            'cancellation_reason.required' => 'Le motif d\'annulation est obligatoire.',
            'cancellation_reason.min' => 'Le motif d\'annulation doit comporter au moins 3 caractères.',
        ]);

        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Cette vente est déjà annulée.');
        }

        DB::transaction(function () use ($sale, $validated, $user) {
            $reason = $validated['cancellation_reason'];

            // 1. Mise à jour de la vente
            $sale->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $user->id,
            ]);

            // 2. Réintégration des stocks
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
                            'reason' => "Annulation vente #{$sale->sale_number} : {$reason}",
                            'reference' => 'ANNUL-'.$sale->sale_number,
                        ]);
                    }
                }
            }

            // 3. Ajustement de caisse si session ouverte et espèces perçues
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

            // 4. Si un crédit avait été accordé au client, décrémenter sa dette
            $isFullCredit = ($sale->payment_method === 'credit');
            $creditAmount = $isFullCredit ? (float) $sale->total_amount : (float) ($sale->credit_amount ?? 0);

            if ($creditAmount > 0 && $sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $customer->decrement('debt_balance', min((float) $customer->debt_balance, $creditAmount));
                }
            }

            // 5. Journalisation d'activité
            ActivityLog::log(
                'vente_annulee',
                "Annulation de la vente {$sale->sale_number} ({$sale->total_amount} FCFA) par le caissier {$user->full_name}. Motif : {$reason}"
            );
        });

        return back()->with('success', "La vente {$sale->sale_number} a été annulée avec succès et les stocks ont été remis à jour.");
    }
}
