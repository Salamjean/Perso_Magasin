@extends('layouts.app')

@section('title', 'Alertes & Ruptures de Stock')
@section('page_title', 'Gestion des Alertes & Ruptures de Stock')

@section('content')
<div class="space-y-6 w-full">

    <!-- HEADER SYNTHÈSE & CARTES KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- RUPTURES TOTALES KPI -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ruptures Totales</p>
                <h4 class="text-3xl font-black text-rose-600 font-mono">{{ $outOfStockProducts->count() }}</h4>
                <span class="text-xs text-rose-700 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    Stock égal à 0
                </span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
        </div>

        <!-- STOCKS FAIBLES KPI -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Stocks Sous le Seuil</p>
                <h4 class="text-3xl font-black text-amber-600 font-mono">{{ $lowStockProducts->count() }}</h4>
                <span class="text-xs text-amber-700 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                    Réapprovisionnement conseillé
                </span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

    </div>

    <!-- CARTE 1 : PRODUITS EN RUPTURE TOTALE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-rose-50/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-rose-950">Produits en Rupture Totale (Stock = 0)</h3>
                    <p class="text-xs text-rose-800/80 mt-0.5">Ces articles sont épuisés et ne peuvent plus être vendus en caisse.</p>
                </div>
            </div>

            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 shrink-0 self-start sm:self-auto font-mono">
                {{ $outOfStockProducts->count() }} article(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Produit</th>
                        <th class="py-3.5 px-4">Référence & Code</th>
                        <th class="py-3.5 px-4">Rayon / Catégorie</th>
                        <th class="py-3.5 px-4">Fournisseur</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($outOfStockProducts as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6">
                                <a href="{{ route('magasinier.products.show', $p) }}" class="font-bold text-slate-900 hover:text-[#0056a6] transition block text-sm">
                                    {{ $p->name }}
                                </a>
                            </td>
                            <td class="py-4 px-4 font-mono text-slate-500">
                                <div>{{ $p->reference }}</div>
                                @if($p->barcode)
                                    <span class="text-[10px] text-slate-400 font-mono bg-slate-100 px-1 rounded">
                                        {{ $p->barcode }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-slate-700">
                                @if($p->category)
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
                                        <span class="w-2 h-2 rounded-full inline-block" style="background-color: {{ $p->category->color ?? '#0056a6' }}"></span>
                                        {{ $p->category->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Non assigné</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                {{ $p->supplier->company_name ?? 'Non assigné' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('magasinier.stock.entry', ['product_id' => $p->id]) }}" 
                                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-arrow-down text-[10px]"></i>
                                    <span>Réapprovisionner</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-400">
                                <i class="fa-solid fa-circle-check text-2xl text-emerald-500 mb-2 block"></i>
                                <span class="font-medium text-slate-600">Aucun produit en rupture totale. Tout est approvisionné !</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- CARTE 2 : PRODUITS SOUS LE SEUIL D'ALERTE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-amber-50/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-amber-950">Produits sous le Seuil d'Alerte (Stock Faible)</h3>
                    <p class="text-xs text-amber-800/80 mt-0.5">Le stock restant est inférieur ou égal au seuil minimum paramétré.</p>
                </div>
            </div>

            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0 self-start sm:self-auto font-mono">
                {{ $lowStockProducts->count() }} article(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Produit</th>
                        <th class="py-3.5 px-4 text-center">Stock Actuel</th>
                        <th class="py-3.5 px-4 text-center">Seuil Minimum</th>
                        <th class="py-3.5 px-4">Fournisseur</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lowStockProducts as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6">
                                <a href="{{ route('magasinier.products.show', $p) }}" class="font-bold text-slate-900 hover:text-[#0056a6] transition block text-sm">
                                    {{ $p->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $p->reference }}</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                                    {{ (float)$p->stock_quantity }} {{ $p->unit ?? 'u' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center font-mono font-semibold text-slate-600">
                                {{ (float)$p->alert_threshold }} {{ $p->unit ?? 'u' }}
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                {{ $p->supplier->company_name ?? 'Non assigné' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('magasinier.stock.entry', ['product_id' => $p->id]) }}" 
                                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                    <span>Ajouter du stock</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-400">
                                <i class="fa-solid fa-boxes-stacked text-2xl text-slate-300 mb-2 block"></i>
                                <span class="font-medium text-slate-600">Aucun produit sous le seuil d'alerte.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
