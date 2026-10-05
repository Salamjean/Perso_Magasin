<?php

namespace App\Http\Controllers\Magasinier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'receivedBy', 'items.product'])
            ->whereIn('status', ['ordered', 'partial', 'received']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest()->paginate(10)->withQueryString();

        return view('magasinier.receptions.index', compact('purchases'));
    }

    public function process(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'receivedBy', 'items.product']);
        $isLocked = $purchase->received_date && ! $purchase->received_date->isToday();

        return view('magasinier.receptions.process', compact('purchase', 'isLocked'));
    }

    public function validateReception(Request $request, Purchase $purchase): RedirectResponse
    {
        // Bloquer la modification si la journée de réception est passée
        if ($purchase->received_date && ! $purchase->received_date->isToday()) {
            return redirect()->route('magasinier.receptions.index')
                ->with('error', 'Modification impossible : le délai de modification de cette réception (jour de réception) est dépassé. Veuillez vous adresser à un administrateur.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $purchase) {
            $allFullyReceived = true;
            $hasAnyReceived = false;

            foreach ($validated['items'] as $itemData) {
                $item = PurchaseItem::findOrFail($itemData['item_id']);
                $newReceivedQty = (float) $itemData['received_qty'];

                // Quantité additionnelle reçue lors de cette livraison
                $deltaQty = $newReceivedQty - $item->quantity_received;

                if ($deltaQty > 0) {
                    $hasAnyReceived = true;
                    $product = $item->product;
                    if ($product) {
                        $prevStock = $product->stock_quantity;
                        $product->increment('stock_quantity', $deltaQty);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'type' => 'in',
                            'quantity' => $deltaQty,
                            'previous_stock' => $prevStock,
                            'new_stock' => $product->stock_quantity,
                            'reason' => 'Réception approvisionnement '.$purchase->reference,
                            'reference' => 'RECEP-'.$purchase->reference,
                        ]);
                    }
                }

                $item->update(['quantity_received' => $newReceivedQty]);

                if ($item->quantity_received < $item->quantity_ordered) {
                    $allFullyReceived = false;
                }
            }

            $newStatus = $allFullyReceived ? 'received' : ($hasAnyReceived ? 'partial' : $purchase->status);

            $purchase->update([
                'status' => $newStatus,
                'received_date' => Carbon::now(),
                'received_by' => auth()->id(),
            ]);

            ActivityLog::log(
                'reception_validee',
                "Réception validée pour la commande fournisseur {$purchase->reference} (Statut: {$newStatus})"
            );
        });

        return redirect()->route('magasinier.receptions.index')->with('success', 'Réception enregistrée et stocks mis à jour avec succès.');
    }
}
