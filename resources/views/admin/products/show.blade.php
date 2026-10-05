@extends('layouts.app')

@section('title', 'Fiche Produit : ' . $product->name)
@section('page_title', 'Fiche Produit & Suivi Stock')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- CARTE PRINCIPALE : SYNTHÈSE DU PRODUIT -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 flex-1">
            <!-- PHOTO DU PRODUIT -->
            <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden shrink-0 shadow-2xs">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <i class="fa-solid fa-box text-3xl text-slate-300"></i>
                @endif
            </div>

            <!-- INFOS DU PRODUIT -->
            <div class="space-y-2 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900">{{ $product->name }}</h3>
                    
                    <!-- BADGE STOCK -->
                    @if($product->isOutOfStock())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Rupture de stock
                        </span>
                    @elseif($product->isLowStock())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <i class="fa-solid fa-triangle-exclamation text-[10px] text-amber-500"></i>
                            Stock Faible
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            En Stock
                        </span>
                    @endif

                    <!-- BADGE STATUT -->
                    @if($product->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            Actif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-600">
                            Inactif
                        </span>
                    @endif
                </div>

                <!-- LIGNE MÉTA : RÉFÉRENCE, RAYON, FOURNISSEUR -->
                <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span><strong class="text-slate-700">Réf :</strong> <span class="font-mono">{{ $product->reference }}</span></span>
                    <span>•</span>
                    <span>
                        <strong class="text-slate-700">Rayon :</strong> 
                        @if($product->category)
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
                                <span class="w-2 h-2 rounded-full inline-block" style="background-color: {{ $product->category->color ?? '#0056a6' }}"></span>
                                {{ $product->category->name }}
                            </span>
                        @else
                            <span class="italic text-slate-400">Non assigné</span>
                        @endif
                    </span>
                    <span>•</span>
                    <span>
                        <strong class="text-slate-700">Fournisseur :</strong> 
                        {{ $product->supplier->company_name ?? 'Non assigné' }}
                    </span>
                </p>

                <!-- 3 CARTES DE MÉTRIQUES ALIGNÉES -->
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    
                    <!-- PRIX DE VENTE UNITAIRE -->
                    <div class="px-3.5 py-2 bg-blue-50/80 border border-blue-100 rounded-xl flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white text-[#0056a6] border border-blue-200 flex items-center justify-center text-xs shadow-2xs shrink-0">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block leading-none">Prix de Vente Unitaire</span>
                            <span class="text-sm font-black text-slate-900 font-mono mt-0.5 block leading-tight">
                                {{ number_format($product->sell_price, 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold text-slate-500">FCFA</span>
                            </span>
                        </div>
                    </div>

                    <!-- STOCK ACTUEL -->
                    <div class="px-3.5 py-2 bg-emerald-50/80 border border-emerald-100 rounded-xl flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white text-emerald-600 border border-emerald-200 flex items-center justify-center text-xs shadow-2xs shrink-0">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block leading-none">Stock Actuel</span>
                            <span class="text-sm font-black text-emerald-700 font-mono mt-0.5 block leading-tight">
                                {{ (float)$product->stock_quantity }} <span class="text-[10px] font-sans font-bold text-slate-500">{{ $product->unit ?? 'unités' }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- SEUIL D'ALERTE -->
                    <div class="px-3.5 py-2 bg-amber-50/80 border border-amber-100 rounded-xl flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white text-amber-600 border border-amber-200 flex items-center justify-center text-xs shadow-2xs shrink-0">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block leading-none">Seuil d'Alerte</span>
                            <span class="text-sm font-black text-amber-700 font-mono mt-0.5 block leading-tight">
                                {{ (float)$product->alert_threshold }} <span class="text-[10px] font-sans font-bold text-slate-500">{{ $product->unit ?? 'unités' }}</span>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- BOUTONS D'ACTION HAUT DE PAGE -->
        <div class="flex items-center gap-2 shrink-0 self-end lg:self-center">
            <a href="{{ route('admin.products.edit', $product) }}" 
               class="px-4 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
                <span>Modifier</span>
            </a>
            
            <a href="{{ route('admin.products.index') }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Retour</span>
            </a>
        </div>
    </div>

    <!-- CARTE DÉDIÉE : CODE-BARRES & OUTILS D'IMPRESSION/TÉLÉCHARGEMENT -->
    @if($product->barcode)
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6">
            
            <!-- INFORMATIONS DU CODE-BARRES -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900">Code-barres & Étiquetage</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Code produit : <strong class="font-mono text-slate-800 text-sm tracking-wider">{{ $product->barcode }}</strong></p>
                </div>
            </div>

            <!-- APERÇU SVG DU CODE-BARRES -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 flex items-center justify-center">
                @if($product->barcode_image)
                    <img src="{{ asset('storage/' . $product->barcode_image) }}" 
                         alt="Code-barres {{ $product->barcode }}" 
                         class="h-16 object-contain bg-white px-3 py-1 rounded border border-slate-200 shadow-2xs">
                @else
                    <span class="font-mono font-bold text-slate-700 text-sm tracking-widest">{{ $product->barcode }}</span>
                @endif
            </div>

            <!-- BOUTONS DE TÉLÉCHARGEMENT & IMPRESSION -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.products.download-barcode', $product) }}" 
                   class="px-4 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Télécharger (SVG)</span>
                </a>

                <button type="button" onclick="printBarcodeLabel()" 
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-print text-xs text-slate-600"></i>
                    <span>Imprimer l'étiquette</span>
                </button>
            </div>

        </div>
    @endif

    <!-- DESCRIPTION DU PRODUIT (SI EXISTE) -->
    @if($product->description)
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                <i class="fa-solid fa-align-left text-[#0056a6]"></i>
                <span>Description du Produit</span>
            </h4>
            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
        </div>
    @endif

    <!-- HISTORIQUE DES MOUVEMENTS DE STOCK (DÉFILANT 3 LIGNES) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[#0056a6]"></i>
                    <span>Historique des Mouvements de Stock</span>
                </h4>
                <p class="text-xs text-slate-400 mt-0.5">Traçabilité complète des entrées, sorties, ventes et ajustements</p>
            </div>

            <span class="text-xs font-bold text-slate-600 px-2.5 py-1 bg-slate-100 rounded-lg">
                {{ $movements instanceof \Illuminate\Pagination\LengthAwarePaginator ? $movements->total() : $movements->count() }} mouvement(s)
            </span>
        </div>

        <!-- CONTENEUR DÉFILANT AVEC HAUTEUR FIXE POUR 3 LIGNES -->
        <div class="max-h-[195px] overflow-y-auto overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs relative">
                <thead class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur-xs border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-5">Date & Heure</th>
                        <th class="py-3 px-5">Type de Mouvement</th>
                        <th class="py-3 px-5 text-right">Quantité</th>
                        <th class="py-3 px-5 text-center">Évolution Stock</th>
                        <th class="py-3 px-5">Motif & Référence</th>
                        <th class="py-3 px-5">Opérateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-5 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                {{ $m->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-5">
                                @if($m->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-arrow-down text-[9px]"></i> Entrée / Achat
                                    </span>
                                @elseif($m->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-arrow-up text-[9px]"></i> Sortie / Perte
                                    </span>
                                @elseif($m->type === 'sale')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0056a6] border border-blue-200">
                                        <i class="fa-solid fa-cart-shopping text-[9px]"></i> Vente Caisse
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ ucfirst($m->type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-right font-bold text-xs {{ $m->type === 'in' || $m->type === 'return' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->type === 'in' || $m->type === 'return' ? '+' : '-' }}{{ (float)$m->quantity }} {{ $product->unit ?? 'u' }}
                            </td>
                            <td class="py-3 px-5 text-center font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ (float)$m->previous_stock }} ➔ <strong class="text-slate-800">{{ (float)$m->new_stock }}</strong>
                            </td>
                            <td class="py-3 px-5">
                                <p class="font-semibold text-slate-800">{{ $m->reason ?? 'Mouvement' }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $m->reference }}</p>
                            </td>
                            <td class="py-3 px-5 text-slate-600 text-[11px]">
                                {{ $m->user->full_name ?? 'Système' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">
                                <i class="fa-solid fa-inbox text-2xl mb-1 block text-slate-300"></i>
                                Aucun mouvement de stock enregistré pour cet article.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements instanceof \Illuminate\Pagination\LengthAwarePaginator && $movements->hasPages())
            <div class="p-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    function printBarcodeLabel() {
        const barcodeImg = "{{ asset('storage/' . $product->barcode_image) }}";
        const productName = "{{ addslashes($product->name) }}";
        const barcode = "{{ $product->barcode }}";
        const price = "{{ number_format($product->sell_price, 0, ',', ' ') }} FCFA";

        const printWindow = window.open('', '_blank', 'width=450,height=350');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Impression Étiquette - ${productName}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 20px;
                        margin: 0;
                    }
                    .label-box {
                        border: 2px dashed #334155;
                        padding: 16px;
                        border-radius: 8px;
                        display: inline-block;
                        max-width: 320px;
                    }
                    .product-name {
                        font-weight: bold;
                        font-size: 14px;
                        margin-bottom: 6px;
                        color: #0f172a;
                    }
                    .price {
                        font-size: 16px;
                        font-weight: 900;
                        color: #0056a6;
                        margin-bottom: 8px;
                    }
                    img {
                        max-width: 260px;
                        height: auto;
                    }
                    @media print {
                        body { padding: 0; }
                        .label-box { border: none; }
                    }
                </style>
            </head>
            <body>
                <div class="label-box">
                    <div class="product-name">${productName}</div>
                    <div class="price">${price}</div>
                    <img src="${barcodeImg}" alt="${barcode}" />
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() { window.close(); }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
@endsection
