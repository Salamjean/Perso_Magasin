<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\BarcodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        public BarcodeService $barcodeService
    ) {}

    public function index(Request $request): View
    {
        $query = Product::with(['category', 'supplier']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('stock_filter')) {
            if ($request->stock_filter === 'out') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($request->stock_filter === 'low') {
                $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'alert_threshold');
            } elseif ($request->stock_filter === 'ok') {
                $query->whereColumn('stock_quantity', '>', 'alert_threshold');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $stats = [
            'total' => Product::count(),
            'in_stock' => Product::whereColumn('stock_quantity', '>', 'alert_threshold')->count(),
            'low_stock' => Product::where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'alert_threshold')->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
        ];

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get();

        return view('admin.products.index', compact('products', 'categories', 'suppliers', 'stats'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get();

        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'alert_threshold' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Le nom du produit est obligatoire.',
            'sell_price.required' => 'Le prix unitaire est obligatoire.',
            'barcode.unique' => 'Ce code-barres est déjà attribué à un autre produit.',
        ]);

        // Génération automatique du code-barres si non fourni
        $barcode = $validated['barcode'] ?? null;
        if (empty($barcode)) {
            $barcode = $this->barcodeService->generateUniqueEan13();
        }

        // Génération de l'image du code-barres
        $barcodeImagePath = $this->barcodeService->generateBarcodeImage($barcode, $validated['name']);

        // Génération automatique de la référence interne
        do {
            $reference = 'PRD-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (Product::where('reference', $reference)->exists());

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $stockQuantity = (float) ($validated['stock_quantity'] ?? 0);
        $alertThreshold = (float) ($validated['alert_threshold'] ?? 5);

        $product = Product::create([
            'name' => $validated['name'],
            'reference' => $reference,
            'barcode' => $barcode,
            'barcode_image' => $barcodeImagePath,
            'category_id' => $validated['category_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'sell_price' => $validated['sell_price'],
            'sale_price' => $validated['sell_price'],
            'buy_price' => 0,
            'purchase_price' => 0,
            'stock_quantity' => $stockQuantity,
            'alert_threshold' => $alertThreshold,
            'unit' => 'pcs',
            'image' => $imagePath,
            'is_active' => true,
        ]);

        // Mouvement de stock initial
        if ($product->stock_quantity > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $product->stock_quantity,
                'previous_stock' => 0,
                'new_stock' => $product->stock_quantity,
                'reason' => 'Stock initial à la création du produit',
                'reference' => 'INIT-'.$product->reference,
            ]);
        }

        ActivityLog::log(
            'produit_cree',
            "Création du produit {$product->name} (Code: {$product->barcode}, Stock initial: {$product->stock_quantity})"
        );

        return redirect()->route('admin.products.index')->with('success', "Le produit {$product->name} a été enregistré avec succès.");
    }

    public function show(Product $product): View
    {
        if ($product->barcode && (! $product->barcode_image || ! Storage::disk('public')->exists($product->barcode_image))) {
            $barcodeImagePath = $this->barcodeService->generateBarcodeImage($product->barcode, $product->name);
            $product->barcode_image = $barcodeImagePath;
            $product->save();
        }

        $movements = $product->stockMovements()->with('user')->latest()->get();

        return view('admin.products.show', compact('product', 'movements'));
    }

    public function downloadBarcode(Product $product): mixed
    {
        if (! $product->barcode) {
            return back()->with('error', 'Ce produit ne possède aucun code-barres.');
        }

        if (! $product->barcode_image || ! Storage::disk('public')->exists($product->barcode_image)) {
            $barcodeImagePath = $this->barcodeService->generateBarcodeImage($product->barcode, $product->name);
            $product->barcode_image = $barcodeImagePath;
            $product->save();
        }

        $filename = 'code-barres-'.Str::slug($product->name).'-'.$product->barcode.'.svg';
        $fullPath = Storage::disk('public')->path($product->barcode_image);

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->orWhere('id', $product->supplier_id)->orderBy('company_name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode,'.$product->id],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'alert_threshold' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Le nom du produit est obligatoire.',
            'sell_price.required' => 'Le prix unitaire est obligatoire.',
            'barcode.unique' => 'Ce code-barres est déjà attribué.',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $validated['name'],
            'barcode' => $validated['barcode'] ?? $product->barcode,
            'category_id' => $validated['category_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'sell_price' => $validated['sell_price'],
            'sale_price' => $validated['sell_price'],
            'alert_threshold' => (float) ($validated['alert_threshold'] ?? 5),
        ]);

        ActivityLog::log('produit_modifie', "Mise à jour du produit {$product->name}");

        return redirect()->route('admin.products.index')->with('success', "Le produit {$product->name} a été mis à jour.");
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->is_active = ! $product->is_active;
        $product->save();

        ActivityLog::log('produit_statut', "Le produit {$product->name} a été ".($product->is_active ? 'activé' : 'désactivé'));

        return back()->with('success', "Le produit {$product->name} a été ".($product->is_active ? 'activé' : 'désactivé').'.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        ActivityLog::log('produit_supprime', "Suppression du produit {$name}");

        return redirect()->route('admin.products.index')->with('success', "Le produit {$name} a été supprimé.");
    }
}
