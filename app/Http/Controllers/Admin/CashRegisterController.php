<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    public function index(): View
    {
        $registers = CashRegister::with(['currentSession.user', 'sessions' => function ($q) {
            $q->latest()->limit(5);
        }])->get();

        $recentSessions = CashSession::with(['cashRegister', 'user'])
            ->latest()
            ->paginate(10);

        return view('admin.cash_registers.index', compact('registers', 'recentSessions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:cash_registers,code'],
        ], [
            'name.required' => 'Le nom de la caisse est obligatoire.',
            'code.required' => 'Le code de la caisse est obligatoire.',
            'code.unique' => 'Ce code de caisse existe déjà.',
        ]);

        $register = CashRegister::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'status' => 'closed',
        ]);

        ActivityLog::log('caisse_creee', "Création de la caisse {$register->name} ({$register->code})");

        return redirect()->route('admin.cash_registers.index')->with('success', "Caisse {$register->name} créée.");
    }

    public function show(CashRegister $cashRegister): View
    {
        $sessions = $cashRegister->sessions()->with(['user', 'movements', 'sales'])->latest()->paginate(10);

        return view('admin.cash_registers.show', compact('cashRegister', 'sessions'));
    }

    public function showSession(CashSession $session): View
    {
        $session->load([
            'cashRegister',
            'user',
            'movements.user',
            'sales' => function ($q) {
                $q->with(['customer', 'items.product'])->latest();
            },
        ]);

        $sales = $session->sales;
        $completedSales = $sales->where('status', 'completed');

        $cashSales = $completedSales->where('payment_method', 'cash');

        $totalCredits = (float) $completedSales->sum(function ($s) {
            return $s->payment_method === 'credit'
                ? max((float) $s->credit_amount, (float) $s->total_amount)
                : (float) ($s->credit_amount ?? 0);
        });

        $creditsCount = $completedSales->filter(function ($s) {
            return $s->payment_method === 'credit' || (float) ($s->credit_amount ?? 0) > 0;
        })->count();

        $stats = [
            'total_sales_count' => $sales->count(),
            'completed_sales_count' => $completedSales->count(),
            'cancelled_sales_count' => $sales->where('status', 'cancelled')->count(),
            'total_sales_amount' => (float) $completedSales->sum('total_amount'),
            // Espèces réellement perçues = total ventes cash - crédits accordés sur ces ventes
            'total_cash' => (float) ($cashSales->sum('total_amount') - $cashSales->sum('credit_amount')),
            'total_cash_credit' => (float) $cashSales->sum('credit_amount'),
            'total_credits' => $totalCredits,
            'credits_count' => $creditsCount,
            'total_mobile_money' => (float) $completedSales->where('payment_method', 'mobile_money')->sum('total_amount'),
            'total_card' => (float) $completedSales->where('payment_method', 'card')->sum('total_amount'),
            'total_transfer' => (float) $completedSales->where('payment_method', 'transfer')->sum('total_amount'),
            'total_discounts' => (float) $completedSales->sum('discount'),
            'total_movements_in' => (float) $session->movements->where('type', 'in')->sum('amount'),
            'total_movements_out' => (float) $session->movements->where('type', 'out')->sum('amount'),
        ];

        return view('admin.cash_registers.session_details', compact('session', 'stats'));
    }

    public function addMovement(Request $request, CashSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        CashMovement::create([
            'cash_session_id' => $session->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Mise à jour du montant théorique de la session
        if ($validated['type'] === 'in') {
            $session->increment('closing_amount_theory', $validated['amount']);
        } else {
            $session->decrement('closing_amount_theory', $validated['amount']);
        }

        ActivityLog::log(
            'mouvement_caisse',
            "Mouvement de caisse ({$validated['type']}) de {$validated['amount']} FCFA sur la session #{$session->id}. Motif: {$validated['reason']}"
        );

        return back()->with('success', 'Mouvement de caisse enregistré avec succès.');
    }
}
