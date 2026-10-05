<?php

namespace App\Http\Controllers\Caissier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $activeSession = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->with(['cashRegister', 'movements', 'sales'])
            ->latest()
            ->first();

        $activeSessionStats = null;
        if ($activeSession) {
            $completedSales = $activeSession->sales->where('status', 'completed');
            $cashSales = $completedSales->where('payment_method', 'cash');
            $totalCredits = (float) $completedSales->sum(function ($s) {
                return $s->payment_method === 'credit'
                    ? max((float) $s->credit_amount, (float) $s->total_amount)
                    : (float) ($s->credit_amount ?? 0);
            });
            $creditsCount = $completedSales->filter(function ($s) {
                return $s->payment_method === 'credit' || (float) ($s->credit_amount ?? 0) > 0;
            })->count();

            $activeSessionStats = [
                'total_sales' => (float) $completedSales->sum('total_amount'),
                'sales_count' => $completedSales->count(),
                'total_cash' => (float) ($cashSales->sum('total_amount') - $cashSales->sum('credit_amount')),
                'total_credits' => $totalCredits,
                'credits_count' => $creditsCount,
                'total_mobile_money' => (float) $completedSales->where('payment_method', 'mobile_money')->sum('total_amount'),
                'total_card' => (float) $completedSales->where('payment_method', 'card')->sum('total_amount'),
            ];
        }

        $registers = CashRegister::where('status', 'closed')->get();
        $pastSessions = CashSession::where('user_id', $user->id)
            ->where('status', 'closed')
            ->with('cashRegister')
            ->latest()
            ->paginate(5);

        return view('caissier.session.status', compact('activeSession', 'activeSessionStats', 'registers', 'pastSessions'));
    }

    public function openForm(): View|RedirectResponse
    {
        $user = auth()->user();
        $activeSession = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        if ($activeSession) {
            return redirect()->route('caissier.pos.index')->with('info', 'Vous avez déjà une session de caisse ouverte.');
        }

        $registers = CashRegister::with(['currentSession.user'])->get();
        $lastSession = CashSession::where('user_id', $user->id)
            ->where('status', 'closed')
            ->with('cashRegister')
            ->latest('closed_at')
            ->first();

        return view('caissier.session.open', compact('registers', 'lastSession'));
    }

    public function open(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Vérifier si le caissier a déjà une session ouverte
        $existingSession = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        if ($existingSession) {
            return redirect()->route('caissier.pos.index')->with('info', 'Une session est déjà ouverte.');
        }

        $validated = $request->validate([
            'cash_register_id' => ['required', 'exists:cash_registers,id'],
            'opening_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'cash_register_id.required' => 'Veuillez sélectionner une caisse.',
            'opening_amount.required' => 'Veuillez indiquer le montant du fond de caisse.',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $register = CashRegister::findOrFail($validated['cash_register_id']);
            $register->update(['status' => 'open']);

            $session = CashSession::create([
                'cash_register_id' => $register->id,
                'user_id' => $user->id,
                'opening_amount' => $validated['opening_amount'],
                'closing_amount_theory' => $validated['opening_amount'],
                'status' => 'open',
                'opened_at' => Carbon::now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            CashMovement::create([
                'cash_session_id' => $session->id,
                'user_id' => $user->id,
                'type' => 'in',
                'amount' => $validated['opening_amount'],
                'reason' => 'Fond de caisse initial à l\'ouverture',
            ]);

            ActivityLog::log(
                'caisse_ouverte',
                "Ouverture de la {$register->name} par {$user->full_name} avec un fond de caisse de {$validated['opening_amount']} FCFA"
            );
        });

        return redirect()->route('caissier.pos.index')->with('success', 'Session de caisse ouverte avec succès. Bon service !');
    }

    public function closeForm(): View|RedirectResponse
    {
        $user = auth()->user();
        $session = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->with(['cashRegister', 'sales'])
            ->latest()
            ->first();

        if (! $session) {
            return redirect()->route('caissier.session.open')->with('error', 'Aucune session de caisse n\'est actuellement ouverte.');
        }

        $completedSales = $session->sales->where('status', 'completed');
        $cashSales = $completedSales->where('payment_method', 'cash');

        $totalCredits = (float) $completedSales->sum(function ($s) {
            return $s->payment_method === 'credit'
                ? max((float) $s->credit_amount, (float) $s->total_amount)
                : (float) ($s->credit_amount ?? 0);
        });
        $creditsCount = $completedSales->filter(function ($s) {
            return $s->payment_method === 'credit' || (float) ($s->credit_amount ?? 0) > 0;
        })->count();

        $sessionStats = [
            'total_sales' => (float) $completedSales->sum('total_amount'),
            'sales_count' => $completedSales->count(),
            'total_cash' => (float) ($cashSales->sum('total_amount') - $cashSales->sum('credit_amount')),
            'total_credits' => $totalCredits,
            'credits_count' => $creditsCount,
            'total_mobile_money' => (float) $completedSales->where('payment_method', 'mobile_money')->sum('total_amount'),
            'total_card' => (float) $completedSales->where('payment_method', 'card')->sum('total_amount'),
        ];

        return view('caissier.session.close', compact('session', 'sessionStats'));
    }

    public function close(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->latest()->first();

        if (! $session) {
            return redirect()->route('caissier.dashboard')->with('error', 'Aucune session active à clôturer.');
        }

        $validated = $request->validate([
            'closing_amount_real' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'closing_amount_real.required' => 'Veuillez saisir le montant réel compté en caisse.',
        ]);

        DB::transaction(function () use ($validated, $session, $user) {
            $realAmount = (float) $validated['closing_amount_real'];
            $theoreticalAmount = (float) $session->closing_amount_theory;
            $difference = $realAmount - $theoreticalAmount;

            $session->update([
                'closing_amount_real' => $realAmount,
                'difference' => $difference,
                'status' => 'closed',
                'closed_at' => Carbon::now(),
                'notes' => $validated['notes'] ?? $session->notes,
            ]);

            $session->cashRegister->update(['status' => 'closed']);

            ActivityLog::log(
                'caisse_fermee',
                "Clôture de la {$session->cashRegister->name} par {$user->full_name}. Théorique: {$theoreticalAmount} FCFA, Réel: {$realAmount} FCFA, Écart: {$difference} FCFA"
            );
        });

        return redirect()->route('caissier.session.status')->with('success', 'Session de caisse clôturée avec succès.');
    }
}
