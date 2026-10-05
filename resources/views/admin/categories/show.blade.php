@extends('layouts.app')

@section('title', 'Rayon : ' . $category->name)
@section('page_title', 'Détails du Rayon')

@section('content')
<div class="space-y-5">

    <!-- EN-TÊTE PRINCIPALE AVEC RECHERCHE INSTANTANÉE AU MILIEU -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        
        <!-- INFORMATIONS DU RAYON -->
        <div class="flex items-center gap-3.5 shrink-0">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-lg shadow-xs shrink-0" 
                 style="background-color: {{ $category->color ?? '#0056a6' }}">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5 mb-0.5">
                    <span class="text-[11px] font-medium text-slate-400">
                        <a href="{{ route('admin.categories.index') }}" class="hover:text-[#0056a6] transition">Rayons & Catégories</a> /
                    </span>
                    <span class="font-mono text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">#{{ $category->slug }}</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">{{ $category->name }}</h2>
                <p class="text-[11px] text-slate-500 truncate max-w-xs">{{ $category->description ?? 'Aucune description spécifique' }}</p>
            </div>
        </div>

        <!-- CHAMP DE RECHERCHE PRODUITS AU MILIEU -->
        <div class="flex-1 max-w-md w-full">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="search-product-input" placeholder="Rechercher un produit (nom, référence, code-barres)..."
                       oninput="filterProducts(this.value)"
                       class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                <button type="button" id="clear-search-btn" onclick="clearProductSearch()" 
                        class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none" title="Effacer la recherche">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- BOUTON RETOUR AUX RAYONS -->
        <a href="{{ route('admin.categories.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition shrink-0 self-start md:self-auto">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux rayons</span>
        </a>
    </div>

    <!-- LISTE DES PRODUITS DU RAYON -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- BANDEAU HAUT DU TABLEAU -->
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-list-check text-slate-400 text-xs"></i>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Produits dans ce rayon</h4>
            </div>
            <div class="text-xs text-slate-500 font-medium">
                <span id="visible-products-count">{{ $products->count() }}</span> produit(s) affiché(s)
            </div>
        </div>

        <!-- TABLEAU DES PRODUITS -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Produit & Réf</th>
                        <th class="py-3.5 px-5 text-center">Code-barres</th>
                        <th class="py-3.5 px-5 text-center">Prix Vente</th>
                        <th class="py-3.5 px-5 text-center">Stock disponible</th>
                        <th class="py-3.5 px-5 text-center">Fournisseur</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="divide-y divide-slate-100 text-xs">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60 transition"
                            data-product-row
                            data-name="{{ mb_strtolower($product->name) }}"
                            data-ref="{{ mb_strtolower($product->reference) }}"
                            data-barcode="{{ mb_strtolower($product->barcode ?? '') }}"
                            data-supplier="{{ mb_strtolower($product->supplier->name ?? '') }}">
                            
                            <!-- NOM & RÉFÉRENCE -->
                            <td class="py-3.5 px-5 align-middle">
                                <div class="font-bold text-slate-900">{{ $product->name }}</div>
                                <div class="font-mono text-[11px] text-slate-400">Réf: {{ $product->reference }}</div>
                            </td>

                            <!-- CODE-BARRES -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($product->barcode)
                                    <span class="font-mono text-[11px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                        {{ $product->barcode }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Non renseigné</span>
                                @endif
                            </td>

                            <!-- PRIX DE VENTE -->
                            <td class="py-3.5 px-5 text-center align-middle font-bold text-slate-900 font-mono">
                                {{ number_format($product->sell_price, 0, ',', ' ') }} FCFA
                            </td>

                            <!-- STOCK & BADGE ÉTAT -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($product->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Rupture (0 {{ $product->unit }})
                                    </span>
                                @elseif($product->stock_quantity <= $product->alert_threshold)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Faible ({{ (float)$product->stock_quantity }} {{ $product->unit }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ (float)$product->stock_quantity }} {{ $product->unit }}
                                    </span>
                                @endif
                            </td>

                            <!-- FOURNISSEUR -->
                            <td class="py-3.5 px-5 text-center align-middle text-slate-600">
                                {{ $product->supplier->name ?? 'Aucun' }}
                            </td>

                            <!-- ACTIONS -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                                       title="Fiche produit">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                                       title="Modifier le produit">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-base">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Aucun produit dans ce rayon</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Ajoutez un produit et assignez-lui cette catégorie.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- MESSAGE SI AUCUN RÉSULTAT LORS DE LA RECHERCHE EN DIRECT -->
            <div id="no-filter-results" class="hidden text-center py-10 text-slate-400">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-base">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <p class="text-xs font-semibold text-slate-700">Aucun produit ne correspond à votre recherche</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Essayez un autre mot-clé ou effacez le filtre.</p>
            </div>
        </div>

        <!-- PAGINATION PRODUITS -->
        @if($products->hasPages())
            <div id="category-products-pagination" class="p-3.5 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif

    </div>

</div>

<script>
    /**
     * Filtrage instantané des lignes de produits en direct
     */
    function filterProducts(query) {
        const q = (query || '').trim().toLowerCase();
        const clearBtn = document.getElementById('clear-search-btn');
        const rows = document.querySelectorAll('[data-product-row]');
        const noResults = document.getElementById('no-filter-results');
        const countDisplay = document.getElementById('visible-products-count');
        const pagination = document.getElementById('category-products-pagination');

        if (clearBtn) {
            if (q.length > 0) {
                clearBtn.classList.remove('hidden');
                clearBtn.style.display = 'flex';
            } else {
                clearBtn.classList.add('hidden');
                clearBtn.style.display = 'none';
            }
        }

        if (pagination) {
            pagination.style.display = q.length > 0 ? 'none' : '';
        }

        let visibleCount = 0;

        rows.forEach(row => {
            const name = (row.getAttribute('data-name') || '').toLowerCase();
            const ref = (row.getAttribute('data-ref') || '').toLowerCase();
            const barcode = (row.getAttribute('data-barcode') || '').toLowerCase();
            const supplier = (row.getAttribute('data-supplier') || '').toLowerCase();

            const matches = !q || name.includes(q) || ref.includes(q) || barcode.includes(q) || supplier.includes(q);

            if (matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countDisplay) {
            countDisplay.textContent = visibleCount;
        }

        if (noResults) {
            if (rows.length > 0 && visibleCount === 0) {
                noResults.classList.remove('hidden');
                noResults.style.display = 'block';
            } else {
                noResults.classList.add('hidden');
                noResults.style.display = 'none';
            }
        }
    }

    /**
     * Effacer la recherche en direct
     */
    function clearProductSearch() {
        const input = document.getElementById('search-product-input');
        if (input) {
            input.value = '';
            filterProducts('');
            input.focus();
        }
    }

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-product-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterProducts(this.value);
            });
            searchInput.addEventListener('keyup', function() {
                filterProducts(this.value);
            });
            if (searchInput.value) {
                filterProducts(searchInput.value);
            }
        }
    });
</script>
@endsection
