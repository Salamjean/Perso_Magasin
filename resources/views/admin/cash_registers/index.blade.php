@extends('layouts.app')

@section('title', 'Gestion des Caisses')
@section('page_title', 'Caisses & Clôtures de Sessions')

@section('content')
<div class="space-y-6">

    <!-- ADD NEW REGISTER & SUMMARY -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($registers as $register)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-[#0056a6]/40 transition group">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full {{ $register->status === 'open' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            <h3 class="text-base font-bold text-slate-900">{{ $register->name }}</h3>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $register->status === 'open' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                            {{ $register->status === 'open' ? 'Ouverte' : 'Fermée' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 font-mono mt-1">Code poste : {{ $register->code }}</p>

                    @if($register->currentSession)
                        <div class="mt-4 p-4 rounded-xl bg-emerald-50/70 border border-emerald-100 text-xs space-y-1.5 text-slate-700">
                            <p class="font-bold text-emerald-950 flex items-center gap-1.5">
                                <i class="fa-solid fa-user text-emerald-600"></i>
                                <span>Caissier : {{ $register->currentSession->user->full_name }}</span>
                            </p>
                            <p>Fond initial : <strong>{{ number_format($register->currentSession->opening_amount, 0, ',', ' ') }} FCFA</strong></p>
                            <p>Montant théorique actuel : <strong class="text-emerald-700 text-sm font-mono font-bold">{{ number_format($register->currentSession->closing_amount_theory, 0, ',', ' ') }} FCFA</strong></p>
                            <p class="text-[10px] text-slate-400 font-mono">Ouverte depuis : {{ $register->currentSession->opened_at->format('H:i') }}</p>
                        </div>
                    @else
                        <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-400 italic text-center">
                            Aucun caissier connecté sur ce poste
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.cash-registers.show', $register) }}" class="text-xs font-bold text-[#0056a6] hover:text-[#004485] flex items-center gap-1">
                        <span>Voir l'historique des sessions</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        @endforeach

        <!-- CREATE REGISTER FORM -->
        <div class="bg-slate-50 rounded-2xl p-6 border-2 border-dashed border-slate-300 flex flex-col justify-center">
            <h4 class="text-sm font-bold text-slate-800 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-cash-register text-[#0056a6]"></i>
                <span>Ajouter un poste de caisse</span>
            </h4>
            <p class="text-xs text-slate-500 mb-4">Définissez un numéro de poste pour votre magasin</p>

            <form action="{{ route('admin.cash-registers.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <input type="text" name="name" required placeholder="Ex: Caisse N°3 (Allée Centrale)"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:border-[#0056a6] focus:ring-1 focus:ring-[#0056a6] focus:outline-none">
                </div>
                <div>
                    <input type="text" name="code" required placeholder="Ex: CAISSE-03"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono uppercase focus:border-[#0056a6] focus:ring-1 focus:ring-[#0056a6] focus:outline-none">
                </div>
                <button type="submit" class="w-full py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer">
                    Créer le poste de caisse
                </button>
            </form>
        </div>
    </div>

    <!-- RECENT SESSIONS HISTORY & DISCREPANCIES (ÉCARTS DE CAISSE & DÉTAILS COMPLETS) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#0056a6]"></i>
                    <span>Historique des Sessions, Ventes & Contrôle des Écarts</span>
                </h4>
                <p class="text-xs text-slate-400 mt-0.5">Suivi de toutes les ouvertures, encaissements et clôtures journalières</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Poste Caisse</th>
                        <th class="py-3.5 px-5 text-center">Caissier</th>
                        <th class="py-3.5 px-5 text-center">Date & Horaires</th>
                        <th class="py-3.5 px-5 text-center">Fond Initial</th>
                        <th class="py-3.5 px-5 text-center">Montant Théorique</th>
                        <th class="py-3.5 px-5 text-center">Montant Déclaré</th>
                        <th class="py-3.5 px-5 text-center">Écart Constaté</th>
                        <th class="py-3.5 px-5 text-center">Détails de la journée</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSessions as $session)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- CAISSE -->
                            <td class="py-3.5 px-5 align-middle font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $session->status === 'open' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300' }}"></span>
                                    <span>{{ $session->cashRegister->name }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">Session #{{ $session->id }}</span>
                            </td>

                            <!-- CAISSIER -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 text-[11px] font-semibold">
                                    <i class="fa-solid fa-user text-[10px] text-slate-400"></i>
                                    {{ $session->user->full_name }}
                                </span>
                            </td>

                            <!-- PÉRIODE -->
                            <td class="py-3.5 px-5 text-center align-middle text-slate-500 font-mono text-[11px]">
                                <div>{{ $session->opened_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $session->opened_at->format('H:i') }} ➔ {{ $session->closed_at ? $session->closed_at->format('H:i') : 'En cours' }}</div>
                            </td>

                            <!-- FOND INITIAL -->
                            <td class="py-3.5 px-5 text-center align-middle text-slate-700 font-mono font-bold">
                                {{ number_format($session->opening_amount, 0, ',', ' ') }} <span class="text-[10px] font-sans font-normal text-slate-400">FCFA</span>
                            </td>

                            <!-- THÉORIQUE -->
                            <td class="py-3.5 px-5 text-center align-middle text-[#0056a6] font-mono font-bold">
                                {{ number_format($session->closing_amount_theory, 0, ',', ' ') }} <span class="text-[10px] font-sans font-normal text-slate-400">FCFA</span>
                            </td>

                            <!-- RÉEL DÉCLARÉ -->
                            <td class="py-3.5 px-5 text-center align-middle font-bold text-slate-900 font-mono">
                                @if($session->closing_amount_real !== null)
                                    {{ number_format($session->closing_amount_real, 0, ',', ' ') }} <span class="text-[10px] font-sans font-normal text-slate-400">FCFA</span>
                                @else
                                    <span class="text-slate-400 font-sans font-normal text-[11px] italic">En cours</span>
                                @endif
                            </td>

                            <!-- ÉCART -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($session->difference !== null)
                                    @if($session->difference == 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-check text-[9px]"></i> Parfait (0 FCFA)
                                        </span>
                                    @elseif($session->difference < 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Manque : {{ number_format($session->difference, 0, ',', ' ') }} FCFA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0056a6] border border-blue-200">
                                            <i class="fa-solid fa-plus text-[9px]"></i> Surplus : +{{ number_format($session->difference, 0, ',', ' ') }} FCFA
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Session ouverte</span>
                                @endif
                            </td>

                            <!-- BOUTON DÉTAILS COMPLETS DE LA JOURNÉE -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <a href="{{ route('admin.cash-sessions.show', $session) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-2xs transition cursor-pointer"
                                   title="Consulter toutes les ventes, crédits, mouvements et écarts de cette session">
                                    <i class="fa-solid fa-chart-pie text-xs"></i>
                                    <span>Bilan complet</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-cash-register"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Aucune session de caisse enregistrée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentSessions->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $recentSessions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
