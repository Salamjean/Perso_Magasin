<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Delivery::with(['customer', 'livreur', 'sale']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('livreur_id')) {
            $query->where('livreur_id', $request->livreur_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%")
                    ->orWhere('delivery_address', 'like', "%{$search}%");
            });
        }

        $deliveries = $query->latest()->paginate(10)->withQueryString();
        $livreurs = User::where('role', 'livreur')->get();

        return view('admin.deliveries.index', compact('deliveries', 'livreurs'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::orderBy('name')->get();
        $livreurs = User::where('role', 'livreur')->where('status', 'active')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Récupérer la vente si passée en paramètre
        $selectedSale = null;
        if ($request->filled('sale_id')) {
            $selectedSale = Sale::with(['customer', 'items.product'])->find($request->sale_id);
        }

        // Ventes récentes sans livraison
        $recentSales = Sale::with(['customer', 'items.product'])
            ->where('status', 'completed')
            ->whereDoesntHave('delivery')
            ->latest()
            ->take(30)
            ->get();

        return view('admin.deliveries.create', compact('customers', 'livreurs', 'selectedSale', 'recentSales', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_id' => ['nullable', 'exists:sales,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'delivery_address' => ['required', 'string'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'livreur_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'recipient_name.required' => 'Le nom du destinataire est obligatoire.',
            'recipient_phone.required' => 'Le numéro de téléphone du destinataire est obligatoire.',
            'delivery_address.required' => 'L\'adresse de livraison est obligatoire.',
            'total_amount.required' => 'Le montant à recouvrer / frais est obligatoire.',
            'total_amount.min' => 'Le montant doit être supérieur ou égal à 0.',
        ]);

        $otp = (string) rand(100000, 999999);
        $status = ! empty($validated['livreur_id']) ? 'assigned' : 'pending';

        $delivery = Delivery::create([
            'delivery_number' => 'LIV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
            'sale_id' => $validated['sale_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'livreur_id' => $validated['livreur_id'] ?? null,
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'delivery_address' => $validated['delivery_address'],
            'total_amount' => $validated['total_amount'],
            'status' => $status,
            'otp_code' => $otp,
            'notes' => $validated['notes'] ?? null,
            'assigned_at' => ! empty($validated['livreur_id']) ? Carbon::now() : null,
        ]);

        // Enregistrer les lignes d'articles si livraison hors vente / sans ticket
        if (empty($validated['sale_id']) && $request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $item) {
                if (! empty($item['product_id']) && ! empty($item['quantity'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        DeliveryItem::create([
                            'delivery_id' => $delivery->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => (float) $item['quantity'],
                            'unit_price' => (float) ($item['unit_price'] ?? $product->sell_price),
                            'total_price' => ((float) $item['quantity'] * (float) ($item['unit_price'] ?? $product->sell_price)),
                        ]);
                    }
                }
            }
        }

        ActivityLog::log('livraison_creee', "Création livraison {$delivery->delivery_number} pour {$delivery->recipient_name}");

        return redirect()->route('admin.deliveries.index')->with('success', "Livraison {$delivery->delivery_number} programmée avec succès.");
    }

    public function show(Delivery $delivery): View
    {
        $delivery->load(['customer', 'livreur', 'sale.items.product', 'items.product']);
        $livreurs = User::where('role', 'livreur')->where('status', 'active')->get();

        return view('admin.deliveries.show', compact('delivery', 'livreurs'));
    }

    public function assign(Request $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'livreur_id' => ['required', 'exists:users,id'],
        ]);

        $delivery->livreur_id = $validated['livreur_id'];
        $delivery->status = 'assigned';
        $delivery->assigned_at = Carbon::now();
        $delivery->save();

        ActivityLog::log('livraison_assignee', "Affectation de la livraison {$delivery->delivery_number} au livreur #{$delivery->livreur_id}");

        return back()->with('success', "Livraison affectée au livreur {$delivery->livreur->full_name}.");
    }

    public function updateStatus(Request $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,preparing,ready,assigned,in_transit,delivered,failed,cancelled'],
            'failure_reason' => ['nullable', 'string'],
        ]);

        $previousStatus = $delivery->status;
        $delivery->status = $validated['status'];
        if ($validated['status'] === 'delivered') {
            $delivery->delivered_at = Carbon::now();

            // Déduction de stock UNIQUEMENT pour les livraisons sans ticket de vente (sale_id === null)
            if ($previousStatus !== 'delivered' && is_null($delivery->sale_id)) {
                $delivery->load('items.product');
                foreach ($delivery->items as $item) {
                    if ($item->product_id && $item->product) {
                        $product = $item->product;
                        $prevStock = (float) $product->stock_quantity;
                        $product->decrement('stock_quantity', $item->quantity);
                        $product->refresh();

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'type' => 'sale',
                            'quantity' => $item->quantity,
                            'previous_stock' => $prevStock,
                            'new_stock' => $product->stock_quantity,
                            'reason' => 'Livraison directe hors vente validée #'.$delivery->delivery_number,
                            'reference' => $delivery->delivery_number,
                        ]);
                    }
                }
            }
        }
        if ($validated['status'] === 'failed') {
            $delivery->failure_reason = $validated['failure_reason'] ?? null;
        }
        $delivery->save();

        ActivityLog::log('livraison_statut_modifie', "Statut livraison {$delivery->delivery_number} -> {$delivery->status}");

        return back()->with('success', 'Statut de la livraison mis à jour.');
    }
}
