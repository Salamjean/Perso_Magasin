@extends('layouts.app')

@section('title', 'Entrée de Stock')
@section('page_title', 'Enregistrer une Entrée en Stock')

@section('content')
<div class="w-full lg:w-[80%] mx-auto space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-arrow-down"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">
                        <a href="{{ route('admin.stock.index') }}" class="hover:text-[#0056a6] transition">Stocks</a> /
                    </span>
                    <span class="text-xs font-bold text-slate-600">Entrée manuelle</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Réception & Entrée en Stock</h2>
            </div>
        </div>

        <a href="{{ route('admin.stock.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux mouvements</span>
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1 shadow-xs">
            <div class="font-bold flex items-center gap-2 mb-1">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Veuillez corriger les erreurs suivantes :</span>
            </div>
            @foreach($errors->all() as $err)
                <p class="pl-5">• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.stock.entry.process') }}" method="POST" class="space-y-6">
        @csrf

        <!-- GRILLE EN 2 COLONNES -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- COLONNE GAUCHE : CHOIX DU PRODUIT & APERÇU STOCK -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Sélection de l'Article</h3>
                        <p class="text-[11px] text-slate-400">Produit à réapprovisionner</p>
                    </div>
                </div>

                <!-- SÉLECTEUR DE PRODUIT -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Choisir un produit dans le catalogue <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <select name="product_id" id="product_select" required onchange="updateProductPreview(this)"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition cursor-pointer">
                            <option value="">Sélectionnez un produit à approvisionner</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" 
                                    data-name="{{ $p->name }}" 
                                    data-ref="{{ $p->reference }}" 
                                    data-barcode="{{ $p->barcode ?? 'N/A' }}"
                                    data-stock="{{ (float)$p->stock_quantity }}"
                                    data-unit="{{ $p->unit ?? 'unités' }}"
                                    data-category="{{ $p->category->name ?? 'Sans rayon' }}"
                                    {{ (old('product_id', request('product_id')) == $p->id) ? 'selected' : '' }}>
                                    {{ $p->name }} (Stock actuel: {{ (float)$p->stock_quantity }} {{ $p->unit ?? 'u' }}) - Réf: {{ $p->reference }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- CARTE DE PREVIEW DU PRODUIT SÉLECTIONNÉ -->
                <div id="product_preview_card" class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block">État actuel de l'article</span>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Nom :</span>
                        <strong id="prev_name" class="text-slate-800 font-bold">—</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Rayon :</span>
                        <span id="prev_category" class="font-semibold text-slate-700">—</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Code-barres :</span>
                        <span id="prev_barcode" class="font-mono text-slate-700 font-semibold">—</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">Stock disponible :</span>
                        <span id="prev_stock" class="text-base font-black text-emerald-700 font-mono">0</span>
                    </div>
                </div>

            </div>

            <!-- COLONNE DROITE : QUANTITÉ, MOTIF & RÉFÉRENCE -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5 flex flex-col justify-between">
                
                <div class="space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Détails du Mouvement</h3>
                            <p class="text-[11px] text-slate-400">Quantité ajoutée et justification</p>
                        </div>
                    </div>

                    <!-- QUANTITÉ À AJOUTER -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Quantité à faire entrer <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600 text-xs font-bold">
                                +
                            </span>
                            <input type="number" step="0.01" min="0.01" name="quantity" value="{{ old('quantity') }}" required 
                                placeholder="Ex: 50"
                                class="w-full pl-8 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:bg-white focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- MOTIF DE L'ENTRÉE -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Motif de l'entrée en stock <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="reason" value="{{ old('reason', 'Réception commande fournisseur') }}" required 
                            placeholder="Ex: Réception livraison fournisseur, ajustement de stock..."
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                    </div>

                    <!-- RÉFÉRENCE DOCUMENT (OPTIONNEL) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            N° Bon de Livraison / Facture <span class="text-[10px] text-slate-400 font-normal">(Optionnel)</span>
                        </label>
                        <input type="text" name="reference" value="{{ old('reference') }}" 
                            placeholder="Ex: BL-2026-0894"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                    </div>

                    <!-- NOTES COMPLÉMENTAIRES -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Remarques ou observations
                        </label>
                        <textarea name="notes" rows="2" placeholder="Observations éventuelles..."
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- BOUTONS D'ACTION -->
                <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.stock.index') }}" 
                       class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Valider l'Entrée de Stock</span>
                    </button>
                </div>

            </div>

        </div>

    </form>

</div>
@endsection

@section('scripts')
<script>
    function updateProductPreview(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('prev_name').textContent = opt.getAttribute('data-name') || '—';
            document.getElementById('prev_category').textContent = opt.getAttribute('data-category') || '—';
            document.getElementById('prev_barcode').textContent = opt.getAttribute('data-barcode') || '—';
            document.getElementById('prev_stock').textContent = (opt.getAttribute('data-stock') || '0') + ' ' + (opt.getAttribute('data-unit') || 'u');
        } else {
            document.getElementById('prev_name').textContent = '—';
            document.getElementById('prev_category').textContent = '—';
            document.getElementById('prev_barcode').textContent = '—';
            document.getElementById('prev_stock').textContent = '0';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('product_select');
        if (sel && sel.value) {
            updateProductPreview(sel);
        }
    });
</script>
@endsection
