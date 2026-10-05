@extends('layouts.app')

@section('title', 'Détails Commande Fournisseur : ' . $purchase->reference)
@section('page_title', 'Commande Fournisseur ' . $purchase->reference)

@section('content')
<div class="space-y-6 w-full">

    <!-- HEADER SYNTHÈSE & ACTIONS RAPIDES -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        
        <div class="flex items-start sm:items-center gap-4 flex-1">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-dolly"></i>
            </div>

            <div class="space-y-1.5 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 font-mono">{{ $purchase->reference }}</h3>
                    
                    <!-- STATUT BADGE -->
                    @if($purchase->status === 'received')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Entièrement Réceptionnée
                        </span>
                    @elseif($purchase->status === 'partial')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Réception Partielle
                        </span>
                    @elseif($purchase->status === 'ordered')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            <i class="fa-solid fa-truck-fast text-[11px] text-sky-600"></i>
                            En cours de livraison
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ ucfirst($purchase->status) }}
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span><strong class="text-slate-700">Fournisseur :</strong> <span class="font-semibold text-slate-900">{{ $purchase->supplier->company_name }}</span></span>
                    <span>•</span>
                    <span><strong class="text-slate-700">Date commande :</strong> {{ $purchase->order_date->format('d/m/Y') }}</span>
                    <span>•</span>
                    <span><strong class="text-slate-700">Émise par :</strong> {{ $purchase->user->full_name ?? 'Système' }}</span>
                </p>
            </div>
        </div>

        <!-- ACTIONS HAUT DE PAGE -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0 self-end lg:self-center">
            @if(in_array($purchase->status, ['ordered', 'partial']))
                <a href="{{ route('admin.purchases.reception', $purchase) }}" 
                   class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Réceptionner la livraison</span>
                </a>
            @else
                <a href="{{ route('admin.purchases.reception', $purchase) }}" 
                   class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Revoir le pointage</span>
                </a>
            @endif

            <a href="{{ route('admin.purchases.index') }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Retour</span>
            </a>
        </div>
    </div>

    <!-- 4 CARTES KPI DÉTAILLÉES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- MONTANT TOTAL -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Montant Total Achat</p>
                <h4 class="text-xl font-black text-slate-900 mt-1 font-mono">
                    {{ number_format($purchase->total_amount, 0, ',', ' ') }} <span class="text-xs font-bold font-sans text-slate-500">FCFA</span>
                </h4>
                <span class="text-[11px] text-slate-500 mt-0.5 block">{{ $purchase->items->count() }} ligne(s) d'articles</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

        <!-- PROGRESSION DE LIVRAISON -->
        @php
            $totalOrdered = $purchase->items->sum('quantity_ordered');
            $totalReceived = $purchase->items->sum('quantity_received');
            $percentReceived = $totalOrdered > 0 ? min(100, round(($totalReceived / $totalOrdered) * 100)) : 0;
        @endphp
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Taux Réception</p>
                <span class="text-xs font-black {{ $percentReceived == 100 ? 'text-emerald-600' : ($percentReceived > 0 ? 'text-amber-600' : 'text-slate-400') }} font-mono">
                    {{ $percentReceived }}%
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                <div class="h-2 rounded-full transition-all duration-500 {{ $percentReceived == 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}" style="width: {{ $percentReceived }}%"></div>
            </div>
            <span class="text-[11px] text-slate-500 font-medium">
                {{ (float)$totalReceived }} / {{ (float)$totalOrdered }} unités livrées
            </span>
        </div>

        <!-- FOURNISSEUR -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Fournisseur</p>
                <h4 class="text-sm font-bold text-slate-900 mt-1 truncate">{{ $purchase->supplier->company_name }}</h4>
                <p class="text-[11px] text-slate-500 mt-0.5 truncate">
                    <i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i> {{ $purchase->supplier->phone ?? 'N/A' }}
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-lg shrink-0 ml-2">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
        </div>

        <!-- POINTAGE & TRACABILITÉ -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Réceptionnaire</p>
                @if($purchase->receivedBy)
                    <h4 class="text-sm font-bold text-emerald-700 mt-1 truncate flex items-center gap-1.5">
                        <i class="fa-solid fa-user-check text-xs"></i>
                        <span>{{ $purchase->receivedBy->full_name }}</span>
                    </h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Le {{ $purchase->received_date ? $purchase->received_date->format('d/m/Y à H:i') : 'Date non définie' }}
                    </p>
                @else
                    <h4 class="text-sm font-bold text-slate-400 mt-1">Non réceptionnée</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">En attente de livraison</p>
                @endif
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center text-lg shrink-0 ml-2">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
        </div>

    </div>

    <!-- TABLEAU DÉTAILLÉ DES ARTICLES -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing text-indigo-600"></i>
                    <span>Articles & Produits de la Commande</span>
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    Contrôle des volumes commandés, quantités reçues en magasin et prix d'achat.
                </p>
            </div>

            @if($purchase->notes)
                <div class="px-3.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-200/60 rounded-xl text-xs flex items-center gap-2">
                    <i class="fa-solid fa-note-sticky text-amber-600"></i>
                    <span><strong>Note :</strong> {{ $purchase->notes }}</span>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Produit & Référence</th>
                        <th class="py-3.5 px-4 text-center">Stock Magasin Actuel</th>
                        <th class="py-3.5 px-4 text-center">Quantité Commandée</th>
                        <th class="py-3.5 px-4 text-center">Quantité Reçue</th>
                        <th class="py-3.5 px-4 text-right">Prix Achat Unit.</th>
                        <th class="py-3.5 px-6 text-right">Montant Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchase->items as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            
                            <!-- PRODUIT -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0">
                                        <i class="fa-solid fa-box text-sm"></i>
                                    </div>
                                    <div>
                                        @if($item->product)
                                            <a href="{{ route('admin.products.show', $item->product) }}" class="font-bold text-slate-900 hover:text-[#0056a6] transition text-sm block">
                                                {{ $item->product->name }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-slate-400 font-mono">{{ $item->product->reference }}</span>
                                                @if($item->product->barcode)
                                                    <span class="text-[10px] text-slate-400 font-mono bg-slate-100 px-1 rounded">
                                                        <i class="fa-solid fa-barcode mr-0.5"></i> {{ $item->product->barcode }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="font-bold text-slate-400 italic">Article supprimé</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- STOCK ACTUEL -->
                            <td class="py-4 px-4 text-center">
                                @if($item->product)
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-mono font-bold text-xs">
                                        {{ (float)$item->product->stock_quantity }} {{ $item->product->unit ?? 'u' }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- QTÉ COMMANDÉE -->
                            <td class="py-4 px-4 text-center font-bold text-slate-900 text-sm font-mono">
                                {{ (float)$item->quantity_ordered }}
                            </td>

                            <!-- QTÉ REÇUE -->
                            <td class="py-4 px-4 text-center">
                                @if($item->quantity_received >= $item->quantity_ordered)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                        <i class="fa-solid fa-check text-[10px]"></i> {{ (float)$item->quantity_received }}
                                    </span>
                                @elseif($item->quantity_received > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                                        <i class="fa-solid fa-clock text-[10px]"></i> {{ (float)$item->quantity_received }} / {{ (float)$item->quantity_ordered }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 font-mono">
                                        0 (En attente)
                                    </span>
                                @endif
                            </td>

                            <!-- PRIX ACHAT -->
                            <td class="py-4 px-4 text-right font-mono text-slate-700 font-semibold">
                                {{ number_format($item->unit_buy_price, 0, ',', ' ') }} FCFA
                            </td>

                            <!-- TOTAL LIGNE -->
                            <td class="py-4 px-6 text-right font-black text-slate-900 font-mono text-sm">
                                {{ number_format($item->total_price, 0, ',', ' ') }} FCFA
                            </td>

                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 bg-slate-50/80 font-bold">
                        <td colspan="5" class="py-4 px-6 text-right text-xs uppercase text-slate-600 tracking-wider">
                            Total Global de la Commande Fournisseur :
                        </td>
                        <td class="py-4 px-6 text-right text-base text-indigo-700 font-black font-mono">
                            {{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</div>
@endsection
