@extends('layouts.app')

@section('title', 'Journal des Ventes')
@section('page_title', 'Journal des Ventes du Supermarché')

@section('content')
<div class="space-y-6">

    <!-- KPI STATS SUMMARY (DESIGN ÉPURÉ ET CENTRÉ SANS DÉGRADÉ) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- TOTAL TRANSACTIONS -->
        <a href="{{ route('admin.sales.index') }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-[#0056a6]/40 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Ventes</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_sales'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-[#0056a6] transition">Tickets émis</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </a>

        <!-- CHIFFRE D'AFFAIRES ENCAISSÉ -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Chiffre d'Affaires</p>
                <h3 class="text-xl font-extrabold text-emerald-600 mt-1 font-mono">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} <span class="text-xs font-sans text-slate-500 font-bold">FCFA</span></h3>
                <span class="text-[10px] text-slate-400 font-medium">Recettes validées</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-cash-register"></i>
            </div>
        </div>

        <!-- VENTES VALIDÉES -->
        <a href="{{ route('admin.sales.index', ['status' => 'completed']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ventes Validées</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['completed'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-emerald-600 transition">Transactions abouties</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </a>

        <!-- VENTES ANNULÉES -->
        <a href="{{ route('admin.sales.index', ['status' => 'cancelled']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-rose-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ventes Annulées</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($stats['cancelled'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-rose-600 transition">Tickets remboursés/annulés</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
        </a>

    </div>

    <!-- BARRE DE RECHERCHE ET FILTRES MULTI-CRITÈRES -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.sales.index') }}" class="flex flex-wrap items-center gap-3">
            
            <!-- RECHERCHE PAR MOT-CLÉ -->
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="N° Vente, nom ou contact client..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
            </div>

            <!-- FILTRE PAIEMENT -->
            <div class="w-full sm:w-auto">
                <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les paiements</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>💵 Espèces</option>
                    <option value="mobile_money" {{ request('payment_method') == 'mobile_money' ? 'selected' : '' }}>📱 Mobile Money</option>
                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>💳 Carte Bancaire</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>🏦 Virement</option>
                    <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>🤝 À Crédit (Dette)</option>
                </select>
            </div>

            <!-- FILTRE CAISSIER -->
            <div class="w-full sm:w-auto">
                <select name="user_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les caissiers</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- FILTRE STATUT -->
            <div class="w-full sm:w-auto">
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les statuts</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Validée</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>

            <!-- FILTRE DATE -->
            <div class="w-full sm:w-auto">
                <input type="date" name="date" value="{{ request('date') }}" 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
            </div>

            <!-- BOUTONS ACTION -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filtrer</span>
                </button>
                @if(request()->hasAny(['search', 'payment_method', 'user_id', 'status', 'date']))
                    <a href="{{ route('admin.sales.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Réinitialiser les filtres">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABLEAU DU JOURNAL DES VENTES (ÉLÉMENTS PARFAITEMENT CENTRÉS) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">N° Vente & Heure</th>
                        <th class="py-3.5 px-5 text-center">Caissier</th>
                        <th class="py-3.5 px-5 text-center">Client</th>
                        <th class="py-3.5 px-5 text-center">Moyen de Paiement</th>
                        <th class="py-3.5 px-5 text-center">Montant Total</th>
                        <th class="py-3.5 px-5 text-center">Statut</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/70 transition {{ $sale->status === 'cancelled' ? 'bg-slate-50/40 opacity-75' : '' }}">
                            
                            <!-- COLONNE 1 : N° VENTE & HEURE -->
                            <td class="py-3.5 px-5 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0056a6] border border-blue-100 flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="font-bold text-slate-900 hover:text-[#0056a6] font-mono text-xs block transition" title="Consulter la vente">
                                            {{ $sale->sale_number }}
                                        </a>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                            {{ $sale->created_at->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- COLONNE 2 : CAISSIER (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 text-[11px] font-semibold">
                                    <i class="fa-solid fa-user text-[10px] text-slate-400"></i>
                                    <span>{{ $sale->user->full_name ?? 'Caissier' }}</span>
                                </div>
                            </td>

                            <!-- COLONNE 3 : CLIENT (CENTRÉ) -->
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

                            <!-- COLONNE 4 : PAIEMENT (CENTRÉ) -->
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

                            <!-- COLONNE 5 : MONTANT TOTAL (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="font-black text-slate-900 text-xs font-mono">
                                    {{ number_format($sale->total_amount, 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold text-slate-500">FCFA</span>
                                </div>
                            </td>

                            <!-- COLONNE 6 : STATUT (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($sale->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Validée
                                    </span>
                                @elseif($sale->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Annulée
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- COLONNE 7 : ACTIONS (CENTRÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.sales.show', $sale) }}" 
                                       class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0056a6] flex items-center justify-center transition" 
                                       title="Consulter le ticket">
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
                                <p class="text-xs font-semibold text-slate-600">Aucune vente trouvée</p>
                                <p class="text-[11px] text-slate-400 mt-1">Aucune transaction ne correspond à vos filtres de recherche.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
