<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::withCount(['products', 'purchases']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'hidden') {
                $query->where('is_active', false);
            }
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'hidden' => Supplier::where('is_active', false)->count(),
        ];

        return view('admin.suppliers.index', compact('suppliers', 'stats'));
    }

    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'company_name.required' => 'Le nom de l\'entreprise fournisseur est obligatoire.',
        ]);

        $supplier = Supplier::create($validated);

        ActivityLog::log('fournisseur_cree', "Ajout du fournisseur {$supplier->company_name}");

        return redirect()->route('admin.suppliers.index')->with('success', "Fournisseur {$supplier->company_name} ajouté avec succès.");
    }

    public function show(Supplier $supplier): View
    {
        $products = $supplier->products()->paginate(10);
        $purchases = $supplier->purchases()->latest()->paginate(10);

        return view('admin.suppliers.show', compact('supplier', 'products', 'purchases'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        ActivityLog::log('fournisseur_modifie', "Modification du fournisseur {$supplier->company_name}");

        return redirect()->route('admin.suppliers.index')->with('success', "Fournisseur {$supplier->company_name} mis à jour.");
    }

    public function toggleStatus(Supplier $supplier): RedirectResponse
    {
        $newStatus = ! $supplier->is_active;
        $supplier->update(['is_active' => $newStatus]);

        $actionText = $newStatus ? 'réactivé' : 'masqué';
        $logAction = $newStatus ? 'fournisseur_reactive' : 'fournisseur_masque';

        ActivityLog::log($logAction, "Fournisseur {$supplier->company_name} {$actionText}");

        return back()->with('success', "Le fournisseur {$supplier->company_name} a été {$actionText} avec succès.");
    }
}
