<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sale::with(['user', 'customer', 'cashSession', 'delivery']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
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
        $cashiers = User::where('role', 'caissier')->get();

        $stats = [
            'total_sales' => Sale::count(),
            'total_revenue' => (float) Sale::where('status', 'completed')->sum('total_amount'),
            'completed' => Sale::where('status', 'completed')->count(),
            'cancelled' => Sale::where('status', 'cancelled')->count(),
        ];

        return view('admin.sales.index', compact('sales', 'cashiers', 'stats'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'customer', 'cashSession.cashRegister', 'items.product', 'delivery.livreur']);

        return view('admin.sales.show', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:3'],
        ], [
            'cancellation_reason.required' => 'Le motif d\'annulation est obligatoire.',
        ]);

        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Cette vente est déjà annulée.');
        }

        DB::transaction(function () use ($sale, $request) {
            $sale->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => auth()->id(),
            ]);

            // Réinjecter le stock des articles vendus
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $prod = $item->product;
                    if ($prod) {
                        $prevStock = $prod->stock_quantity;
                        $prod->increment('stock_quantity', $item->quantity);

                        StockMovement::create([
                            'product_id' => $prod->id,
                            'user_id' => auth()->id(),
                            'type' => 'return',
                            'quantity' => $item->quantity,
                            'previous_stock' => $prevStock,
                            'new_stock' => $prod->stock_quantity,
                            'reason' => 'Annulation vente '.$sale->sale_number.': '.$request->cancellation_reason,
                            'reference' => 'ANNUL-'.$sale->sale_number,
                        ]);
                    }
                }
            }

            ActivityLog::log(
                'vente_annulee',
                "Annulation de la vente {$sale->sale_number} ({$sale->total_amount} FCFA) par l'administrateur. Motif : {$request->cancellation_reason}"
            );
        });

        return back()->with('success', "La vente {$sale->sale_number} a été annulée et les stocks ont été réajustés.");
    }
}
