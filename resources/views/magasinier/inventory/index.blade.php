@extends('layouts.app')

@section('title', 'Inventaires de Stock')
@section('page-title', 'Sessions d\'Inventaire & Ajustements')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">Inventaires physiques</h3>
            <p class="text-xs text-slate-500">Contrôle des écarts entre stock réel en rayon et stock théorique système</p>
        </div>

        <a href="{{ route('magasinier.inventory.create') }}" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-600/20 transition flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check"></i>
            <span>Lancer un Inventaire</span>
        </a>
    </div>

    <!-- INVENTORIES TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Référence Inventaire</th>
                        <th class="py-4 px-6">Type d'inventaire</th>
                        <th class="py-4 px-6">Date de réalisation</th>
                        <th class="py-4 px-6">Articles vérifiés</th>
                        <th class="py-4 px-6">Opérateur</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inventories as $inv)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                <a href="{{ route('magasinier.inventory.show', $inv) }}" class="text-amber-600 hover:underline">
                                    {{ $inv->reference }}
                                </a>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    @if($inv->type === 'general') bg-indigo-100 text-indigo-800
                                    @elseif($inv->type === 'category') bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $inv->type === 'general' ? 'Général' : ($inv->category ? $inv->category->name : 'Par Produit') }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $inv->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                {{ $inv->items->count() }} article(s)
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $inv->user->full_name ?? 'Magasinier' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('magasinier.inventory.show', $inv) }}" class="p-2 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition" title="Consulter">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-clipboard-list text-3xl mb-2"></i>
                                <p>Aucun inventaire clôturé.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
