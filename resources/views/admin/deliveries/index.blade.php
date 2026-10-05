@extends('layouts.app')

@section('title', 'Gestion des Livraisons - Administration')
@section('page_title', 'Suivi des Livraisons & Affectations')

@section('content')
<div class="w-full space-y-6">

    <!-- EN-TÊTE ET FILTRES -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.deliveries.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="N° Livraison, destinataire, tél..."
                    class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition w-60">
            </div>

            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none cursor-pointer">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assignée au livreur</option>
                <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>En cours de route</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livrée</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échec / Incident</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>

            <select name="livreur_id" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none cursor-pointer">
                <option value="">Tous les livreurs</option>
                @foreach($livreurs as $l)
                    <option value="{{ $l->id }}" {{ request('livreur_id') == $l->id ? 'selected' : '' }}>
                        🛵 {{ $l->full_name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Filtrer</span>
            </button>

            @if(request()->hasAny(['search', 'status', 'livreur_id']))
                <a href="{{ route('admin.deliveries.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-semibold transition">
                    Réinitialiser
                </a>
            @endif
        </form>

        <a href="{{ route('admin.deliveries.create') }}" class="px-5 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-md shadow-blue-900/20 transition flex items-center gap-2 shrink-0 cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Créer une Livraison</span>
        </a>
    </div>

    <!-- TABLE DES LIVRAISONS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">N° Course / Type</th>
                        <th class="py-3.5 px-5">Destinataire & Contact</th>
                        <th class="py-3.5 px-5">Adresse de destination</th>
                        <th class="py-3.5 px-5">Livreur en charge</th>
                        <th class="py-3.5 px-5 text-right">Montant</th>
                        <th class="py-3.5 px-5 text-center">Statut</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveries as $deliv)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5">
                                <a href="{{ route('admin.deliveries.show', $deliv) }}" class="font-mono font-bold text-[#0056a6] hover:underline">
                                    {{ $deliv->delivery_number }}
                                </a>
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    @if($deliv->sale_id)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-50 text-[#0056a6] border border-blue-100">
                                            Ticket #{{ $deliv->sale->sale_number ?? $deliv->sale_id }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                            Course libre ({{ $deliv->items->count() }} art.)
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $deliv->created_at->format('d/m/Y H:i') }}</span>
                            </td>

                            <td class="py-3.5 px-5">
                                <strong class="text-slate-900 font-bold block">{{ $deliv->recipient_name }}</strong>
                                <span class="text-slate-500 font-mono text-[11px]">{{ $deliv->recipient_phone }}</span>
                            </td>

                            <td class="py-3.5 px-5 text-slate-600 max-w-xs truncate" title="{{ $deliv->delivery_address }}">
                                {{ $deliv->delivery_address }}
                            </td>

                            <td class="py-3.5 px-5">
                                @if($deliv->livreur)
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-800">
                                        <i class="fa-solid fa-motorcycle text-[#0056a6]"></i>
                                        {{ $deliv->livreur->full_name }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Non assignée
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-5 text-right font-mono font-black text-slate-900">
                                {{ number_format($deliv->total_amount, 0, ',', ' ') }} FCFA
                            </td>

                            <td class="py-3.5 px-5 text-center">
                                @if($deliv->status === 'delivered')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Livrée
                                    </span>
                                @elseif($deliv->status === 'in_transit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                        <i class="fa-solid fa-motorcycle mr-1 animate-bounce"></i> En cours
                                    </span>
                                @elseif($deliv->status === 'assigned')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-[#0056a6]">
                                        Assignée
                                    </span>
                                @elseif($deliv->status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800" title="{{ $deliv->failure_reason }}">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Échec
                                    </span>
                                @elseif($deliv->status === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Annulée
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        En attente
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-5 text-right">
                                <a href="{{ route('admin.deliveries.show', $deliv) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-[#0056a6] hover:text-white text-slate-600 text-xs font-bold transition" title="Consulter la fiche">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span>Détails</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-motorcycle text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs">Aucune livraison enregistrée pour le moment.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveries->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
