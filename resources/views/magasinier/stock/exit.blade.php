@extends('layouts.app')

@section('title', 'Sortie de Stock')
@section('page_title', 'Enregistrer une Sortie de Stock / Casse')

@section('content')
<div class="w-full lg:w-[85%] xl:w-[80%] mx-auto space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-arrow-up"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">
                        <a href="{{ route('magasinier.stock.index') }}" class="hover:text-[#0056a6] transition">Stocks</a> /
                    </span>
                    <span class="text-xs font-bold text-slate-600">Sortie physique</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Déclaration de Perte, Casse ou Péremption</h2>
                <p class="text-xs text-slate-500 mt-0.5">Déduction immédiate du stock physique avec traçabilité et justification</p>
            </div>
        </div>

        <a href="{{ route('magasinier.stock.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux stocks</span>
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

    <form action="{{ route('magasinier.stock.exit.process') }}" method="POST" class="space-y-6">
        @csrf

        <!-- GRILLE EN 2 COLONNES -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- COLONNE GAUCHE : CHOIX DU PRODUIT & APERÇU STOCK -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5 flex flex-col justify-between">
                
                <div class="space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Sélection de l'Article</h3>
                            <p class="text-[11px] text-slate-400">Produit à déduire de l'inventaire</p>
                        </div>
                    </div>

                    <!-- SÉLECTEUR DE PRODUIT (UNIQUEMENT AVEC STOCK > 0) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Choisir le produit en stock <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <select name="product_id" id="product_select" required onchange="updateProductPreview(this)"
                                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-rose-600 focus:ring-2 focus:ring-rose-600/20 focus:outline-none transition cursor-pointer">
                                <option value="">Sélectionnez un produit à déstocker</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" 
                                        data-name="{{ $p->name }}" 
                                        data-ref="{{ $p->reference }}" 
                                        data-barcode="{{ $p->barcode ?? 'N/A' }}"
                                        data-stock="{{ (float)$p->stock_quantity }}"
                                        data-unit="{{ $p->unit ?? 'unités' }}"
                                        data-category="{{ $p->category->name ?? 'Sans rayon' }}"
                                        {{ (old('product_id', request('product_id')) == $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }} (Disponible: {{ (float)$p->stock_quantity }} {{ $p->unit ?? 'u' }}) - Réf: {{ $p->reference }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- CARTE DE PREVIEW DU PRODUIT SÉLECTIONNÉ -->
                    <div id="product_preview_card" class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <span class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">État actuel du produit</span>
                            <span id="prev_badge_category" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700">Général</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Nom :</span>
                            <strong id="prev_name" class="text-slate-800 font-bold">—</strong>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Référence interne :</span>
                            <span id="prev_ref" class="font-mono text-slate-700 font-semibold">—</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Code-barres :</span>
                            <span id="prev_barcode" class="font-mono text-slate-700 font-semibold">—</span>
                        </div>
                        <div class="pt-2.5 border-t border-slate-200 grid grid-cols-2 gap-3">
                            <div class="bg-white p-2.5 rounded-lg border border-slate-200/80 text-center">
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Stock Actuel</span>
                                <span id="prev_stock" class="text-base font-black text-slate-800 font-mono">0</span>
                            </div>
                            <div id="box_remaining_stock" class="bg-rose-50/80 p-2.5 rounded-lg border border-rose-200/80 text-center transition">
                                <span id="label_remaining" class="text-[10px] font-bold text-rose-700 block uppercase">Stock Restant Projeté</span>
                                <span id="prev_remaining_stock" class="text-base font-black text-rose-700 font-mono">0</span>
                            </div>
                        </div>

                        <!-- ALERTE QUANTITÉ DÉPASSÉE -->
                        <div id="stock_warning_alert" class="hidden p-2.5 rounded-lg bg-red-100 border border-red-300 text-red-800 text-[11px] font-bold flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                            <span>Attention : la quantité à déduire dépasse le stock disponible physique !</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-rose-500"></i>
                    <span>Toute sortie de marchandise est journalisée et rattachée à votre compte magasinier.</span>
                </div>

            </div>

            <!-- COLONNE DROITE : QUANTITÉ & MOTIF DE SORTIE -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5 flex flex-col justify-between">
                
                <div class="space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Motif & Quantité Déduite</h3>
                            <p class="text-[11px] text-slate-400">Raison de la sortie d'inventaire</p>
                        </div>
                    </div>

                    <!-- QUANTITÉ À RETIRER -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Quantité à déduire <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-rose-600 text-sm font-black">
                                -
                            </span>
                            <input type="number" step="0.01" min="0.01" name="quantity" id="input_quantity" value="{{ old('quantity') }}" required 
                                placeholder="Ex: 3" oninput="calculateProjectedStock()"
                                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:bg-white focus:border-rose-600 focus:ring-2 focus:ring-rose-600/20 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- MOTIF DE SORTIE PRÉDÉFINI -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Motif de la sortie <span class="text-rose-500">*</span>
                        </label>
                        <select name="reason" required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-rose-600 focus:ring-2 focus:ring-rose-600/20 focus:outline-none transition cursor-pointer">
                            <option value="">Sélectionnez une raison</option>
                            <option value="produit_endommage" {{ old('reason') == 'produit_endommage' ? 'selected' : '' }}>📦 Produit abîmé / endommagé</option>
                            <option value="produit_perime" {{ old('reason') == 'produit_perime' ? 'selected' : '' }}>⌛ Produit périmé / date dépassée</option>
                            <option value="casse" {{ old('reason', 'casse') == 'casse' ? 'selected' : '' }}>💥 Casse en rayon ou manipulation</option>
                            <option value="perte" {{ old('reason') == 'perte' ? 'selected' : '' }}>🔍 Perte / Vol constaté</option>
                            <option value="retour_fournisseur" {{ old('reason') == 'retour_fournisseur' ? 'selected' : '' }}>↩️ Retour au fournisseur (défectueux)</option>
                            <option value="autre" {{ old('reason') == 'autre' ? 'selected' : '' }}>📝 Autre sortie de stock</option>
                        </select>
                    </div>

                    <!-- NOTES / OBSERVATIONS -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Circonstances & Commentaires
                        </label>
                        <textarea name="notes" rows="3" placeholder="Précisez le lieu de la casse, les circonstances ou la cause..."
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-rose-600 focus:outline-none transition leading-relaxed">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- BOUTONS D'ACTION -->
                <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('magasinier.stock.index') }}" 
                       class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition">
                        Annuler
                    </a>
                    <button type="submit" id="btn_submit"
                            class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-minus text-xs"></i>
                        <span>Valider la Sortie de Stock</span>
                    </button>
                </div>

            </div>

        </div>

    </form>

