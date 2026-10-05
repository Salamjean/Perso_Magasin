@extends('layouts.app')

@section('title', 'Inventaires de Stock')
@section('page_title', 'Gestion des Sessions d\'Inventaire')

@section('content')
<div class="space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">Stock & Audit /</span>
                    <span class="text-xs font-bold text-slate-600">Sessions d'inventaire</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Inventaires Physiques & Ajustements</h2>
                <p class="text-xs text-slate-500 mt-0.5">Contrôle des écarts entre stock réel en rayon et stock théorique système</p>
            </div>
        </div>

        <a href="{{ route('admin.inventory.create') }}" 
           class="px-4 py-2.5 bg-[#0056a6] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-900/20 transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Lancer un Nouvel Inventaire</span>
        </a>
    </div>

    <!-- TABLEAU DES INVENTAIRES RÉALISÉS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- BARRE DE RECHERCHE / FILTRE RAPIDE -->
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Réf ou opérateur..."
                        class="w-full pl-9 pr-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:border-[#0056a6] focus:outline-none transition">
                </div>

                <select name="type" onchange="this.form.submit()"
                    class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les types</option>
                    <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>Général (Tout le stock)</option>
                    <option value="category" {{ request('type') === 'category' ? 'selected' : '' }}>Par Rayon / Catégorie</option>
                </select>

                <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Filtrer
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Référence</th>
                        <th class="py-3.5 px-6">Périmètre</th>
                        <th class="py-3.5 px-6">Date de Clôture</th>
                        <th class="py-3.5 px-6 text-center">Articles Pointés</th>
                        <th class="py-3.5 px-6">Opérateur</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inventories as $inv)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                <a href="{{ route('admin.inventory.show', $inv) }}" class="text-[#0056a6] hover:underline flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-lines text-xs text-slate-400"></i>
                                    <span>{{ $inv->reference }}</span>
                                </a>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    @if($inv->type === 'general') bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @elseif($inv->type === 'category') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $inv->type === 'general' ? 'Général (Magasin)' : ($inv->category ? 'Rayon : ' . $inv->category->name : 'Par Produit') }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                {{ $inv->created_at->format('d/m/Y à H:i') }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-bold text-slate-800">
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                    {{ $inv->items->count() }} article(s)
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-700 font-medium">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold uppercase">
                                        {{ substr($inv->user->first_name ?? 'U', 0, 1) }}
                                    </div>
                                    <span>{{ $inv->user->full_name ?? 'Utilisateur' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.inventory.show', $inv) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-blue-50 hover:text-[#0056a6] hover:border-blue-200 text-slate-600 text-xs font-bold transition" title="Consulter le rapport détaillé">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span>Rapport</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-clipboard-list text-3xl mb-2 text-slate-300"></i>
                                <p class="font-medium text-xs">Aucun inventaire enregistré pour le moment.</p>
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
