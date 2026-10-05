@extends('layouts.app')

@section('title', 'Fiche Stock : ' . $product->name)
@section('page_title', 'Fiche Produit & Suivi des Stocks')

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
        <div class="flex flex-wrap items-center gap-2 shrink-0 self-end lg:self-center">
            <a href="{{ route('magasinier.stock.entry') }}" 
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-arrow-down text-xs"></i>
                <span>+ Entrée</span>
            </a>
            
            <a href="{{ route('magasinier.stock.exit') }}" 
               class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up text-xs"></i>
                <span>- Sortie</span>
            </a>

            <a href="{{ route('magasinier.products.index') }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Retour</span>
            </a>
        </div>
    </div>

    <!-- CARTE DÉDIÉE : CODE-BARRES DU PRODUIT -->
    @if($product->barcode)
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900">Code-barres & Étiquetage</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Valeur scannable : <span class="font-mono font-bold text-slate-800">{{ $product->barcode }}</span></p>
                </div>
            </div>

            <!-- APERÇU DU CODE-BARRES SVG -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center">
                <svg id="barcode-preview" class="max-h-12 w-auto"></svg>
            </div>
        </div>
    @endif

    <!-- HISTORIQUE DES MOUVEMENTS DE STOCK DU PRODUIT (AVEC CONTENEUR SCROLLABLE) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
        
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-[#0056a6]"></i>
                <span>Historique des flux de stock</span>
            </h4>
            <span class="text-xs font-semibold text-slate-400 font-mono">{{ $movements->total() }} mouvement(s) enregistré(s)</span>
        </div>

        <div class="border border-slate-200/80 rounded-xl overflow-hidden shadow-2xs">
            <div class="overflow-x-auto max-h-[220px] overflow-y-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="sticky top-0 z-10 bg-slate-100/95 backdrop-blur-xs border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-2.5 px-4">Date & Heure</th>
                            <th class="py-2.5 px-4 text-center">Type</th>
                            <th class="py-2.5 px-4 text-center">Quantité</th>
                            <th class="py-2.5 px-4 text-center">Évolution Stock</th>
                            <th class="py-2.5 px-4">Motif & Réf</th>
                            <th class="py-2.5 px-4 text-right">Opérateur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($movements as $m)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-slate-500 font-mono">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($m->type === 'in')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Entrée</span>
                                    @elseif($m->type === 'out')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Sortie / Perte</span>
                                    @elseif($m->type === 'sale')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Vente</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ ucfirst($m->type) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-bold font-mono text-sm {{ $m->type === 'in' || $m->type === 'return' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $m->type === 'in' || $m->type === 'return' ? '+' : '-' }}{{ (float)$m->quantity }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-slate-600">
                                    <span class="text-slate-400">{{ (float)$m->previous_stock }}</span>
                                    <i class="fa-solid fa-arrow-right text-[9px] text-slate-400 mx-1"></i>
                                    <span class="font-bold text-slate-900">{{ (float)$m->new_stock }}</span>
                                </td>
                                <td class="py-3 px-4 text-slate-800">
                                    <span class="font-semibold block">{{ $m->reason }}</span>
                                    @if($m->reference)
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $m->reference }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-slate-500 font-medium">
                                    {{ $m->user->full_name ?? 'Système' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400">
                                    <i class="fa-solid fa-boxes-stacked text-2xl mb-1 block"></i>
                                    Aucun mouvement de stock enregistré pour ce produit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($movements->hasPages())
            <div class="pt-2">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>

@if($product->barcode)
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                JsBarcode("#barcode-preview", "{{ $product->barcode }}", {
                    format: "CODE128",
                    lineColor: "#1e293b",
                    width: 2,
                    height: 40,
                    displayValue: true,
                    fontSize: 12,
                    font: "monospace"
                });
            } catch (e) {
                console.warn("Barcode rendering error:", e);
            }
        });
    </script>
@endif
@endsection
