@extends('layouts.app')

@section('title', 'Mouvements de Stock')
@section('page-title', 'Journal des Mouvements de Stock')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('magasinier.stock.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher produit, code-barres..."
                    class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500 w-56">
            </div>

            <select name="type" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
                <option value="">Tous les types</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Entrées (+)</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Sorties (-)</option>
                <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Ventes caisse (-)</option>
                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Ajustements inventaire</option>
                <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Retours / Annulations (+)</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                Filtrer
            </button>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('magasinier.stock.entry') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-down-long"></i> Entrée
            </a>
            <a href="{{ route('magasinier.stock.exit') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-long"></i> Sortie
            </a>
        </div>
    </div>

    <!-- MOVEMENTS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Date & Heure</th>
                        <th class="py-4 px-6">Produit Concerné</th>
                        <th class="py-4 px-6">Type Mouvement</th>
                        <th class="py-4 px-6">Quantité</th>
                        <th class="py-4 px-6">Variation Stock</th>
                        <th class="py-4 px-6">Motif & Référence</th>
                        <th class="py-4 px-6">Opérateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 text-slate-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900 block">{{ $m->product->name ?? 'Produit' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">Réf: {{ $m->product->reference ?? 'N/A' }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    @if($m->type === 'in' || $m->type === 'return') bg-emerald-100 text-emerald-800
                                    @elseif($m->type === 'out') bg-rose-100 text-rose-800
                                    @elseif($m->type === 'sale') bg-sky-100 text-sky-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $m->type }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-black text-sm {{ $m->type === 'in' || $m->type === 'return' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->type === 'in' || $m->type === 'return' ? '+' : '-' }}{{ (float)$m->quantity }} {{ $m->product->unit ?? 'pcs' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                {{ (float)$m->previous_stock }} ➔ <strong class="text-slate-900">{{ (float)$m->new_stock }}</strong>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-slate-800">{{ $m->reason ?? 'Mouvement' }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $m->reference }}</p>
                            </td>
                            <td class="py-4 px-6 text-slate-600">{{ $m->user->full_name ?? 'Système' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-boxes-stacked text-3xl mb-2"></i>
                                <p>Aucun mouvement de stock trouvé.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
