@extends('layouts.app')

@section('title', 'Sortie de Stock')
@section('page_title', 'Enregistrer une Sortie de Stock / Perte')

@section('content')
<div class="w-full lg:w-[80%] mx-auto space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-arrow-up"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">
                        <a href="{{ route('admin.stock.index') }}" class="hover:text-[#0056a6] transition">Stocks</a> /
                    </span>
                    <span class="text-xs font-bold text-slate-600">Sortie manuelle</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Déclaration de Sortie ou Perte</h2>
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

    <form action="{{ route('admin.stock.exit.process') }}" method="POST" class="space-y-6">
        @csrf

        <!-- GRILLE EN 2 COLONNES -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- COLONNE GAUCHE : CHOIX DU PRODUIT & APERÇU STOCK -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
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
                        Choisir le produit à déstocker <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <select name="product_id" id="product_select" required onchange="updateProductPreview(this)"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition cursor-pointer">
                            <option value="">Sélectionnez un produit en stock</option>
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

                <!-- CARTE DE PREVIEW DU PRODUIT -->
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
                        <span id="prev_stock" class="text-base font-black text-rose-700 font-mono">0</span>
                    </div>
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
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-rose-600 text-xs font-bold">
                                -
                            </span>
                            <input type="number" step="0.01" min="0.01" name="quantity" value="{{ old('quantity') }}" required 
                                placeholder="Ex: 5"
                                class="w-full pl-8 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:bg-white focus:border-rose-600 focus:ring-2 focus:ring-rose-600/20 focus:outline-none transition">
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
                            <option value="casse" {{ old('reason') == 'casse' ? 'selected' : '' }}>💥 Casse en rayon ou magasin</option>
                            <option value="produit_perime" {{ old('reason') == 'produit_perime' ? 'selected' : '' }}>⌛ Produit périmé / Date dépassée</option>
                            <option value="produit_endommage" {{ old('reason') == 'produit_endommage' ? 'selected' : '' }}>📦 Produit abîmé / endommagé</option>
                            <option value="perte" {{ old('reason') == 'perte' ? 'selected' : '' }}>🔍 Perte ou vol constaté</option>
                            <option value="retour_fournisseur" {{ old('reason') == 'retour_fournisseur' ? 'selected' : '' }}>↩️ Retour au fournisseur</option>
                            <option value="autre" {{ old('reason') == 'autre' ? 'selected' : '' }}>📝 Autre motif exceptionnel</option>
                        </select>
                    </div>

                    <!-- NOTES / OBSERVATIONS -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Détails & Notes explicatives
                        </label>
                        <textarea name="notes" rows="2" placeholder="Précisez les circonstances de la casse ou de la perte..."
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-rose-600 focus:outline-none transition leading-relaxed">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- BOUTONS D'ACTION -->
                <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.stock.index') }}" 
                       class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
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
