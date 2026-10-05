<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::withCount(['sales', 'deliveries']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('debt_only') && $request->debt_only == '1') {
            $query->where('debt_balance', '>', 0);
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'debt_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'Le nom du client est obligatoire.',
        ]);

        $customer = Customer::create([
            ...$validated,
            'debt_balance' => $validated['debt_balance'] ?? 0,
        ]);

        ActivityLog::log('client_cree', "Création du client {$customer->full_name}");

        return redirect()->route('admin.customers.index')->with('success', "Client {$customer->full_name} créé.");
    }

    public function show(Customer $customer): View
    {
        $sales = $customer->sales()->with(['user', 'items'])->latest()->paginate(10);
        $deliveries = $customer->deliveries()->with('livreur')->latest()->paginate(5);

        return view('admin.customers.show', compact('customer', 'sales', 'deliveries'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'debt_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        ActivityLog::log('client_modifie', "Modification des informations du client {$customer->full_name}");

        return redirect()->route('admin.customers.index')->with('success', "Client {$customer->full_name} mis à jour.");
    }

    public function adjustDebt(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric'],
            'action' => ['required', 'in:add,settle'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldBalance = $customer->debt_balance;
        if ($validated['action'] === 'add') {
            $customer->increment('debt_balance', $validated['amount']);
        } else {
            $customer->decrement('debt_balance', min($customer->debt_balance, $validated['amount']));
        }

        ActivityLog::log(
            'client_dette_ajustee',
            "Ajustement dette client {$customer->full_name} ({$oldBalance} -> {$customer->debt_balance} FCFA)"
        );

        return back()->with('success', "Dette du client mise à jour : {$customer->debt_balance} FCFA");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $name = $customer->full_name;
        $customer->delete();

        ActivityLog::log('client_supprime', "Suppression du client {$name}");

        return redirect()->route('admin.customers.index')->with('success', "Client {$name} supprimé.");
    }
}
