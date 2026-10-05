<?php

namespace App\Http\Controllers\Magasinier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockMovement::with(['product', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($pq) use ($search) {
                $pq->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $movements = $query->latest()->paginate(15)->withQueryString();
        $products = Product::where('is_active', true)->get();

        return view('magasinier.stock.index', compact('movements', 'products'));
    }

    public function entry(): View
    {
        $products = Product::where('is_active', true)->get();

        return view('magasinier.stock.entry', compact('products'));
    }

    public function processEntry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'product_id.required' => 'Veuillez sélectionner un produit.',
            'quantity.required' => 'Veuillez renseigner une quantité valide.',
            'reason.required' => 'Veuillez indiquer le motif de l\'entrée.',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::findOrFail($validated['product_id']);
            $prevStock = $product->stock_quantity;
            $product->increment('stock_quantity', $validated['quantity']);

            $ref = $validated['reference'] ?? 'ENTREE-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'previous_stock' => $prevStock,
                'new_stock' => $product->stock_quantity,
                'reason' => $validated['reason'],
                'reference' => $ref,
                'notes' => $validated['notes'] ?? null,
            ]);

            ActivityLog::log(
                'stock_entree',
                "Entrée de stock : +{$validated['quantity']} {$product->unit} pour {$product->name} (Nouveau stock: {$product->stock_quantity}). Motif: {$validated['reason']}"
            );
        });

        return redirect()->route('magasinier.stock.index')->with('success', 'Entrée de stock enregistrée avec succès.');
    }

    public function exit(): View
    {
        $products = Product::where('is_active', true)->where('stock_quantity', '>', 0)->get();

        return view('magasinier.stock.exit', compact('products'));
    }

    public function processExit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'in:produit_endommage,produit_perime,perte,casse,retour_fournisseur,autre'],
            'notes' => ['nullable', 'string'],
        ], [
            'product_id.required' => 'Veuillez sélectionner un produit.',
            'quantity.required' => 'Veuillez renseigner une quantité valide.',
            'reason.required' => 'Veuillez choisir un motif de sortie.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock_quantity < $validated['quantity']) {
            return back()->with('error', "Quantité insuffisante en stock ({$product->stock_quantity} disponible).");
        }

        DB::transaction(function () use ($validated, $product) {
            $prevStock = $product->stock_quantity;
            $product->decrement('stock_quantity', $validated['quantity']);

            $reasonLabels = [
                'produit_endommage' => 'Produit endommagé',
                'produit_perime' => 'Produit périmé',
                'perte' => 'Perte / Vol',
                'casse' => 'Casse en rayon/magasin',
                'retour_fournisseur' => 'Retour au fournisseur',
                'autre' => 'Autre sortie',
            ];
            $reasonText = $reasonLabels[$validated['reason']] ?? $validated['reason'];

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'previous_stock' => $prevStock,
                'new_stock' => $product->stock_quantity,
                'reason' => $reasonText,
                'reference' => 'SORTIE-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                'notes' => $validated['notes'] ?? null,
            ]);

            ActivityLog::log(
                'stock_sortie',
                "Sortie de stock : -{$validated['quantity']} {$product->unit} pour {$product->name} ({$reasonText})"
            );
        });

        return redirect()->route('magasinier.stock.index')->with('success', 'Sortie de stock validée.');
    }
}
