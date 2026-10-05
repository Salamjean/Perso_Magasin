@extends('layouts.app')

@section('title', 'Gestion des Produits')
@section('page_title', 'Catalogue des Produits')

@section('content')
<div class="space-y-6">

    <!-- KPI STATS SUMMARY (DESIGN ÉPURÉ & MODERNE SANS DÉGRADÉ) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- TOTAL RÉFÉRENCES -->
        <a href="{{ route('admin.products.index') }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-[#0056a6]/40 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Références</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-[#0056a6] transition">Articles enregistrés</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </a>

        <!-- EN STOCK NORMAL -->
        <a href="{{ route('admin.products.index', ['stock_filter' => 'ok']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">En Stock Normal</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['in_stock'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-emerald-600 transition">Niveau suffisant</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </a>

        <!-- STOCK FAIBLE / ALERTE -->
        <a href="{{ route('admin.products.index', ['stock_filter' => 'low']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-amber-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stock Faible</p>
                <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['low_stock'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-amber-600 transition">Sous le seuil d'alerte</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </a>

        <!-- RUPTURE DE STOCK -->
        <a href="{{ route('admin.products.index', ['stock_filter' => 'out']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-rose-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rupture de Stock</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($stats['out_of_stock'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-rose-600 transition">Quantité nulle (0)</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
        </a>

    </div>

    <!-- BARRE D'ACTIONS & FILTRES MULTI-CRITÈRES -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
        
        <!-- FORMULAIRE DE RECHERCHE & FILTRAGE -->
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            
            <!-- RECHERCHE PAR MOT-CLÉ -->
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Nom, code-barres, référence..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
            </div>

            <!-- FILTRE CATÉGORIE -->
            <div class="w-full sm:w-auto">
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- FILTRE FOURNISSEUR -->
            <div class="w-full sm:w-auto">
                <select name="supplier_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les fournisseurs</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- FILTRE ÉTAT DU STOCK -->
            <div class="w-full sm:w-auto">
                <select name="stock_filter" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les états de stock</option>
                    <option value="ok" {{ request('stock_filter') == 'ok' ? 'selected' : '' }}>En stock normal</option>
                    <option value="low" {{ request('stock_filter') == 'low' ? 'selected' : '' }}>⚠️ Stock faible / Alerte</option>
                    <option value="out" {{ request('stock_filter') == 'out' ? 'selected' : '' }}>🔴 En rupture</option>
                </select>
            </div>

            <!-- BOUTONS FILTRER ET RÉINITIALISER -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filtrer</span>
                </button>
                @if(request()->hasAny(['search', 'category_id', 'supplier_id', 'stock_filter']))
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Réinitialiser les filtres">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- BOUTON AJOUT PRODUIT AVEC ÉTAPE SCAN -->
        <a href="{{ route('admin.products.create') }}" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer shrink-0">
            <i class="fa-solid fa-barcode text-xs"></i>
            <span>Nouveau Produit</span>
        </a>
    </div>

    <!-- TABLEAU MODERNE DES PRODUITS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Produit & Code-barres</th>
                        <th class="py-3.5 px-5">Rayon / Catégorie</th>
                        <th class="py-3.5 px-5">Fournisseur</th>
                        <th class="py-3.5 px-5 text-right">Prix Unitaire</th>
                        <th class="py-3.5 px-5 text-center">Stock Actuel</th>
                        <th class="py-3.5 px-5 text-center">Statut</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/70 transition {{ !$product->is_active ? 'bg-slate-50/40 opacity-75' : '' }}">
                            
                            <!-- COLONNE 1 : VISUEL + NOM + CODE-BARRES + RÉF -->
                            <td class="py-3.5 px-5 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden shrink-0">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-box text-base text-slate-300"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.show', $product) }}" class="font-bold text-slate-900 hover:text-[#0056a6] transition truncate block max-w-xs" title="{{ $product->name }}">
                                            {{ $product->name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                            @if($product->barcode)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 rounded text-[10px] font-mono text-slate-600 font-semibold border border-slate-200" title="Code-barres">
                                                    <i class="fa-solid fa-barcode text-[9px] text-slate-400"></i>
                                                    {{ $product->barcode }}
                                                </span>
                                            @endif
                                            <span class="text-[10px] text-slate-400 font-mono">
                                                Réf: {{ $product->reference }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- COLONNE 2 : RAYON / CATÉGORIE -->
                            <td class="py-3.5 px-5 align-middle text-slate-600">
                                @if($product->category)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-50 border border-slate-200 text-slate-700">
                                        <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $product->category->color ?? '#0056a6' }}"></span>
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Sans catégorie</span>
                                @endif
                            </td>

                            <!-- COLONNE 3 : FOURNISSEUR -->
                            <td class="py-3.5 px-5 align-middle text-slate-600">
                                @if($product->supplier)
                                    <div class="font-semibold text-slate-800 text-[11px]">{{ $product->supplier->company_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $product->supplier->phone ?? '—' }}</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Non assigné</span>
                                @endif
                            </td>

                            <!-- COLONNE 4 : PRIX UNITAIRE (VENTE) -->
                            <td class="py-3.5 px-5 text-right align-middle">
                                <div class="font-extrabold text-slate-900 text-[13px] font-mono">
                                    {{ number_format($product->sell_price, 0, ',', ' ') }} <span class="text-[10px] text-slate-500 font-sans font-bold">FCFA</span>
                                </div>
                            </td>

                            <!-- COLONNE 5 : STOCK ACTUEL -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($product->isOutOfStock())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        Rupture (0 {{ $product->unit ?? 'u' }})
                                    </span>
                                @elseif($product->isLowStock())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Seuil d'alerte: {{ (float)$product->alert_threshold }}">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px] text-amber-500 mr-0.5"></i>
                                        {{ (float)$product->stock_quantity }} {{ $product->unit ?? 'u' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ (float)$product->stock_quantity }} {{ $product->unit ?? 'u' }}
                                    </span>
                                @endif
                            </td>

                            <!-- COLONNE 6 : STATUT -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($product->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Inactif
                                    </span>
                                @endif
                            </td>

                            <!-- COLONNE 7 : ACTIONS -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    
                                    <!-- VOIR FICHE PRODUIT -->
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0056a6] flex items-center justify-center transition" 
                                       title="Consulter la fiche">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    <!-- MODIFIER -->
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                                       title="Modifier le produit">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>

                                    <!-- ACTIVER / DÉSACTIVER -->
                                    <form action="{{ route('admin.products.toggle-active', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($product->is_active)
                                            <button type="button" 
                                                onclick="confirmAction(this.closest('form'), {
                                                    title: 'Désactiver ce produit ?',
                                                    text: 'Le produit {{ addslashes($product->name) }} ne sera plus disponible à la caisse.',
                                                    confirmText: 'Oui, désactiver',
                                                    confirmColor: '#d97706',
                                                    icon: 'warning'
                                                })"
                                                class="w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition cursor-pointer" 
                                                title="Désactiver">
                                                <i class="fa-solid fa-ban text-xs"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                onclick="confirmAction(this.closest('form'), {
                                                    title: 'Activer ce produit ?',
                                                    text: 'Le produit {{ addslashes($product->name) }} sera de nouveau disponible au catalogue.',
                                                    confirmText: 'Oui, activer',
                                                    confirmColor: '#0056a6',
                                                    icon: 'question'
                                                })"
                                                class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition cursor-pointer" 
                                                title="Activer">
                                                <i class="fa-solid fa-check text-xs"></i>
                                            </button>
                                        @endif
                                    </form>

                                    <!-- SUPPRIMER -->
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                            onclick="confirmAction(this.closest('form'), {
                                                title: 'Supprimer ce produit ?',
                                                text: 'Êtes-vous sûr de vouloir supprimer définitivement {{ addslashes($product->name) }} ? Cette action est irréversible.',
                                                confirmText: 'Oui, supprimer',
                                                confirmColor: '#e11d48',
                                                icon: 'warning'
                                            })"
                                            class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition cursor-pointer" 
                                            title="Supprimer définitivement">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Aucun produit trouvé dans le catalogue</p>
                                <p class="text-[11px] text-slate-400 mt-1">Modifiez vos filtres de recherche ou enregistrez un nouvel article.</p>
                                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-1.5 mt-3 px-3.5 py-1.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold transition">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Ajouter un produit</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
