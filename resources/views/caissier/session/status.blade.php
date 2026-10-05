@extends('layouts.app')

@section('title', 'Gestion de Caisse')
@section('page-title', 'État de la Caisse & Historique des Services')

@section('content')
<div class="space-y-6">

    @if($activeSession)
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="text-xl font-bold text-slate-900">{{ $activeSession->cashRegister->name }}</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">En service</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Ouverte le {{ $activeSession->opened_at->format('d/m/Y à H:i') }}
                        &nbsp;•&nbsp; Fond initial : {{ number_format($activeSession->opening_amount, 0, ',', ' ') }} FCFA
                    </p>
                    <p class="text-sm font-black text-indigo-700 mt-2">
                        Montant théorique en caisse : {{ number_format($activeSession->closing_amount_theory, 0, ',', ' ') }} FCFA
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('caissier.pos.index') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Aller au TPV</span>
                    </a>
                    <a href="{{ route('caissier.session.close') }}" class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold border border-rose-200 transition">
                        Clôturer la caisse
                    </a>
                </div>
            </div>

            @if($activeSessionStats && ($activeSessionStats['sales_count'] > 0 || $activeSessionStats['total_credits'] > 0))
                <!-- Ventilation rapide des encaissements -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-100">
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-center">
                        <p class="text-[10px] font-bold text-emerald-700 uppercase mb-1">💵 Espèces</p>
                        <p class="text-sm font-black text-emerald-900 font-mono">{{ number_format($activeSessionStats['total_cash'], 0, ',', ' ') }}</p>
                        <p class="text-[9px] text-emerald-600 font-semibold">FCFA encaissés</p>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-100 text-center">
                        <p class="text-[10px] font-bold text-amber-700 uppercase mb-1">📱 Mobile</p>
                        <p class="text-sm font-black text-amber-900 font-mono">{{ number_format($activeSessionStats['total_mobile_money'], 0, ',', ' ') }}</p>
                        <p class="text-[9px] text-amber-600 font-semibold">FCFA</p>
                    </div>
                    <div class="p-3 rounded-xl bg-sky-50 border border-sky-100 text-center">
                        <p class="text-[10px] font-bold text-sky-700 uppercase mb-1">💳 Carte</p>
                        <p class="text-sm font-black text-sky-900 font-mono">{{ number_format($activeSessionStats['total_card'], 0, ',', ' ') }}</p>
                        <p class="text-[9px] text-sky-600 font-semibold">FCFA</p>
                    </div>
                    <div class="p-3 rounded-xl {{ $activeSessionStats['total_credits'] > 0 ? 'bg-amber-100 border-amber-200' : 'bg-slate-50 border-slate-100' }} border text-center">
                        <p class="text-[10px] font-bold text-amber-700 uppercase mb-1">🤝 Crédits</p>
                        <p class="text-sm font-black text-amber-900 font-mono">{{ number_format($activeSessionStats['total_credits'], 0, ',', ' ') }}</p>
                        <p class="text-[9px] text-amber-600 font-semibold">FCFA (non en caisse)</p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-4">
                <i class="fa-solid fa-vault"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Aucune caisse active</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-6">Ouvrez une session de caisse avec votre fond initial pour commencer à encaisser les clients.</p>
            <a href="{{ route('caissier.session.open') }}" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition inline-flex items-center gap-2">
                <i class="fa-solid fa-play"></i>
                <span>Ouvrir une caisse</span>
            </a>
        </div>
    @endif

    <!-- PAST SESSIONS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4">Mes Dernières Clôtures de Caisse</h4>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[10px] font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Poste Caisse</th>
                        <th class="py-3 px-4">Période</th>
                        <th class="py-3 px-4">Fond Initial</th>
                        <th class="py-3 px-4">Théorique</th>
                        <th class="py-3 px-4">Réel Déclaré</th>
                        <th class="py-3 px-4">Écart Constaté</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pastSessions as $ps)
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $ps->cashRegister->name }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $ps->opened_at->format('d/m/Y H:i') }} ➔ {{ $ps->closed_at ? $ps->closed_at->format('H:i') : '' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ number_format($ps->opening_amount, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ number_format($ps->closing_amount_theory, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ number_format($ps->closing_amount_real, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 font-bold {{ $ps->difference < 0 ? 'text-rose-600' : ($ps->difference > 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                                {{ number_format($ps->difference, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-slate-400">Aucune clôture enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