</div>
@endsection

@section('scripts')
<script>
    let currentStockValue = 0;
    let currentStockUnit = 'u';

    function updateProductPreview(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            currentStockValue = parseFloat(opt.getAttribute('data-stock')) || 0;
            currentStockUnit = opt.getAttribute('data-unit') || 'u';

            document.getElementById('prev_name').textContent = opt.getAttribute('data-name') || '—';
            document.getElementById('prev_ref').textContent = opt.getAttribute('data-ref') || '—';
            document.getElementById('prev_barcode').textContent = opt.getAttribute('data-barcode') || '—';
            document.getElementById('prev_badge_category').textContent = opt.getAttribute('data-category') || 'Général';
            document.getElementById('prev_stock').textContent = currentStockValue + ' ' + currentStockUnit;
        } else {
            currentStockValue = 0;
            currentStockUnit = 'u';
            document.getElementById('prev_name').textContent = '—';
            document.getElementById('prev_ref').textContent = '—';
            document.getElementById('prev_barcode').textContent = '—';
            document.getElementById('prev_badge_category').textContent = 'Général';
            document.getElementById('prev_stock').textContent = '0';
        }
        calculateProjectedStock();
    }

    function calculateProjectedStock() {
        const qtyInput = parseFloat(document.getElementById('input_quantity').value) || 0;
        const remaining = currentStockValue - qtyInput;
        const remainingFormatted = (Math.round(remaining * 100) / 100);
        
        const prevRemainingEl = document.getElementById('prev_remaining_stock');
        const warningEl = document.getElementById('stock_warning_alert');
        const boxRemaining = document.getElementById('box_remaining_stock');
        const submitBtn = document.getElementById('btn_submit');

        prevRemainingEl.textContent = remainingFormatted + ' ' + currentStockUnit;

        if (qtyInput > currentStockValue && currentStockValue > 0) {
            warningEl.classList.remove('hidden');
            boxRemaining.className = 'bg-red-100 p-2.5 rounded-lg border border-red-300 text-center transition';
            prevRemainingEl.className = 'text-base font-black text-red-700 font-mono';
        } else {
            warningEl.classList.add('hidden');
            boxRemaining.className = 'bg-rose-50/80 p-2.5 rounded-lg border border-rose-200/80 text-center transition';
            prevRemainingEl.className = 'text-base font-black text-rose-700 font-mono';
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
