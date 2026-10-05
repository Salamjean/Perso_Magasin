@extends('layouts.app')

@section('title', 'Bilan Journalier & Session de Caisse #' . $session->id)
@section('page_title', 'Bilan de Clôture & Audit de Session #' . $session->id)

@section('content')
<div class="space-y-6 w-full">

    <!-- EN-TÊTE DE LA SESSION & ACTIONS RAPIDES -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0056a6] border border-blue-100 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900">{{ $session->cashRegister->name }} — Session #{{ $session->id }}</h3>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">Code poste : {{ $session->cashRegister->code }}</p>
                </div>

                <div class="ml-2">
                    @if($session->status === 'open')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Session Ouverte (En cours)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Session Clôturée
                        </span>
                    @endif
                </div>
            </div>

            <p class="text-xs text-slate-500 mt-2.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span><i class="fa-solid fa-user mr-1 text-[#0056a6]"></i> Caissier responsable : <strong class="text-slate-800">{{ $session->user->full_name }}</strong></span>
                <span>•</span>
                <span><i class="fa-solid fa-clock mr-1 text-slate-400"></i> Ouverte le : <strong>{{ $session->opened_at->format('d/m/Y à H:i') }}</strong></span>
                @if($session->closed_at)
                    <span>•</span>
                    <span><i class="fa-solid fa-lock mr-1 text-slate-400"></i> Clôturée le : <strong>{{ $session->closed_at->format('d/m/Y à H:i') }}</strong></span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2.5 self-end lg:self-center shrink-0">
            <button type="button" onclick="window.print()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-2xs">
                <i class="fa-solid fa-print text-xs text-slate-600"></i>
                <span>Imprimer le Bilan (Z)</span>
            </button>
            <a href="{{ route('admin.cash-registers.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Retour aux caisses</span>
            </a>
        </div>
    </div>

    <!-- BILAN FINANCIER GLOBAL : 6 CARTES KPI PLEINE LARGEUR -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        
        <!-- FOND DE CAISSE INITIAL -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Fond Initial</span>
            <p class="text-xl font-black text-slate-900 font-mono mt-1">
                {{ number_format($session->opening_amount, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span>
            </p>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Monnaie de départ</span>
        </div>

        <!-- TOTAL VENTES ENCAISSÉES -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Recettes Ventes</span>
            <p class="text-xl font-black text-[#0056a6] font-mono mt-1">
                {{ number_format($stats['total_sales_amount'], 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span>
            </p>
            <span class="text-[11px] text-slate-400 mt-0.5 block">{{ $stats['completed_sales_count'] }} ticket(s) validé(s)</span>
        </div>

        <!-- MONTANT THÉORIQUE ATTENDU -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Montant Théorique</span>
            <p class="text-xl font-black text-slate-900 font-mono mt-1">
                {{ number_format($session->closing_amount_theory, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span>
            </p>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Attendu en espèces</span>
        </div>

        <!-- MONTANT RÉEL DÉCLARÉ -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Montant Déclaré</span>
            <p class="text-xl font-black text-slate-900 font-mono mt-1">
                @if($session->closing_amount_real !== null)
                    {{ number_format($session->closing_amount_real, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span>
                @else
                    <span class="text-slate-400 font-sans text-sm font-normal italic">En cours</span>
                @endif
            </p>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Comptage physique</span>
        </div>

        <!-- ÉCART CONSTATÉ (RÉEL VS THÉORIQUE) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Écart de Caisse</span>
            <div class="mt-1">
                @if($session->difference !== null)
                    <p class="text-xl font-black font-mono {{ $session->difference < 0 ? 'text-rose-600' : ($session->difference > 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                        {{ ($session->difference > 0 ? '+' : '') . number_format($session->difference, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span>
                    </p>
                    <span class="text-[11px] font-bold {{ $session->difference < 0 ? 'text-rose-600' : ($session->difference > 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                        @if($session->difference == 0)
                            ✓ Caisse équilibrée
                        @elseif($session->difference < 0)
                            ⚠️ Manquant en caisse
                        @else
                            ℹ️ Excédent / Surplus
                        @endif
                    </span>
                @else
                    <p class="text-sm font-bold text-slate-400 mt-1">En cours de session</p>
                    <span class="text-[10px] text-slate-400">Calculé à la clôture</span>
                @endif
            </div>
        </div>

        <!-- TOTAL PRISES DE CRÉDIT CLIENTS (NON COMPTABILISÉ DANS LE TIROIR) -->
        <div class="p-5 rounded-2xl border {{ $stats['total_credits'] > 0 ? 'bg-amber-50/70 border-amber-300' : 'bg-white border-slate-200/80' }} shadow-xs">
            <span class="text-[10px] font-bold uppercase {{ $stats['total_credits'] > 0 ? 'text-amber-800' : 'text-slate-400' }} tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-hand-holding-dollar {{ $stats['total_credits'] > 0 ? 'text-amber-600' : 'text-slate-400' }}"></i>
                <span>Prises de Crédit</span>
            </span>
            <p class="text-xl font-black {{ $stats['total_credits'] > 0 ? 'text-amber-900' : 'text-slate-900' }} font-mono mt-1">
                {{ number_format($stats['total_credits'], 0, ',', ' ') }} <span class="text-xs font-sans font-bold {{ $stats['total_credits'] > 0 ? 'text-amber-700' : 'text-slate-500' }}">FCFA</span>
            </p>
            <span class="text-[11px] {{ $stats['total_credits'] > 0 ? 'text-amber-800 font-bold' : 'text-slate-400' }} mt-0.5 block">
                @if($stats['total_credits'] > 0)
                    {{ $stats['credits_count'] }} ticket(s) • Non en caisse
                @else
                    0 FCFA en crédit
                @endif
            </span>
        </div>

    </div>

    <!-- VENTILATION PAR MOYEN DE PAIEMENT & MOUVEMENTS EXCEPTIONNELS (2 COLONNES LARGES) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- VENTILATION PAR MOYEN DE PAIEMENT DU JOUR -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-[#0056a6]"></i>
                    <span>Ventilation des Encaissements du Jour</span>
                </h4>
                <span class="text-[11px] text-slate-400 font-semibold">{{ $stats['completed_sales_count'] }} ventes validées</span>
            </div>

            <div class="grid grid-cols-2 gap-3.5 pt-1">
                <!-- ESPÈCES -->
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100">
                    <div class="flex items-center justify-between text-xs text-emerald-800 font-semibold mb-1">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-money-bill-wave text-emerald-600"></i> Espèces reçues</span>
                    </div>
                    <div class="text-lg font-black text-emerald-950 font-mono">
                        {{ number_format($stats['total_cash'], 0, ',', ' ') }} <span class="text-[11px] font-sans font-bold text-emerald-700">FCFA</span>
                    </div>
                    @if($stats['total_cash_credit'] > 0)
                        <div class="mt-1.5 pt-1.5 border-t border-emerald-200 flex items-center justify-between text-[10px]">
                            <span class="text-amber-700 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-hand-holding-dollar"></i> dont crédit client :
                            </span>
                            <span class="font-black text-amber-800 font-mono">-{{ number_format($stats['total_cash_credit'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                </div>

                <!-- MOBILE MONEY -->
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-100">
                    <div class="flex items-center justify-between text-xs text-amber-800 font-semibold mb-1">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-mobile-screen-button text-amber-600"></i> Mobile Money</span>
                    </div>
                    <div class="text-lg font-black text-amber-950 font-mono">
                        {{ number_format($stats['total_mobile_money'], 0, ',', ' ') }} <span class="text-[11px] font-sans font-bold text-amber-700">FCFA</span>
                    </div>
                </div>

                <!-- CARTE BANCAIRE -->
                <div class="p-4 rounded-xl bg-sky-50/70 border border-sky-100">
                    <div class="flex items-center justify-between text-xs text-sky-800 font-semibold mb-1">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-credit-card text-sky-600"></i> Carte Bancaire</span>
                    </div>
                    <div class="text-lg font-black text-sky-950 font-mono">
                        {{ number_format($stats['total_card'], 0, ',', ' ') }} <span class="text-[11px] font-sans font-bold text-sky-700">FCFA</span>
                    </div>
                </div>

                <!-- VIREMENT & AUTRES -->
                <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-100">
                    <div class="flex items-center justify-between text-xs text-purple-800 font-semibold mb-1">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-building-columns text-purple-600"></i> Virement / Autre</span>
                    </div>
                    <div class="text-lg font-black text-purple-950 font-mono">
                        {{ number_format($stats['total_transfer'], 0, ',', ' ') }} <span class="text-[11px] font-sans font-bold text-purple-700">FCFA</span>
                    </div>
                </div>

                @if($stats['total_credits'] > 0)
                    <!-- CRÉDITS ACCORDÉS (pleine largeur) -->
                    <div class="col-span-2 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-1.5 text-xs text-amber-800 font-bold mb-0.5">
                                <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i>
                                Total Crédits accordés aux clients (non encaissé)
                            </div>
                            <p class="text-[10px] text-amber-600 font-medium">Ces montants sont en dette chez les clients — ils ne sont pas dans le tiroir.</p>
                        </div>
                        <div class="text-lg font-black text-amber-900 font-mono shrink-0 ml-4">
                            {{ number_format($stats['total_credits'], 0, ',', ' ') }} <span class="text-[11px] font-sans font-bold text-amber-700">FCFA</span>
                        </div>
                    </div>
                @endif
            </div>

            @if($stats['total_discounts'] > 0)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                    <span class="text-slate-600 font-medium flex items-center gap-1.5">
                        <i class="fa-solid fa-percent text-slate-400"></i>
                        <span>Total Remises accordées aux clients :</span>
                    </span>
                    <strong class="font-mono text-slate-800 font-bold text-sm">{{ number_format($stats['total_discounts'], 0, ',', ' ') }} FCFA</strong>
                </div>
            @endif
        </div>

        <!-- MOUVEMENTS DE CAISSE EXCEPTIONNELS (ENTRÉES / SORTIES) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-transfer text-[#0056a6]"></i>
                    <span>Mouvements Exceptionnels de Fonds</span>
                </h4>
                <div class="flex items-center gap-2 text-xs font-mono font-bold">
                    <span class="text-emerald-700">+{{ number_format($stats['total_movements_in'], 0, ',', ' ') }} FCFA</span>
                    <span>/</span>
                    <span class="text-rose-700">-{{ number_format($stats['total_movements_out'], 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            <div class="max-h-48 overflow-y-auto divide-y divide-slate-100 text-xs">
                @forelse($session->movements as $m)
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold {{ $m->type === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                <i class="fa-solid {{ $m->type === 'in' ? 'fa-plus' : 'fa-minus' }}"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800">{{ $m->reason }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $m->created_at->format('H:i:s') }} • Opérateur : {{ $m->user->full_name ?? 'Système' }}</p>
                            </div>
                        </div>
                        <span class="font-bold font-mono text-sm {{ $m->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ ($m->type === 'in' ? '+' : '-') . number_format($m->amount, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">Aucun mouvement exceptionnel durant cette session.</p>
                @endforelse
            </div>

            <!-- AJOUT D'UN MOUVEMENT SI SESSION OUVERTE -->
            @if($session->status === 'open')
                <div class="pt-3 border-t border-slate-100">
                    <form action="{{ route('admin.cash-sessions.movement', $session) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                        @csrf
                        <select name="type" required class="px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none">
                            <option value="in">Entrée (+)</option>
                            <option value="out">Sortie (-)</option>
                        </select>
                        <input type="number" step="0.01" name="amount" required placeholder="Montant FCFA" class="px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:outline-none">
                        <input type="text" name="reason" required placeholder="Motif" class="px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none">
                        <button type="submit" class="py-2 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold transition cursor-pointer">
                            Enregistrer
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>

    <!-- TABLEAU PLEINE LARGEUR DE TOUTES LES VENTES DU JOUR -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#0056a6]"></i>
                    <span>Journal complet des ventes de la session</span>
                </h4>
                <p class="text-xs text-slate-400 mt-0.5">Tickets de caisse émis, clients, modes de règlement et totaux</p>
            </div>
            <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold font-mono">
                {{ $session->sales->count() }} vente(s) enregistrée(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">N° Ticket & Heure</th>
                        <th class="py-3.5 px-5 text-center">Client</th>
                        <th class="py-3.5 px-5 text-center">Articles Vendus</th>
                        <th class="py-3.5 px-5 text-center">Moyen Paiement</th>
                        <th class="py-3.5 px-5 text-center">Montant Total</th>
                        <th class="py-3.5 px-5 text-center">Statut</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($session->sales as $sale)
                        <tr class="hover:bg-slate-50/70 transition {{ $sale->status === 'cancelled' ? 'bg-slate-50/40 opacity-75' : '' }}">
                            
                            <!-- TICKET -->
                            <td class="py-3.5 px-5 align-middle">
                                <a href="{{ route('admin.sales.show', $sale) }}" class="font-bold text-slate-900 hover:text-[#0056a6] font-mono text-xs block transition">
                                    {{ $sale->sale_number }}
                                </a>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $sale->created_at->format('d/m/Y à H:i:s') }}</span>
                            </td>

                            <!-- CLIENT (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($sale->customer)
                                    <div class="font-bold text-slate-900 text-xs">{{ $sale->customer->full_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $sale->customer->phone ?? '—' }}</div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500">
                                        Client Comptoir
                                    </span>
                                @endif
                            </td>

                            <!-- ARTICLES (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] rounded-lg font-bold border border-slate-200">
                                    {{ $sale->items->count() }} article(s)
                                </span>
                            </td>

                            <!-- MOYEN PAIEMENT (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($sale->payment_method === 'credit')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                        <i class="fa-solid fa-hand-holding-dollar text-amber-700 text-[10px]"></i> À Crédit (Dette)
                                    </span>
                                @elseif($sale->payment_method === 'cash')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-money-bill-wave text-[10px]"></i> Espèces
                                    </span>
                                @elseif($sale->payment_method === 'mobile_money')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-mobile-screen-button text-[10px]"></i> Mobile Money
                                    </span>
                                @elseif($sale->payment_method === 'card')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        <i class="fa-solid fa-credit-card text-[10px]"></i> Carte Bancaire
                                    </span>
                                @elseif($sale->payment_method === 'transfer')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="fa-solid fa-building-columns text-[10px]"></i> Virement
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                                        {{ $sale->payment_method }}
                                    </span>
                                @endif
                            </td>

                            <!-- MONTANT TOTAL (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="font-black text-slate-900 text-xs font-mono">
                                    {{ number_format($sale->total_amount, 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold text-slate-500">FCFA</span>
                                </div>
                                @if($sale->payment_method === 'credit')
                                    <div class="mt-1 flex flex-col items-center gap-0.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                            100% Crédit (0 FCFA en caisse)
                                        </span>
                                    </div>
                                @elseif($sale->credit_amount > 0)
                                    <div class="mt-1 flex flex-col items-center gap-0.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                            Crédit : {{ number_format($sale->credit_amount, 0, ',', ' ') }} FCFA
                                        </span>
                                        <span class="text-[9px] text-emerald-700 font-bold">
                                            Encaissé : {{ number_format($sale->total_amount - $sale->credit_amount, 0, ',', ' ') }} FCFA
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- STATUT (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($sale->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Validée
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Annulée
                                    </span>
                                @endif
                            </td>

                            <!-- ACTIONS (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.sales.show', $sale) }}" class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0056a6] flex items-center justify-center transition" title="Consulter le ticket de caisse">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Aucune vente enregistrée lors de cette session</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
