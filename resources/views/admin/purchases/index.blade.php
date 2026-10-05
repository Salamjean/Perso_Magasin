@extends('layouts.app')

@section('title', 'Gestion des Achats & Approvisionnements')
@section('page-title', 'Commandes Fournisseurs & Approvisionnements')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.purchases.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
                <option value="">Tous les statuts</option>
                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Commandée (En cours)</option>
                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partiellement réceptionnée</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Entièrement Réceptionnée</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>

            <select name="supplier_id" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
                <option value="">Tous les fournisseurs</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->company_name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                Filtrer
            </button>
        </form>

        <a href="{{ route('admin.purchases.create') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-cart-flatbed"></i>
            <span>Nouvelle Commande Fournisseur</span>
        </a>
    </div>

    <!-- PURCHASES TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Référence Commande</th>
                        <th class="py-4 px-6">Fournisseur</th>
                        <th class="py-4 px-6">Date Commande</th>
                        <th class="py-4 px-6">Montant Total</th>
                        <th class="py-4 px-6">Statut Réception</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.purchases.show', $purchase) }}" class="font-bold text-slate-900 hover:text-indigo-600 font-mono text-sm block">
                                    {{ $purchase->reference }}
                                </a>
                                <p class="text-[10px] text-slate-400">Par {{ $purchase->user->full_name }}</p>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800">
                                {{ $purchase->supplier->company_name }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $purchase->order_date->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-900">
                                {{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-4 px-6">
                                @if($purchase->status === 'received')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check mr-1"></i> Réceptionnée
                                    </span>
                                @elseif($purchase->status === 'partial')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock mr-1"></i> Partielle
                                    </span>
                                @elseif($purchase->status === 'ordered')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                        <i class="fa-solid fa-truck-fast mr-1"></i> Commandée
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                @endif

                                @if($purchase->receivedBy)
                                    <p class="text-[10px] text-emerald-700 font-medium mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-user-check text-[9px]"></i>
                                        <span>Reçu par {{ $purchase->receivedBy->full_name }}</span>
                                    </p>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(in_array($purchase->status, ['ordered', 'partial']))
                                        <a href="{{ route('admin.purchases.reception', $purchase) }}" 
                                            class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1 border border-indigo-200/80 shadow-xs" 
                                            title="Réceptionner la livraison">
                                            <i class="fa-solid fa-truck-ramp-box"></i>
                                            <span class="hidden sm:inline">Réceptionner</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.purchases.show', $purchase) }}" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Consulter la commande">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-dolly text-3xl mb-2"></i>
                                <p>Aucune commande fournisseur enregistrée.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
