@extends('layouts.app')

@section('title', 'Gestion des Clients')
@section('page-title', 'Répertoire Clients & Suivi des Dettes')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, téléphone, email..."
                    class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500 w-56">
            </div>

            <label class="flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs cursor-pointer">
                <input type="checkbox" name="debt_only" value="1" {{ request('debt_only') == '1' ? 'checked' : '' }} onchange="this.form.submit()">
                <span class="text-rose-600 font-bold">Clients avec dettes uniquement</span>
            </label>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                Filtrer
            </button>
        </form>

        <a href="{{ route('admin.customers.create') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-user-plus"></i>
            <span>Nouveau Client</span>
        </a>
    </div>

    <!-- CUSTOMERS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Client</th>
                        <th class="py-4 px-6">Contact</th>
                        <th class="py-4 px-6">Adresse de livraison</th>
                        <th class="py-4 px-6">Achats Réalisés</th>
                        <th class="py-4 px-6">Solde Dettes</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.customers.show', $c) }}" class="font-bold text-slate-900 hover:text-indigo-600 text-sm block">
                                    {{ $c->full_name }}
                                </a>
                                <span class="text-[10px] text-slate-400">Inscrit le {{ $c->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-medium text-slate-800">{{ $c->phone ?? 'N/A' }}</p>
                                <p class="text-[10px] text-slate-400">{{ $c->email ?? 'Aucun email' }}</p>
                            </td>
                            <td class="py-4 px-6 text-slate-600 max-w-xs truncate">
                                {{ $c->address ?? 'Non renseignée' }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                    {{ $c->sales_count }} achat(s)
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($c->debt_balance > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200">
                                        {{ number_format($c->debt_balance, 0, ',', ' ') }} FCFA
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        À jour (0 FCFA)
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.customers.show', $c) }}" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Consulter">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $c) }}" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Modifier">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.customers.destroy', $c) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                            onclick="confirmAction(this.closest('form'), {
                                                title: 'Supprimer ce client ?',
                                                text: 'Êtes-vous sûr de vouloir supprimer {{ addslashes($c->name) }} ?',
                                                confirmText: 'Oui, supprimer',
                                                confirmColor: '#e11d48',
                                                icon: 'warning'
                                            })"
                                            class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Supprimer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-user-group text-3xl mb-2"></i>
                                <p>Aucun client enregistré.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
