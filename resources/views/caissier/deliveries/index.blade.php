@extends('layouts.app')

@section('title', 'Livraisons - Espace Caisse')
@section('page_title', 'Programmation & Suivi des Livraisons')

@section('content')
<div class="space-y-6">

    <!-- BARRE D'ACTIONS ET DE RECHERCHE -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('caissier.deliveries.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="N° Livraison, destinataire, contact..."
                    class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-[#0056a6]/20 focus:border-[#0056a6] w-64 text-slate-800 placeholder:text-slate-400">
            </div>

            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente de coursier</option>
                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assignée au livreur</option>
                <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>En cours de route</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livrée avec succès</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échec / Reportée</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>

            <select name="livreur_id" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none">
                <option value="">Tous les livreurs</option>
                @foreach($livreurs as $l)
                    <option value="{{ $l->id }}" {{ request('livreur_id') == $l->id ? 'selected' : '' }}>{{ $l->full_name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status', 'livreur_id']))
                <a href="{{ route('caissier.deliveries.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-semibold">
                    Réinitialiser
                </a>
            @endif
        </form>

        <a href="{{ route('caissier.deliveries.create') }}" class="px-4 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-md shadow-blue-900/20 transition flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-motorcycle"></i>
            <span>Programmer une Livraison</span>
        </a>
    </div>

    <!-- TABLEAU DES LIVRAISONS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">N° Livraison</th>
                        <th class="py-4 px-6">Type / Vente</th>
                        <th class="py-4 px-6">Destinataire & Contact</th>
                        <th class="py-4 px-6">Adresse de destination</th>
                        <th class="py-4 px-6">Livreur Assigné</th>
                        <th class="py-4 px-6 text-right">Montant à Encaisser</th>
                        <th class="py-4 px-6 text-center">Statut & OTP</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveries as $deliv)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                <a href="{{ route('caissier.deliveries.show', $deliv) }}" class="text-[#0056a6] hover:underline">{{ $deliv->delivery_number }}</a>
                                <p class="text-[10px] text-slate-400 font-sans font-normal">{{ $deliv->created_at->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="py-4 px-6">
                                @if($deliv->sale)
                                    <a href="{{ route('caissier.sales.show', $deliv->sale) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-[#0056a6] font-bold text-[11px] hover:bg-blue-100 transition border border-blue-100">
                                        <i class="fa-solid fa-receipt text-[10px]"></i>
                                        <span>{{ $deliv->sale->sale_number ?? ('Vente #' . $deliv->sale->id) }}</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-bold text-[11px] border border-purple-100">
                                        <i class="fa-solid fa-box text-[10px]"></i>
                                        <span>Hors Vente (Colis)</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-800">{{ $deliv->recipient_name }}</span>
                                <p class="text-[11px] text-slate-500 font-mono">{{ $deliv->recipient_phone }}</p>
                            </td>
                            <td class="py-4 px-6 text-slate-600 max-w-xs truncate" title="{{ $deliv->delivery_address }}">
                                {{ $deliv->delivery_address }}
                            </td>
                            <td class="py-4 px-6">
                                @if($deliv->livreur)
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-800">
                                        <i class="fa-solid fa-person-biking text-sky-600"></i>
                                        {{ $deliv->livreur->full_name }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        En attente
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-black text-slate-900">
                                {{ number_format($deliv->total_amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($deliv->status === 'delivered')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Livrée
                                    </span>
                                @elseif($deliv->status === 'in_transit')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 animate-pulse">
                                        <i class="fa-solid fa-motorcycle mr-1"></i> En cours
                                    </span>
                                @elseif($deliv->status === 'assigned')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-[#0056a6]">
                                        Assignée
                                    </span>
                                @elseif($deliv->status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800" title="{{ $deliv->failure_reason }}">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Échec
                                    </span>
                                @elseif($deliv->status === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Annulée
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        En attente
                                    </span>
                                @endif

                                @if($deliv->otp_code)
                                    <span class="block text-[10px] font-mono text-slate-500 mt-1">OTP: <strong class="text-slate-800">{{ $deliv->otp_code }}</strong></span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('caissier.deliveries.show', $deliv) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-[#0056a6] text-slate-700 rounded-lg font-bold text-xs transition" title="Consulter le suivi">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span>Suivi</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-16 text-slate-400">
                                <div class="w-16 h-16 rounded-full bg-blue-50 text-[#0056a6] flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                                    <i class="fa-solid fa-motorcycle"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Aucune livraison enregistrée</p>
                                <p class="text-[11px] text-slate-400 mt-1">Programmez des livraisons pour des articles achetés ou des colis non achetés.</p>
                                <a href="{{ route('caissier.deliveries.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-[#0056a6] text-white rounded-xl text-xs font-bold hover:bg-[#004485] transition">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Programmer une livraison</span>
                                </a>
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
