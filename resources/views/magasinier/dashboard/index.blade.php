@extends('layouts.app')

@section('title', 'Tableau de bord Magasinier')
@section('page_title', 'Espace Gestion des Stocks & Magasin')

@section('content')
<div class="space-y-6 sm:space-y-8">

    <!-- 4 CARTES KPI STATISTIQUES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">

        <!-- 1. TOTAL PRODUITS & VOLUME -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition group">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Catalogue Actif</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1.5 font-mono">
                        {{ $totalProducts }}
                        <span class="text-xs font-semibold text-slate-400 font-sans">réfs</span>
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-2 font-medium">
                        <i class="fa-solid fa-layer-group text-slate-400 text-[10px]"></i>
                        <span>{{ number_format($totalStockUnits, 0, ',', ' ') }} unités en stock</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#0056a6] flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>

        <!-- 2. RUPTURES TOTALES (STOCK = 0) -->
        <a href="{{ route('magasinier.alerts.index') }}" 
           class="bg-white rounded-2xl p-5 sm:p-6 border {{ $outOfStockProducts > 0 ? 'border-rose-200/80 bg-rose-50/20' : 'border-slate-200/80' }} shadow-xs hover:shadow-md transition group block">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500 block">Ruptures de Stock</span>
                    <h3 class="text-2xl font-black text-rose-600 mt-1.5 font-mono">
                        {{ $outOfStockProducts }}
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs {{ $outOfStockProducts > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }} mt-2">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        <span>{{ $outOfStockProducts > 0 ? 'Réapprovisionnement requis' : 'Aucune rupture totale' }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
        </a>

        <!-- 3. STOCK SOUS SEUIL D'ALERTE -->
        <a href="{{ route('magasinier.alerts.index') }}" 
           class="bg-white rounded-2xl p-5 sm:p-6 border {{ $lowStockProducts > 0 ? 'border-amber-200/80 bg-amber-50/20' : 'border-slate-200/80' }} shadow-xs hover:shadow-md transition group block">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 block">Stock Faible / Seuil</span>
                    <h3 class="text-2xl font-black text-amber-600 mt-1.5 font-mono">
                        {{ $lowStockProducts }}
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs {{ $lowStockProducts > 0 ? 'text-amber-700 font-semibold' : 'text-slate-400' }} mt-2">
                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                        <span>{{ $lowStockProducts > 0 ? 'Sous le seuil minimal' : 'Tous stocks suffisants' }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-chart-line-down"></i>
                </div>
            </div>
        </a>

        <!-- 4. RÉCEPTIONS FOURNISSEURS EN ATTENTE -->
        <a href="{{ route('magasinier.receptions.index') }}" 
           class="bg-white rounded-2xl p-5 sm:p-6 border {{ $pendingPurchasesCount > 0 ? 'border-blue-200 bg-blue-50/20' : 'border-slate-200/80' }} shadow-xs hover:shadow-md transition group block">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#0056a6] block">Réceptions à Traiter</span>
                    <h3 class="text-2xl font-black text-[#0056a6] mt-1.5 font-mono">
                        {{ $pendingPurchasesCount }}
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs {{ $pendingPurchasesCount > 0 ? 'text-blue-700 font-semibold' : 'text-slate-400' }} mt-2">
                        <i class="fa-solid fa-truck-ramp-box text-[10px]"></i>
                        <span>{{ $pendingPurchasesCount > 0 ? 'Commandes en attente de BL' : 'Toutes commandes reçues' }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#0056a6] flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-dolly"></i>
                </div>
            </div>
        </a>

    </div>

    <!-- HUB DES ACTIONS OPÉRATIONNELLES (4 RACCOURCIS) -->
    <div>
        <div class="flex items-center justify-between mb-3.5 px-1">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i>
                <span>Actions Rapides du Magasin</span>
            </h3>
            <span class="text-xs text-slate-400">Opérations quotidiennes</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- 1. ENTRÉE DE STOCK -->
            <a href="{{ route('magasinier.stock.entry') }}" 
               class="bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all group flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-lg group-hover:bg-emerald-600 group-hover:text-white transition-colors shadow-2xs">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">+ Stock</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">Entrée de Stock</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Ajout direct de marchandise & BL</p>
                </div>
            </a>

            <!-- 2. SORTIE / CASSE / PERTE -->
            <a href="{{ route('magasinier.stock.exit') }}" 
               class="bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-rose-300 hover:shadow-md transition-all group flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-lg group-hover:bg-rose-600 group-hover:text-white transition-colors shadow-2xs">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                    <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full">- Stock</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-rose-700 transition-colors">Sortie / Casse / Perte</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Déclarer produit abîmé ou périmé</p>
                </div>
            </a>

            <!-- 3. INVENTAIRE PHYSIQUE -->
            <a href="{{ route('magasinier.inventory.create') }}" 
               class="bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-amber-300 hover:shadow-md transition-all group flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-lg group-hover:bg-amber-600 group-hover:text-white transition-colors shadow-2xs">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Comptage</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-amber-700 transition-colors">Faire un Inventaire</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Comptage physique & ajustements</p>
                </div>
            </a>

            <!-- 4. RÉCEPTIONS FOURNISSEURS -->
            <a href="{{ route('magasinier.receptions.index') }}" 
               class="bg-white p-5 rounded-2xl border border-slate-200/80 hover:border-blue-300 hover:shadow-md transition-all group flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 text-[#0056a6] flex items-center justify-center text-lg group-hover:bg-[#0056a6] group-hover:text-white transition-colors shadow-2xs">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </div>
                    <span class="text-[10px] font-bold text-[#0056a6] bg-blue-50 px-2 py-0.5 rounded-full">{{ $pendingPurchasesCount }} en attente</span>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-[#0056a6] transition-colors">Réceptions Fournisseurs</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Pointer les livraisons de commande</p>
                </div>
            </a>

        </div>
    </div>

    <!-- COMMANDES EN ATTENTE DE RÉCEPTION (SI PRÉSENTES) -->
    @if($pendingPurchases->count() > 0)
        <div class="bg-white rounded-2xl border border-blue-200/80 shadow-xs p-5 sm:p-6 overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                        <i class="fa-solid fa-truck-loading"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Commandes Fournisseurs en Attente de Livraison</h3>
                        <p class="text-xs text-slate-500">Pointez la réception physique des cartons et mettez les stocks à jour</p>
                    </div>
                </div>
                <a href="{{ route('magasinier.receptions.index') }}" class="text-xs font-bold text-[#0056a6] hover:underline flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Toutes les réceptions</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3.5 pt-4">
                @foreach($pendingPurchases as $purchase)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $purchase->reference }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $purchase->status === 'partial' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-[#0056a6]' }}">
                                    {{ $purchase->status === 'partial' ? 'Partielle' : 'Commandée' }}
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-slate-700 mt-1">{{ $purchase->supplier->name ?? 'Fournisseur' }}</p>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Commandée le {{ $purchase->created_at->format('d/m/Y') }}</span>
                        </div>

                        <a href="{{ route('magasinier.receptions.process', $purchase) }}" 
                           class="w-full py-2 bg-[#0056a6] hover:bg-blue-700 text-white rounded-lg text-xs font-bold text-center transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                            <i class="fa-solid fa-clipboard-check text-[11px]"></i>
                            <span>Réceptionner</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- SECTION PRINCIPALE : ALERTES CRITIQUES & DERNIERS MOUVEMENTS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 1. ARTICLES CRITIQUES & RUPTURES -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Articles Critiques & Ruptures</h3>
                            <p class="text-[11px] text-slate-400">Priorités de réapprovisionnement</p>
                        </div>
                    </div>
                    <a href="{{ route('magasinier.alerts.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                        Voir tout ({{ $outOfStockProducts + $lowStockProducts }})
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($criticalProducts as $p)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3 hover:bg-slate-100/70 transition">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-900 block truncate">{{ $p->name }}</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-400 font-mono">Réf: {{ $p->reference }}</span>
                                    @if($p->category)
                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-200/70 text-slate-600 font-medium">{{ $p->category->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                @if($p->isOutOfStock())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-xmark text-[10px]"></i>
                                        <span>Rupture (0 {{ $p->unit ?? 'u' }})</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                        <span>{{ (float)$p->stock_quantity }} / Seuil {{ (float)$p->alert_threshold }} {{ $p->unit ?? 'u' }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-lg">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">Aucun produit en rupture ou en alerte critique.</p>
                            <span class="text-[11px] text-slate-400">Tous les stocks sont au-dessus de leur seuil minimal.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100">
                <a href="{{ route('magasinier.stock.entry') }}" 
                   class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-down text-emerald-600 text-xs"></i>
                    <span>Enregistrer une entrée de réapprovisionnement</span>
                </a>
            </div>
        </div>

        <!-- 2. DERNIERS MOUVEMENTS DE STOCK -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Journal des Flux Récents</h3>
                            <p class="text-[11px] text-slate-400">Historique des entrées, sorties et ajustements</p>
                        </div>
                    </div>
                    <a href="{{ route('magasinier.stock.index') }}" class="text-xs font-bold text-[#0056a6] hover:underline">
                        Voir tout
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentMovements as $m)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3 hover:bg-slate-100/70 transition">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-900 block truncate">{{ $m->product->name ?? 'Article inconnu' }}</span>
                                <p class="text-[10px] text-slate-400 mt-0.5 truncate">
                                    {{ $m->created_at->diffForHumans() }} • {{ $m->reason }}
                                    @if($m->user)
                                        • <span class="text-slate-500 font-medium">{{ $m->user->first_name }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                @if($m->type === 'in' || $m->type === 'return')
                                    <span class="font-mono font-black text-xs text-emerald-600">
                                        +{{ (float)$m->quantity }} {{ $m->product->unit ?? 'u' }}
                                    </span>
                                    <span class="block text-[9px] font-bold text-emerald-700 uppercase">Entrée</span>
                                @elseif($m->type === 'out')
                                    <span class="font-mono font-black text-xs text-rose-600">
                                        -{{ (float)$m->quantity }} {{ $m->product->unit ?? 'u' }}
                                    </span>
                                    <span class="block text-[9px] font-bold text-rose-700 uppercase">Sortie</span>
                                @elseif($m->type === 'adjustment')
                                    <span class="font-mono font-black text-xs text-amber-600">
                                        ±{{ (float)$m->quantity }} {{ $m->product->unit ?? 'u' }}
                                    </span>
                                    <span class="block text-[9px] font-bold text-amber-700 uppercase">Ajustement</span>
                                @else
                                    <span class="font-mono font-black text-xs text-blue-600">
                                        -{{ (float)$m->quantity }} {{ $m->product->unit ?? 'u' }}
                                    </span>
                                    <span class="block text-[9px] font-bold text-blue-700 uppercase">Vente</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-slate-400">
                            <i class="fa-solid fa-boxes-packing text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs font-semibold text-slate-700">Aucun mouvement de stock enregistré récemment.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100">
                <a href="{{ route('magasinier.stock.index') }}" 
                   class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-list-ul text-[#0056a6] text-xs"></i>
                    <span>Consulter tous les mouvements de stock</span>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
