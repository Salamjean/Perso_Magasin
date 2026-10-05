<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->latest()->paginate(16);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Le nom de la catégorie est obligatoire.',
            'name.unique' => 'Cette catégorie existe déjà.',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'is_active' => true,
        ]);

        ActivityLog::log('categorie_creee', "Création de la catégorie {$category->name}");

        return redirect()->route('admin.categories.index')->with('success', "Catégorie {$category->name} créée.");
    }

    public function show(Category $category, Request $request): View
    {
        $query = $category->products()->with(['category', 'supplier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total_products' => $category->products()->count(),
            'total_stock' => (float) $category->products()->sum('stock_quantity'),
            'total_value' => (float) $category->products()->selectRaw('SUM(stock_quantity * sell_price) as total')->value('total') ?? 0,
        ];

        return view('admin.categories.show', compact('category', 'products', 'stats'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::log('categorie_modifiee', "Modification de la catégorie {$category->name}");

        return redirect()->route('admin.categories.index')->with('success', "Catégorie {$category->name} mise à jour.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        ActivityLog::log('categorie_supprimee', "Suppression de la catégorie {$name}");

        return redirect()->route('admin.categories.index')->with('success', "Catégorie {$name} supprimée.");
    }
}
