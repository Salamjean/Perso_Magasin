@extends('layouts.app')

@section('title', 'Réceptions Fournisseurs')
@section('page-title', 'Réception des Livraisons Fournisseurs')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-800">Commandes Fournisseurs à Réceptionner</h3>
            <p class="text-xs text-slate-500">Contrôlez les colis reçus, saisissez les quantités réelles et mettez à jour le stock</p>
        </div>

        <form method="GET" action="{{ route('magasinier.receptions.index') }}" class="flex items-center gap-2">
            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs" onchange="this.form.submit()">
                <option value="">Tous les états de livraison</option>
                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>En attente de livraison</option>
                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partiellement reçue</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Entièrement reçue</option>
            </select>
        </form>
    </div>

    <!-- RECEPTIONS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">N° Commande</th>
                        <th class="py-4 px-6">Fournisseur</th>
                        <th class="py-4 px-6">Date Commande</th>
                        <th class="py-4 px-6">Progression Réception</th>
                        <th class="py-4 px-6">Statut</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                {{ $p->reference }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                {{ $p->supplier->company_name }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $p->order_date->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ $p->items->sum('quantity_received') }} / {{ $p->items->sum('quantity_ordered') }} articles
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($p->status === 'received')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Complète
                                    </span>
                                @elseif($p->status === 'partial')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Partielle
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                        En attente
                                    </span>
                                @endif

                                @if($p->receivedBy)
                                    <p class="text-[10px] text-emerald-700 font-medium mt-1 flex items-center gap-1">
                                        <i class="fa-solid fa-user-check text-[9px]"></i>
                                        <span>{{ $p->receivedBy->full_name }}</span>
                                    </p>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                @php
                                    $isLocked = $p->received_date && ! $p->received_date->isToday();
                                @endphp
                                @if($isLocked)
                                    <a href="{{ route('magasinier.receptions.process', $p) }}" 
                                       class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold text-xs transition inline-flex items-center gap-1.5 border border-slate-200 shadow-2xs" 
                                       title="Consulter le pointage (Verrouillé - Délai dépassé)">
                                        <i class="fa-solid fa-lock text-[11px] text-slate-500"></i>
                                        <span>Consulter</span>
                                    </a>
                                @else
                                    <a href="{{ route('magasinier.receptions.process', $p) }}" 
                                       class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs transition inline-flex items-center gap-1.5 shadow-xs">
                                        <i class="fa-solid fa-truck-ramp-box"></i>
                                        <span>{{ $p->status === 'received' ? 'Modifier pointage' : 'Réceptionner' }}</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2"></i>
                                <p>Aucune commande fournisseur en attente de réception.</p>
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
