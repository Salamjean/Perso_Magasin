@extends('layouts.app')

@section('title', 'Détails de la Caisse : ' . $cashRegister->name)
@section('page_title', 'Sessions de ' . $cashRegister->name)

@section('content')
<div class="space-y-6 w-full">

    <!-- EN-TÊTE DE LA CAISSE -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0056a6] border border-blue-100 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-cash-register"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900">{{ $cashRegister->name }}</h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Code poste : {{ $cashRegister->code }}</p>
            </div>
        </div>
        <a href="{{ route('admin.cash-registers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux caisses</span>
        </a>
    </div>

    <!-- TABLEAU DE TOUTES LES SESSIONS DE CETTE CAISSE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-[#0056a6]"></i>
                <span>Historique de toutes les sessions de cette caisse</span>
            </h4>
            <p class="text-xs text-slate-400 mt-0.5">Consultez les bilans journaliers, ventes et écarts enregistrés pour ce poste</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Session #</th>
                        <th class="py-3.5 px-5 text-center">Caissier</th>
                        <th class="py-3.5 px-5 text-center">Date & Horaires</th>
                        <th class="py-3.5 px-5 text-center">Fond de Caisse</th>
                        <th class="py-3.5 px-5 text-center">Montant Théorique</th>
                        <th class="py-3.5 px-5 text-center">Montant Déclaré</th>
                        <th class="py-3.5 px-5 text-center">Écart</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $session)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5 align-middle font-mono font-bold text-slate-900">
                                #{{ $session->id }}
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 text-[11px] font-semibold">
                                    <i class="fa-solid fa-user text-[10px] text-slate-400"></i>
                                    {{ $session->user->full_name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle text-slate-500 font-mono text-[11px]">
                                <div>{{ $session->opened_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $session->opened_at->format('H:i') }} ➔ {{ $session->closed_at ? $session->closed_at->format('H:i') : 'En cours' }}</div>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle font-mono font-bold text-slate-700">
                                {{ number_format($session->opening_amount, 0, ',', ' ') }} <span class="text-[10px] font-sans font-normal text-slate-400">FCFA</span>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle font-mono font-bold text-[#0056a6]">
                                {{ number_format($session->closing_amount_theory, 0, ',', ' ') }} <span class="text-[10px] font-sans font-normal text-slate-400">FCFA</span>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle font-mono font-bold text-slate-900">
                                {{ $session->closing_amount_real !== null ? number_format($session->closing_amount_real, 0, ',', ' ') . ' FCFA' : 'En cours' }}
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($session->difference !== null)
                                    @if($session->difference == 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            0 FCFA
                                        </span>
                                    @elseif($session->difference < 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ number_format($session->difference, 0, ',', ' ') }} FCFA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0056a6] border border-blue-200">
                                            +{{ number_format($session->difference, 0, ',', ' ') }} FCFA
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[11px]">En cours</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                <a href="{{ route('admin.cash-sessions.show', $session) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-2xs transition cursor-pointer"
                                   title="Consulter le bilan complet de cette session">
                                    <i class="fa-solid fa-chart-pie text-xs"></i>
                                    <span>Bilan complet</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">Aucune session enregistrée pour cette caisse.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
