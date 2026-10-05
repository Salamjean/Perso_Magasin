<?php

namespace App\Http\Controllers\Caissier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    /**
     * Liste des livraisons pour le caissier.
     */
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

        $deliveries = $query->latest()->paginate(15)->withQueryString();
        $livreurs = User::where('role', 'livreur')->where('status', 'active')->get();

        return view('caissier.deliveries.index', compact('deliveries', 'livreurs'));
    }

    /**
     * Formulaire de programmation d'une livraison (avec ou sans achat).
     */
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

        // Ventes récentes du caissier pour sélection rapide avec articles
        $recentSales = Sale::with(['customer', 'items.product'])
            ->where('status', 'completed')
            ->whereDoesntHave('delivery')
            ->latest()
            ->take(30)
            ->get();

        return view('caissier.deliveries.create', compact('customers', 'livreurs', 'selectedSale', 'recentSales', 'products'));
    }

    /**
     * Enregistrer une nouvelle livraison programmée.
     */
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

        $notes = $validated['notes'] ?? null;
        if ($request->filled('custom_items_description')) {
            $customDesc = 'Articles/Contenu : '.$request->input('custom_items_description');
            $notes = $notes ? $notes."\n\n".$customDesc : $customDesc;
        }

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
            'notes' => $notes,
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

        $typeInfo = $delivery->sale_id ? "liée à la vente #{$delivery->sale->sale_number}" : 'sans vente (articles/colis non achetés)';
        ActivityLog::log('livraison_programmee_caissier', "Programmation livraison {$delivery->delivery_number} ({$typeInfo}) pour {$delivery->recipient_name} par le caissier ".auth()->user()->full_name);

        return redirect()->route('caissier.deliveries.index')->with('success', "Livraison {$delivery->delivery_number} programmée avec succès (Code OTP de sécurité : {$otp}).");
    }

    /**
     * Détails d'une livraison.
     */
    public function show(Delivery $delivery): View
    {
        $delivery->load(['customer', 'livreur', 'sale.items.product', 'sale.user', 'items.product']);
        $livreurs = User::where('role', 'livreur')->where('status', 'active')->get();

        return view('caissier.deliveries.show', compact('delivery', 'livreurs'));
    }

    /**
     * Annuler une livraison programmée par le caissier si nécessaire.
     */
    public function cancel(Request $request, Delivery $delivery): RedirectResponse
    {
        if (in_array($delivery->status, ['delivered', 'cancelled'])) {
            return back()->with('error', 'Cette livraison ne peut pas être annulée.');
        }

        $validated = $request->validate([
            'failure_reason' => ['required', 'string', 'max:500'],
        ], [
            'failure_reason.required' => 'Le motif d\'annulation de la livraison est obligatoire.',
        ]);

        $delivery->status = 'cancelled';
        $delivery->failure_reason = $validated['failure_reason'];
        $delivery->save();

        ActivityLog::log('livraison_annulee_caissier', "Livraison {$delivery->delivery_number} annulée par le caissier. Motif: {$delivery->failure_reason}");

        return back()->with('success', "La livraison {$delivery->delivery_number} a été annulée.");
    }
}
