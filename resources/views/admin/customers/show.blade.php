@extends('layouts.app')

@section('title', 'Fiche Client')
@section('page-title', 'Client : ' . $customer->full_name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- CLIENT HEADER & DEBT SETTLEMENT -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-bold text-slate-900">{{ $customer->full_name }}</h3>
                @if($customer->debt_balance > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-800">
                        Dette : {{ number_format($customer->debt_balance, 0, ',', ' ') }} FCFA
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Solde à jour (0 FCFA)</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Téléphone : <strong>{{ $customer->phone ?? 'N/A' }}</strong>
                &nbsp;•&nbsp; Email : {{ $customer->email ?? 'N/A' }}
                &nbsp;•&nbsp; Adresse : {{ $customer->address ?? 'Non précisée' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-200 transition">
                Modifier
            </a>
            <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Retour
            </a>
        </div>
    </div>

    <!-- ADJUST DEBT MODAL / FORM -->
    <div class="bg-indigo-50/60 rounded-2xl border border-indigo-200 p-6">
        <h4 class="text-sm font-bold text-indigo-950 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-hand-holding-dollar text-indigo-600"></i>
            <span>Règlement de dette / Crédit client</span>
        </h4>
        <form action="{{ route('admin.customers.debt', $customer) }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <select name="action" required class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs">
                <option value="settle">Règlement / Remboursement de dette (-)</option>
                <option value="add">Ajouter une créance (+)</option>
            </select>
            <input type="number" step="0.01" name="amount" required placeholder="Montant (FCFA)"
                class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold">
            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition">
                Valider l'opération
            </button>
        </form>
    </div>

    <!-- SALES HISTORY OF THIS CUSTOMER -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-receipt text-indigo-600"></i>
            <span>Historique des Achats de ce Client</span>
        </h4>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[10px] font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">N° Vente</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Articles</th>
                        <th class="py-3 px-4">Moyen Paiement</th>
                        <th class="py-3 px-4 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $s)
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('admin.sales.show', $s) }}" class="text-indigo-600 hover:underline">{{ $s->sale_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $s->items->count() }} article(s)</td>
                            <td class="py-3 px-4 uppercase font-bold text-slate-600">{{ $s->payment_method }}</td>
                            <td class="py-3 px-4 text-right font-black text-slate-900">{{ number_format($s->total_amount, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400">Aucun achat enregistré pour ce client.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
