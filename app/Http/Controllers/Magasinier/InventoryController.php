<?php

namespace App\Http\Controllers\Magasinier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $inventories = Inventory::with(['user', 'category', 'items'])
            ->latest()
            ->paginate(10);

        return view('magasinier.inventory.index', compact('inventories'));
    }

    public function create(Request $request): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $type = $request->input('type', 'general');
        $categoryId = $request->input('category_id');

        // Si une catégorie est sélectionnée, s'assurer que type est 'category'
        if ($categoryId) {
            $type = 'category';
        }

        $productsQuery = Product::where('is_active', true)->with('category');
        if (($type === 'category' || $request->filled('category_id')) && $categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }
        $products = $productsQuery->orderBy('name')->get();

        return view('magasinier.inventory.create', compact('categories', 'type', 'categoryId', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:general,category,product'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.physical_stock' => ['required', 'numeric', 'min:0'],
            'items.*.reason' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $inventory = Inventory::create([
                'reference' => 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                'user_id' => auth()->id(),
                'type' => $validated['type'],
                'category_id' => $validated['category_id'] ?? null,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $systemStock = $product->stock_quantity;
                $physicalStock = (float) $itemData['physical_stock'];
                $difference = $physicalStock - $systemStock;

                InventoryItem::create([
                    'inventory_id' => $inventory->id,
                    'product_id' => $product->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $difference,
                    'reason' => $itemData['reason'] ?? ($difference != 0 ? 'Ajustement inventaire' : 'Conforme'),
                ]);

                // Ajuster le stock système et logger le mouvement
                if ($difference != 0) {
                    $product->update(['stock_quantity' => $physicalStock]);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'adjustment',
                        'quantity' => abs($difference),
                        'previous_stock' => $systemStock,
                        'new_stock' => $physicalStock,
                        'reason' => 'Ajustement inventaire '.$inventory->reference.($difference < 0 ? ' (Perte/Casse)' : ' (Surplus trouvé)'),
                        'reference' => $inventory->reference,
                        'notes' => $itemData['reason'] ?? null,
                    ]);
                }
            }

            ActivityLog::log(
                'inventaire_effectue',
                "Inventaire {$inventory->reference} clôturé par le magasinier (".count($validated['items']).' articles vérifiés)'
            );
        });

        return redirect()->route('magasinier.inventory.index')->with('success', 'Inventaire enregistré et stocks mis à jour avec succès.');
    }

    public function show(Inventory $inventory): View
    {
        $inventory->load(['user', 'category', 'items.product']);

        return view('magasinier.inventory.show', compact('inventory'));
    }
}
