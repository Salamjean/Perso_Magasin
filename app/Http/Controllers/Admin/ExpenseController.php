<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::with('user');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('month')) {
            $query->whereYear('expense_date', substr($request->month, 0, 4))
                ->whereMonth('expense_date', substr($request->month, 5, 2));
        }

        $expenses = $query->latest('expense_date')->paginate(10)->withQueryString();
        $totalExpenses = $query->sum('amount');

        return view('admin.expenses.index', compact('expenses', 'totalExpenses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ], [
            'title.required' => 'Le titre de la dépense est obligatoire.',
            'category.required' => 'La catégorie est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'expense_date.required' => 'La date est obligatoire.',
        ]);

        $expense = Expense::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        ActivityLog::log('depense_creee', "Enregistrement d'une dépense de {$expense->amount} FCFA ({$expense->title} - {$expense->category})");

        return redirect()->route('admin.expenses.index')->with('success', 'Dépense enregistrée avec succès.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $title = $expense->title;
        $amount = $expense->amount;
        $expense->delete();

        ActivityLog::log('depense_supprimee', "Suppression de la dépense {$title} ({$amount} FCFA)");

        return redirect()->route('admin.expenses.index')->with('success', 'Dépense supprimée.');
    }
}
