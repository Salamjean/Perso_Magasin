@extends('layouts.app')

@section('title', 'Gestion des Catégories')
@section('page_title', 'Rayons & Catégories')

@section('content')
<div class="space-y-5">

    <!-- EN-TÊTE PRINCIPALE AVEC RECHERCHE INSTANTANÉE AU MILIEU -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="shrink-0">
            <h3 class="text-base font-black text-slate-900 tracking-tight">Rayons & Catégories</h3>
            <p class="text-xs text-slate-500">Organisez vos rayons pour faciliter le scan, la recherche et l'encaissement</p>
        </div>

        <!-- CHAMP DE RECHERCHE INSTANTANÉE AU MILIEU -->
        <div class="flex-1 max-w-md w-full">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="search-category-input" placeholder="Rechercher une catégorie en direct..."
                       oninput="filterCategories(this.value)"
                       class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                <button type="button" id="clear-search-btn" onclick="clearSearch()" 
                        class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none" title="Effacer la recherche">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- BOUTON AJOUTER NOUVELLE CATÉGORIE -->
        <a href="{{ route('admin.categories.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer shrink-0 self-start md:self-auto">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Nouvelle Catégorie</span>
        </a>
    </div>

    <!-- GRILLE COMPACTE EN 4 COLONNES -->
    <div id="categories-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
        @forelse($categories as $category)
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-[#0056a6]/40 hover:shadow-sm transition flex flex-col justify-between group"
                 data-category-card
                 data-name="{{ mb_strtolower($category->name) }}"
                 data-desc="{{ mb_strtolower($category->description ?? '') }}"
                 data-slug="{{ mb_strtolower($category->slug) }}">
                
                <div>
                    <!-- EN-TÊTE DE LA CARTE : COULEUR + NOM + COMPTEUR -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $category->color ?? '#0056a6' }}"></span>
                            <a href="{{ route('admin.categories.show', $category) }}" 
                               class="text-xs font-bold text-slate-900 truncate hover:text-[#0056a6] transition" 
                               title="Ouvrir le rayon {{ $category->name }}">
                                {{ $category->name }}
                            </a>
                        </div>
                        
                        <a href="{{ route('admin.categories.show', $category) }}" 
                           class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 hover:bg-blue-100 text-[#0056a6] border border-blue-100 shrink-0 whitespace-nowrap transition"
                           title="Voir les {{ $category->products_count }} produit(s)">
                            {{ $category->products_count }} réf.
                        </a>
                    </div>

                    <!-- DESCRIPTION COMPACTE -->
                    <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed min-h-[32px]">
                        {{ $category->description ?? 'Aucune description spécifiée pour ce rayon.' }}
                    </p>
                </div>

                <!-- BAS DE CARTE : SLUG + BOUTONS D'ACTION -->
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="font-mono text-slate-400 text-[10px] truncate max-w-[90px]" title="{{ $category->slug }}">
                        #{{ $category->slug }}
                    </span>

                    <div class="flex items-center gap-1.5">
                        <!-- OUVRIR LE RAYON ET VOIR LES PRODUITS -->
                        <a href="{{ route('admin.categories.show', $category) }}" 
                           class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0056a6] flex items-center justify-center transition" 
                           title="Ouvrir le rayon et voir les produits">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>

                        <!-- MODIFIER -->
                        <a href="{{ route('admin.categories.edit', $category) }}" 
                           class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                           title="Modifier la catégorie">
                            <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                        </a>

                        <!-- SUPPRIMER -->
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                onclick="confirmAction(this.closest('form'), {
                                    title: 'Supprimer cette catégorie ?',
                                    text: 'Tous les produits associés au rayon {{ addslashes($category->name) }} perdront leur assignation.',
                                    confirmText: 'Oui, supprimer',
                                    confirmColor: '#e11d48',
                                    icon: 'warning'
                                })"
                                class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition cursor-pointer" 
                                title="Supprimer la catégorie">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-10 text-center text-slate-400 border border-slate-200/80">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <p class="text-xs font-semibold text-slate-700">Aucune catégorie pour l'instant</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Créez votre premier rayon pour organiser votre catalogue de produits.</p>
            </div>
        @endforelse

        <!-- MESSAGE AUCUN RÉSULTAT DE RECHERCHE -->
        <div id="no-search-results" class="hidden col-span-full bg-white rounded-2xl p-8 text-center text-slate-400 border border-slate-200/80">
            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-base">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <p class="text-xs font-semibold text-slate-700">Aucun rayon correspondant</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Aucune catégorie ne correspond à votre recherche.</p>
            <button type="button" onclick="clearSearch()" class="mt-3 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                Réinitialiser la recherche
            </button>
        </div>
    </div>

    <!-- PAGINATION -->
    @if($categories->hasPages())
        <div id="pagination-wrapper" class="p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $categories->links() }}
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    function filterCategories(query) {
        const q = query.trim().toLowerCase();
        const cards = document.querySelectorAll('[data-category-card]');
        const clearBtn = document.getElementById('clear-search-btn');
        const noResults = document.getElementById('no-search-results');
        const paginationWrapper = document.getElementById('pagination-wrapper');
        let visibleCount = 0;

        if (q.length > 0) {
            clearBtn.classList.remove('hidden');
            if (paginationWrapper) paginationWrapper.classList.add('hidden');
        } else {
            clearBtn.classList.add('hidden');
            if (paginationWrapper) paginationWrapper.classList.remove('hidden');
        }

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const desc = card.getAttribute('data-desc') || '';
            const slug = card.getAttribute('data-slug') || '';

            if (name.includes(q) || desc.includes(q) || slug.includes(q)) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        if (noResults) {
            if (visibleCount === 0 && cards.length > 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }
    }

    function clearSearch() {
        const input = document.getElementById('search-category-input');
        input.value = '';
        filterCategories('');
        input.focus();
    }
</script>
@endsection
