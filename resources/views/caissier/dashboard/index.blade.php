@extends('layouts.app')

@section('title', 'Tableau de bord Caisse')
@section('page-title', 'Espace Caisse & Encaissements')

@section('content')
<div class="space-y-8">

    <!-- CARTE D'ÉTAT DE SESSION (HERO) -->
    @if($activeSession)
        <div class="relative overflow-hidden rounded-3xl bg-[#0056a6] text-white shadow-xl shadow-blue-900/30 border border-blue-400/20">
            <!-- Motif décoratif -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -right-20 -top-20 w-72 h-72 rounded-full bg-white/5"></div>
                <div class="absolute right-10 top-6 w-32 h-32 rounded-full bg-white/5"></div>
                <div class="absolute -left-10 -bottom-10 w-48 h-48 rounded-full bg-black/10"></div>
            </div>

            <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-6 p-6 sm:p-7">
                <!-- Statut + actions -->
                <div class="flex flex-col gap-4">
                    <!-- Badge statut -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-white/15 border border-white/25 backdrop-blur-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-sm shadow-emerald-400"></span>
                            Caisse Ouverte
                        </span>
                        <span class="text-xs text-blue-100 font-medium">
                            <i class="fa-regular fa-clock mr-1"></i>Depuis {{ $activeSession->opened_at->format('H:i') }}
                            &nbsp;&bull;&nbsp;
                            <i class="fa-solid fa-wallet mr-1"></i>Fond : <strong class="text-white">{{ number_format($activeSession->opening_amount, 0, ',', ' ') }} FCFA</strong>
                        </span>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('caissier.pos.index') }}"
                           class="inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl bg-white text-[#0056a6] font-extrabold text-sm shadow-lg shadow-black/15 hover:bg-blue-50 transition-all transform hover:-translate-y-0.5 active:scale-[.98]">
                            <i class="fa-solid fa-cash-register text-base"></i>
                            <span>Point de Vente (TPV)</span>
                        </a>
                        <a href="{{ route('caissier.sales.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/20 transition">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>Mes Ventes</span>
                        </a>
                        <a href="{{ route('caissier.session.close') }}"
                           class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/10 hover:bg-rose-500/30 text-white font-bold text-xs border border-white/15 hover:border-rose-400/40 transition">
                            <i class="fa-solid fa-power-off text-rose-300"></i>
                            <span>Fermer la Caisse</span>
                        </a>
                    </div>
                </div>

                <!-- 4 KPI de session -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full xl:w-auto xl:min-w-[580px]">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/15">
                        <span class="text-[10px] uppercase font-bold text-blue-200 block mb-1">Ventes Session</span>
                        <div class="text-2xl font-black text-white">{{ number_format($sessionTotalSales, 0, ',', ' ') }}</div>
                        <span class="text-[11px] text-blue-200 font-semibold">FCFA &bull; {{ $sessionSalesCount }} ticket(s)</span>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/15">
                        <span class="text-[10px] uppercase font-bold text-blue-200 block mb-1">Espèces Encaissées</span>
                        <div class="text-2xl font-black text-emerald-300">{{ number_format($sessionCashSales, 0, ',', ' ') }}</div>
                        <span class="text-[11px] text-blue-200 font-semibold">FCFA liquide</span>
                    </div>

                    <div class="bg-purple-950/30 backdrop-blur-sm rounded-2xl p-4 border border-purple-300/30 shadow-inner">
                        <span class="text-[10px] uppercase font-bold text-purple-200 block mb-1 flex items-center gap-1">
                            <i class="fa-solid fa-hand-holding-dollar text-purple-300"></i> Ventes Crédit
                        </span>
                        <div class="text-2xl font-black text-purple-200">{{ number_format($sessionCreditSales, 0, ',', ' ') }}</div>
                        <span class="text-[11px] text-purple-300 font-semibold">FCFA &bull; {{ $sessionCreditCount }} crédit(s)</span>
                    </div>

                    <div class="bg-white/15 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <span class="text-[10px] uppercase font-bold text-blue-100 block mb-1">Tiroir Théorique</span>
                        <div class="text-2xl font-black text-white">{{ number_format($theoreticalDrawerAmount, 0, ',', ' ') }}</div>
                        <span class="text-[11px] text-blue-100 font-semibold">FCFA liquide attendu</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="relative overflow-hidden bg-gradient-to-r from-amber-50/90 via-white to-amber-50/50 rounded-3xl p-7 sm:p-8 shadow-md border-2 border-amber-300/90 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center text-2xl shadow-lg shadow-slate-900/20 border border-slate-800 flex-shrink-0">
                    <i class="fa-solid fa-lock text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                            <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                            Caisse Actuellement Fermée
                        </span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Aucune session de caisse ouverte pour votre compte
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-700 font-medium max-w-2xl leading-relaxed">
                        Pour commencer vos encaissements et imprimer des tickets clients, veuillez déclarer votre fond de caisse initial et ouvrir votre poste.
                    </p>
                </div>
            </div>
            <a href="{{ route('caissier.session.open') }}" class="px-7 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm shadow-xl shadow-emerald-600/30 transition-all transform hover:-translate-y-0.5 flex items-center gap-2.5 whitespace-nowrap">
                <i class="fa-solid fa-vault text-base"></i>
                <span>Ouvrir ma Session de Caisse</span>
            </a>
        </div>
    @endif

    <!-- STATISTIQUES DU JOUR (5 CARTES AVEC CRÉDIT) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Chiffre d'Affaires du Jour -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Ventes Jour</span>
                <div class="w-9 h-9 rounded-2xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-sm">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <h3 class="text-xl font-black text-slate-900 mt-2.5">{{ number_format($totalCollectedToday, 0, ',', ' ') }} <span class="text-[10px] font-bold text-slate-400">FCFA</span></h3>
            <div class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                <i class="fa-solid fa-arrow-trend-up text-[10px]"></i>
                <span>{{ $salesCountToday }} transaction(s)</span>
            </div>
        </div>

        <!-- Espèces Encaissées -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Espèces</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
            <h3 class="text-xl font-black text-emerald-600 mt-2.5">{{ number_format($cashTotal, 0, ',', ' ') }} <span class="text-[10px] font-bold text-slate-400">FCFA</span></h3>
            <div class="mt-1.5 text-[11px] text-slate-500 font-medium">
                {{ $cashPercentage }}% du chiffre du jour
            </div>
        </div>

        <!-- Mobile Money & Cartes -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">M-Money & Cartes</span>
                <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>
            </div>
            <h3 class="text-xl font-black text-amber-600 mt-2.5">{{ number_format($mobileMoneyTotal + $cardTotal, 0, ',', ' ') }} <span class="text-[10px] font-bold text-slate-400">FCFA</span></h3>
            <div class="mt-1.5 text-[11px] text-slate-500 font-medium">
                {{ $mobileMoneyPercentage + $cardPercentage }}% du chiffre du jour
            </div>
        </div>

        <!-- Ventes à Crédit -->
        <div class="bg-white rounded-3xl p-5 border border-purple-200 shadow-xs relative overflow-hidden group hover:shadow-md transition bg-gradient-to-br from-white via-white to-purple-50/40">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600">Ventes à Crédit</span>
                <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <h3 class="text-xl font-black text-purple-700 mt-2.5">{{ number_format($creditTotal, 0, ',', ' ') }} <span class="text-[10px] font-bold text-slate-400">FCFA</span></h3>
            <div class="mt-1.5 text-[11px] font-bold text-purple-700 flex items-center gap-1">
                <span>{{ $creditCountToday }} crédit(s) accordé(s) ({{ $creditPercentage }}%)</span>
            </div>
        </div>

        <!-- Panier Moyen -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Panier Moyen</span>
                <div class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
            </div>
            <h3 class="text-xl font-black text-slate-900 mt-2.5">{{ number_format($averageBasket, 0, ',', ' ') }} <span class="text-[10px] font-bold text-slate-400">FCFA</span></h3>
            <div class="mt-1.5 text-[11px] text-slate-500 font-medium">
                Moyenne par ticket client
            </div>
        </div>
    </div>

    <!-- CORPS PRINCIPAL : DERNIÈRES VENTES + RÉPARTITIONS ET RACCOURCIS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- COLONNE GAUCHE : TABLEAU DES DERNIÈRES VENTES (8 colonnes) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center text-base">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900">Dernières Ventes Enregistrées</h3>
                            <p class="text-xs text-slate-500 font-medium">Historique en temps réel de vos encaissements</p>
                        </div>
                    </div>
                    <a href="{{ route('caissier.sales.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0056a6] hover:text-[#004482] transition group">
                        <span>Voir tout l'historique</span>
                        <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/75 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-4 rounded-l-xl">N° Ticket</th>
                                <th class="py-3.5 px-4">Heure</th>
                                <th class="py-3.5 px-4">Client</th>
                                <th class="py-3.5 px-4">Articles</th>
                                <th class="py-3.5 px-4">Mode de Règlement</th>
                                <th class="py-3.5 px-4 font-black">Montant Net</th>
                                <th class="py-3.5 px-4 text-center">Statut</th>
                                <th class="py-3.5 px-4 text-right rounded-r-xl">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentSales as $sale)
                                <tr class="hover:bg-slate-50/80 transition group {{ $sale->status === 'cancelled' ? 'bg-rose-50/30' : '' }}">
                                    <!-- N° Ticket -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-black text-slate-900 {{ $sale->status === 'cancelled' ? 'text-rose-700' : '' }}">{{ $sale->sale_number }}</div>
                                    </td>
                                    <!-- Heure -->
                                    <td class="py-3.5 px-4 text-slate-500 font-medium">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-clock text-[10px] text-slate-400"></i>
                                            {{ $sale->created_at->format('H:i') }}
                                        </span>
                                    </td>
                                    <!-- Client -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800">
                                            {{ $sale->customer->full_name ?? 'Client Comptoir' }}
                                        </div>
                                    </td>
                                    <!-- Articles -->
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px]">
                                            {{ $sale->items->count() }} art.
                                        </span>
                                    </td>
                                    <!-- Mode Règlement -->
                                    <td class="py-3.5 px-4">
                                        @if($sale->payment_method === 'cash')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200">
                                                <i class="fa-solid fa-money-bill"></i> Espèces
                                            </span>
                                        @elseif($sale->payment_method === 'credit')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-bold text-[10px] border border-purple-200">
                                                <i class="fa-solid fa-hand-holding-dollar text-purple-600"></i> Crédit
                                            </span>
                                        @elseif($sale->payment_method === 'mobile_money')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold text-[10px] border border-amber-200">
                                                <i class="fa-solid fa-mobile-screen"></i> Mobile Money
                                            </span>
                                        @elseif($sale->payment_method === 'card')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-[10px] border border-indigo-200">
                                                <i class="fa-regular fa-credit-card"></i> Carte
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[10px]">
                                                {{ ucfirst($sale->payment_method ?? 'Autre') }}
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Montant Total -->
                                    <td class="py-3.5 px-4 font-black text-sm {{ $sale->status === 'cancelled' ? 'text-slate-400 line-through' : 'text-slate-900' }}">
                                        {{ number_format($sale->total_amount, 0, ',', ' ') }} <span class="text-[10px] font-semibold text-slate-400">FCFA</span>
                                    </td>
                                    <!-- Statut -->
                                    <td class="py-3.5 px-4 text-center">
                                        @if($sale->status === 'completed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Validée
                                            </span>
                                        @elseif($sale->status === 'cancelled' || $sale->status === 'refunded')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $sale->cancellation_reason ? 'Motif : ' . $sale->cancellation_reason : 'Vente annulée' }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Annulée
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 text-[10px] rounded-full font-semibold border border-slate-200">
                                                {{ ucfirst($sale->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Action -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('caissier.pos.receipt', $sale) }}" target="_blank" title="Imprimer le ticket" class="p-2 bg-slate-100 hover:bg-[#0056a6] text-slate-600 hover:text-white rounded-xl font-bold text-xs transition shadow-sm">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                            <a href="{{ route('caissier.sales.show', $sale) }}" title="Voir détails" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs transition">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-12">
                                        <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
                                            <i class="fa-solid fa-cart-shopping"></i>
                                        </div>
                                        <h4 class="font-bold text-slate-700 text-sm">Aucune vente effectuée aujourd'hui</h4>
                                        <p class="text-xs text-slate-400 mt-1">Ouvrez le TPV pour débuter vos premiers encaissements de la journée.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE : RÉPARTITION PAIEMENTS, TOP ARTICLES & OUTILS (4 colonnes) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- RÉPARTITION DES MODES DE PAIEMENT DU JOUR -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <h3 class="text-sm font-black text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-[#0056a6]"></i>
                    <span>Modes de Règlement du Jour</span>
                </h3>

                <div class="space-y-4">
                    <!-- Espèces -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="text-slate-700 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Espèces
                            </span>
                            <span class="text-slate-900 font-black">{{ number_format($cashTotal, 0, ',', ' ') }} FCFA <span class="text-slate-400 font-medium">({{ $cashPercentage }}%)</span></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $cashPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- Ventes à Crédit -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="text-slate-700 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                Ventes à Crédit
                            </span>
                            <span class="text-purple-700 font-black">{{ number_format($creditTotal, 0, ',', ' ') }} FCFA <span class="text-slate-400 font-medium">({{ $creditPercentage }}%)</span></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-purple-600 h-full rounded-full transition-all duration-500" style="width: {{ $creditPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- Mobile Money -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="text-slate-700 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                Mobile Money
                            </span>
                            <span class="text-slate-900 font-black">{{ number_format($mobileMoneyTotal, 0, ',', ' ') }} FCFA <span class="text-slate-400 font-medium">({{ $mobileMoneyPercentage }}%)</span></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $mobileMoneyPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- Carte Bancaire -->
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="text-slate-700 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                Cartes Bancaires
                            </span>
                            <span class="text-slate-900 font-black">{{ number_format($cardTotal, 0, ',', ' ') }} FCFA <span class="text-slate-400 font-medium">({{ $cardPercentage }}%)</span></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ $cardPercentage }}%"></div>
                        </div>
                    </div>

                    @if($otherTotal > 0)
                        <!-- Autres -->
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1.5">
                                <span class="text-slate-700 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                                    Autres (Virements...)
                                </span>
                                <span class="text-slate-900 font-black">{{ number_format($otherTotal, 0, ',', ' ') }} FCFA <span class="text-slate-400 font-medium">({{ $otherPercentage }}%)</span></span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-slate-400 h-full rounded-full transition-all duration-500" style="width: {{ $otherPercentage }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- RACCOURCIS & OUTILS RAPIDES -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <h3 class="text-sm font-black text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-[#0056a6]"></i>
                    <span>Raccourcis Caisse</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                    <a href="{{ route('caissier.pos.index') }}" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-blue-50/80 border border-slate-100 hover:border-blue-200 transition text-center group">
                        <i class="fa-solid fa-cart-plus text-base text-[#0056a6] mb-1.5 block group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-black text-slate-800 group-hover:text-[#0056a6] block truncate">Nouveau Ticket</span>
                    </a>

                    <a href="{{ route('caissier.sales.index') }}" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition text-center group">
                        <i class="fa-solid fa-list-check text-base text-slate-600 mb-1.5 block group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-black text-slate-800 block truncate">Historique</span>
                    </a>

                    <a href="{{ route('caissier.deliveries.index') }}" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-blue-50/80 border border-slate-100 hover:border-blue-200 transition text-center group">
                        <i class="fa-solid fa-motorcycle text-base text-[#0056a6] mb-1.5 block group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-black text-slate-800 group-hover:text-[#0056a6] block truncate">Livraisons</span>
                    </a>

                    <a href="{{ route('caissier.returns.index') }}" class="p-3.5 rounded-2xl bg-slate-50 hover:bg-rose-50/80 border border-slate-100 hover:border-rose-200 transition text-center group">
                        <i class="fa-solid fa-rotate-left text-base text-rose-500 mb-1.5 block group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-black text-slate-800 group-hover:text-rose-600 block truncate">Retours</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection


