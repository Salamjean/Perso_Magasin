@extends('layouts.app')

@section('title', 'Lancer un Inventaire')
@section('page_title', 'Session de Comptage d\'Inventaire')

@section('content')
<div class="space-y-6">

    <!-- EN-TÊTE PRINCIPAL & BOUTON RETOUR -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">
                        <a href="{{ route('magasinier.inventory.index') }}" class="hover:text-[#0056a6] transition">Inventaires</a> /
                    </span>
                    <span class="text-xs font-bold text-slate-600">Session de comptage</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Nouvelle Session d'Inventaire</h2>
                <p class="text-xs text-slate-500 mt-0.5">Contrôle physique en rayon et calcul automatique des écarts</p>
            </div>
        </div>

        <a href="{{ route('magasinier.inventory.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition self-start sm:self-auto">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Historique des inventaires</span>
        </a>
    </div>

    <!-- SÉLECTION DU PÉRIMÈTRE D'INVENTAIRE (CARTE DE CONFIGURATION) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-amber-500"></i>
                    <span>Périmètre de l'inventaire</span>
                </h3>
                <p class="text-[11px] text-slate-400">Choisissez de compter l'ensemble du catalogue ou de cibler un rayon spécifique</p>
            </div>

            <!-- SELECTEUR DE MODE / FORMULAIRE GET -->
            <form method="GET" action="{{ route('magasinier.inventory.create') }}" id="filter_scope_form" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="type" id="filter_type_input" value="{{ $type }}">
                
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80">
                    <button type="button" onclick="setInventoryType('general')" 
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer {{ $type === 'general' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fa-solid fa-boxes-stacked mr-1.5 text-xs {{ $type === 'general' ? 'text-amber-600' : 'text-slate-400' }}"></i>
                        Général (Tout le stock)
                    </button>
                    <button type="button" onclick="setInventoryType('category')" 
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer {{ $type === 'category' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fa-solid fa-tags mr-1.5 text-xs {{ $type === 'category' ? 'text-amber-600' : 'text-slate-400' }}"></i>
                        Par Rayon / Catégorie
                    </button>
                </div>

                @if($type === 'category')
                    <div class="relative min-w-[220px]">
                        <select name="category_id" id="filter_category_select" onchange="document.getElementById('filter_scope_form').submit()"
                            class="w-full px-3.5 py-2 bg-amber-50/50 border border-amber-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:border-amber-500 focus:outline-none transition cursor-pointer">
                            <option value="">Tous les rayons / catégories</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>
                                    📂 {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </form>
        </div>

        <!-- 4 KPIS EN DIRECT -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 pt-4">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Total Articles</span>
                    <p id="kpi_total" class="text-lg font-black text-slate-800 font-mono">{{ count($products) }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 text-xs">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/70 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-700 tracking-wider">Lignes Conformes</span>
                    <p id="kpi_conformes" class="text-lg font-black text-emerald-700 font-mono">{{ count($products) }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-check"></i>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-200/70 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-rose-700 tracking-wider">Manquants / Pertes</span>
                    <p id="kpi_manquants" class="text-lg font-black text-rose-700 font-mono">0</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200/70 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase text-blue-700 tracking-wider">Surplus / Excédents</span>
                    <p id="kpi_surplus" class="text-lg font-black text-blue-700 font-mono">0</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULAIRE PRINCIPAL D'ENREGISTREMENT -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- BARRE D'OUTILS ET RECHERCHE RAPIDE -->
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="table_search_input" oninput="filterInventoryTable(this.value)"
                    placeholder="Filtrer par nom, référence ou code-barres..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:border-amber-500 focus:outline-none transition">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="button" onclick="setAllConforme()" 
                    class="px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-check-double text-emerald-600"></i>
                    <span>Tout pointer conforme</span>
                </button>
                <button type="button" onclick="resetAllToZero()" 
                    class="px-3 py-2 bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 text-slate-600 text-xs font-semibold rounded-xl transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>Remettre à zéro</span>
                </button>
            </div>
        </div>

        <form action="{{ route('magasinier.inventory.store') }}" method="POST" id="inventory_form" class="space-y-6">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            @if($categoryId) 
                <input type="hidden" name="category_id" value="{{ $categoryId }}"> 
            @endif

            <!-- TABLEAU DES PRODUITS -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs" id="inventory_table">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4 sm:px-6">Produit & Référence</th>
                            <th class="py-3.5 px-4 text-center">Stock Théorique</th>
                            <th class="py-3.5 px-4 text-center w-56">Stock Physique Compté <span class="text-rose-500">*</span></th>
                            <th class="py-3.5 px-4 text-center">Écart Constaté</th>
                            <th class="py-3.5 px-4 sm:px-6">Justification / Motif d'écart</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $idx => $prod)
                            <tr class="hover:bg-slate-50/80 transition inventory-row" 
                                data-search="{{ strtolower($prod->name . ' ' . $prod->reference . ' ' . ($prod->barcode ?? '')) }}">
                                
                                <!-- COLONNE ARTICLE -->
                                <td class="py-3.5 px-4 sm:px-6">
                                    <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $prod->id }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0">
                                            <i class="fa-solid fa-box"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block leading-tight">{{ $prod->name }}</span>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-slate-400 font-mono font-semibold">Réf: {{ $prod->reference }}</span>
                                                @if($prod->category)
                                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium">{{ $prod->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- COLONNE STOCK THÉORIQUE -->
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700 system-stock" data-val="{{ (float)$prod->stock_quantity }}" data-unit="{{ $prod->unit ?? 'u' }}">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200/80 text-xs text-slate-800">
                                        {{ (float)$prod->stock_quantity }} <span class="text-[10px] text-slate-500 font-normal">{{ $prod->unit ?? 'u' }}</span>
                                    </span>
                                </td>

                                <!-- COLONNE STOCK PHYSIQUE (AVEC STEPPERS) -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1 max-w-[180px] mx-auto">
                                        <button type="button" onclick="stepQuantity(this, -1)"
                                            class="w-7 h-7 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs transition cursor-pointer">
                                            -
                                        </button>
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $idx }}][physical_stock]" 
                                            value="{{ (float)$prod->stock_quantity }}" 
                                            required
                                            class="w-20 px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold text-slate-900 text-center focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition physical-input"
                                            oninput="calculateDiff(this)">
                                        <button type="button" onclick="stepQuantity(this, 1)"
                                            class="w-7 h-7 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs transition cursor-pointer">
                                            +
                                        </button>
                                    </div>
                                </td>

                                <!-- COLONNE ÉCART CALCULÉ -->
                                <td class="py-3.5 px-4 text-center diff-cell">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="fa-solid fa-check text-[10px]"></i>
                                        <span>0 (Conforme)</span>
                                    </span>
                                </td>

                                <!-- COLONNE MOTIF / JUSTIFICATION -->
                                <td class="py-3.5 px-4 sm:px-6">
                                    <input type="text" name="items[{{ $idx }}][reason]" 
                                        placeholder="Ex: Casse, vol, erreur réception..."
                                        class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 placeholder-slate-400 focus:bg-white focus:border-amber-500 focus:outline-none transition">
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-medium text-xs">Aucun produit trouvé dans ce périmètre d'inventaire.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- SECTION OBSERVATIONS GLOBALES & CLÔTURE -->
            <div class="p-5 sm:p-6 bg-slate-50/60 border-t border-slate-100 space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Notes & Synthèse de la session d'inventaire <span class="text-[10px] text-slate-400 font-normal">(Optionnel)</span>
                    </label>
                    <textarea name="notes" rows="2" placeholder="Observations globales, état général du magasin ou des rayons inventoriés..."
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-[11px] text-slate-500 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                        <span>La validation enregistrera automatiquement les mouvements d'ajustement pour chaque ligne avec écart.</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <a href="{{ route('magasinier.inventory.index') }}" 
                           class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-bold transition">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-clipboard-check text-xs"></i>
                            <span>Clôturer l'inventaire & Ajuster le stock</span>
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function setInventoryType(type) {
        document.getElementById('filter_type_input').value = type;
        if (type === 'general') {
            const catSelect = document.getElementById('filter_category_select');
            if (catSelect) {
                catSelect.value = '';
            }
        }
        document.getElementById('filter_scope_form').submit();
    }

    function stepQuantity(button, delta) {
        const input = button.parentElement.querySelector('.physical-input');
        if (input) {
            let currentVal = parseFloat(input.value) || 0;
            currentVal = Math.max(0, currentVal + delta);
            input.value = Math.round(currentVal * 100) / 100;
            calculateDiff(input);
        }
    }

    function calculateDiff(input) {
        const row = input.closest('.inventory-row');
        const systemEl = row.querySelector('.system-stock');
        const systemStock = parseFloat(systemEl.getAttribute('data-val')) || 0;
        const unit = systemEl.getAttribute('data-unit') || 'u';
        const physicalStock = parseFloat(input.value) || 0;
        const diff = Math.round((physicalStock - systemStock) * 100) / 100;

        const cell = row.querySelector('.diff-cell');
        if (diff === 0) {
            cell.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    <i class="fa-solid fa-check text-[10px]"></i>
                    <span>0 (Conforme)</span>
                </span>`;
        } else if (diff < 0) {
            cell.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    <i class="fa-solid fa-arrow-down text-[10px]"></i>
                    <span>${diff} ${unit} (Manque)</span>
                </span>`;
        } else {
            cell.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fa-solid fa-arrow-up text-[10px]"></i>
                    <span>+${diff} ${unit} (Surplus)</span>
                </span>`;
        }

        updateSummaryKpis();
    }

    function updateSummaryKpis() {
        const rows = document.querySelectorAll('.inventory-row');
        let total = rows.length;
        let conformes = 0;
        let manquants = 0;
        let surplus = 0;

        rows.forEach(row => {
            const systemStock = parseFloat(row.querySelector('.system-stock')?.getAttribute('data-val')) || 0;
            const input = row.querySelector('.physical-input');
            const physicalStock = input ? (parseFloat(input.value) || 0) : 0;
            const diff = Math.round((physicalStock - systemStock) * 100) / 100;

            if (diff === 0) {
                conformes++;
            } else if (diff < 0) {
                manquants++;
            } else {
                surplus++;
            }
        });

        const kpiTotal = document.getElementById('kpi_total');
        const kpiConformes = document.getElementById('kpi_conformes');
        const kpiManquants = document.getElementById('kpi_manquants');
        const kpiSurplus = document.getElementById('kpi_surplus');

        if (kpiTotal) kpiTotal.textContent = total;
        if (kpiConformes) kpiConformes.textContent = conformes;
        if (kpiManquants) kpiManquants.textContent = manquants;
        if (kpiSurplus) kpiSurplus.textContent = surplus;
    }

    function setAllConforme() {
        document.querySelectorAll('.inventory-row').forEach(row => {
            const systemStock = row.querySelector('.system-stock')?.getAttribute('data-val') || 0;
            const input = row.querySelector('.physical-input');
            if (input) {
                input.value = systemStock;
                calculateDiff(input);
            }
        });
    }

    function resetAllToZero() {
        document.querySelectorAll('.inventory-row').forEach(row => {
            const input = row.querySelector('.physical-input');
            if (input) {
                input.value = 0;
                calculateDiff(input);
            }
        });
    }

    function filterInventoryTable(term) {
        const cleanTerm = term.toLowerCase().trim();
        document.querySelectorAll('.inventory-row').forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            if (!cleanTerm || searchData.includes(cleanTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateSummaryKpis();
    });
</script>
@endsection
