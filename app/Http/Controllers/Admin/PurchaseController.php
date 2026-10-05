<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'user', 'receivedBy', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->latest()->paginate(10)->withQueryString();
        $suppliers = Supplier::all();

        return view('admin.purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get();
        $products = Product::where('is_active', true)->get();

        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'numeric', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            foreach ($validated['products'] as $item) {
                $totalAmount += ($item['quantity'] * $item['price']);
            }

            $purchase = Purchase::create([
                'reference' => 'CMD-FOURN-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'status' => 'ordered',
                'order_date' => $validated['order_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['products'] as $p) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $p['id'],
                    'quantity_ordered' => $p['quantity'],
                    'quantity_received' => 0,
                    'unit_buy_price' => $p['price'],
                    'total_price' => $p['quantity'] * $p['price'],
                ]);
            }

            ActivityLog::log(
                'approvisionnement_commande',
                "Création de la commande fournisseur {$purchase->reference} d'un montant de {$purchase->total_amount} FCFA"
            );
        });

        return redirect()->route('admin.purchases.index')->with('success', 'Commande fournisseur créée avec succès.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'receivedBy', 'items.product']);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function reception(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'receivedBy', 'items.product']);

        return view('admin.purchases.reception', compact('purchase'));
    }

    public function processReception(Request $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Aucun article à réceptionner.',
            'items.*.received_qty.required' => 'La quantité reçue est obligatoire.',
            'items.*.received_qty.min' => 'La quantité reçue ne peut pas être négative.',
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
                "Réception validée par l'Admin pour la commande fournisseur {$purchase->reference} (Statut: {$newStatus})"
            );
        });

        return redirect()->route('admin.purchases.show', $purchase)->with('success', 'Réception enregistrée et stocks mis à jour avec succès.');
    }

    public function updateStatus(Request $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,ordered,received,partial,cancelled'],
        ]);

        $purchase->status = $validated['status'];
        if ($validated['status'] === 'received') {
            if (! $purchase->received_date) {
                $purchase->received_date = Carbon::now();
            }
            if (! $purchase->received_by) {
                $purchase->received_by = auth()->id();
            }
        }
        $purchase->save();

        ActivityLog::log('approvisionnement_statut', "Statut de la commande fournisseur {$purchase->reference} changé en {$purchase->status}");

        return back()->with('success', 'Statut mis à jour.');
    }
}
