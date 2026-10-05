@extends('layouts.app')

@section('title', 'Détails Inventaire : ' . $inventory->reference)
@section('page_title', 'Rapport d\'Inventaire ' . $inventory->reference)

@section('content')
<div class="space-y-6 w-full">

    <!-- SUMMARY HEADER CARD -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-1.5 flex-1">
            <div class="flex flex-wrap items-center gap-3">
                <span class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#0056a6] flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-clipboard-check"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-900">{{ $inventory->reference }}</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Clôturé & Appliqué
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Périmètre : <strong class="text-slate-800 uppercase">{{ $inventory->type === 'general' ? 'Général (Tout le stock)' : ($inventory->category ? 'Rayon ' . $inventory->category->name : 'Par Produit') }}</strong>
                        &nbsp;•&nbsp; Date de clôture : <span class="text-slate-700 font-medium">{{ $inventory->created_at->format('d/m/Y à H:i') }}</span>
                        &nbsp;•&nbsp; Réalisé par : <span class="text-slate-900 font-semibold">{{ $inventory->user->full_name ?? 'Utilisateur' }} ({{ ucfirst($inventory->user->role ?? 'N/A') }})</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            <a href="{{ route('admin.inventory.index') }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Retour aux inventaires</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI SUMMARY CARDS -->
    @php
        $totalItems = $inventory->items->count();
        $okItems = $inventory->items->where('difference', 0)->count();
        $missingItems = $inventory->items->where('difference', '<', 0)->count();
        $surplusItems = $inventory->items->where('difference', '>', 0)->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Articles Comptés</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ $totalItems }}</h4>
                <span class="text-[10px] text-slate-400">Total lignes vérifiées</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Stocks Conformes</p>
                <h4 class="text-2xl font-black text-emerald-600 mt-1 font-mono">{{ $okItems }}</h4>
                <span class="text-[10px] text-emerald-600 font-semibold">Écart = 0</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Manquants / Pertes</p>
                <h4 class="text-2xl font-black text-rose-600 mt-1 font-mono">{{ $missingItems }}</h4>
                <span class="text-[10px] text-rose-600 font-semibold">Stock physique < système</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-minus"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Surplus Constatés</p>
                <h4 class="text-2xl font-black text-sky-600 mt-1 font-mono">{{ $surplusItems }}</h4>
                <span class="text-[10px] text-sky-600 font-semibold">Stock physique > système</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-plus"></i>
            </div>
        </div>

    </div>

    <!-- TABLEAU DÉTAILLÉ DES COMPTAGES -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-table-list text-[#0056a6]"></i>
                    <span>Pointage Physique & Écarts constatés</span>
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    Détail article par article entre le stock théorique et le stock compté physiquement.
                </p>
            </div>

            @if($inventory->notes)
                <div class="px-3.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-200/60 rounded-xl text-xs flex items-center gap-2">
                    <i class="fa-solid fa-note-sticky text-amber-600"></i>
                    <span><strong>Observation :</strong> {{ $inventory->notes }}</span>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Produit & Référence</th>
                        <th class="py-3.5 px-4 text-center">Stock Théorique</th>
                        <th class="py-3.5 px-4 text-center">Stock Compté</th>
                        <th class="py-3.5 px-4 text-center">Écart Constaté</th>
                        <th class="py-3.5 px-6">Justification / Motif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($inventory->items as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-900 block text-sm">{{ $item->product->name ?? 'Article supprimé' }}</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $item->product->reference ?? '' }}</span>
                                    @if($item->product && $item->product->category)
                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">{{ $item->product->category->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-600 text-sm">
                                {{ (float)$item->system_stock }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-black text-slate-900 text-sm">
                                {{ (float)$item->physical_stock }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->difference == 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                        <i class="fa-solid fa-check text-[10px]"></i> 0 (Conforme)
                                    </span>
                                @elseif($item->difference < 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 font-mono">
                                        <i class="fa-solid fa-arrow-down text-[10px]"></i> {{ (float)$item->difference }} (Manque)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200 font-mono">
                                        <i class="fa-solid fa-arrow-up text-[10px]"></i> +{{ (float)$item->difference }} (Surplus)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 text-xs">
                                {{ $item->reason ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
