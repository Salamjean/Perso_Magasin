@extends('layouts.app')

@section('title', 'Gestion des Dépenses')
@section('page-title', 'Charges & Dépenses du Magasin')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- FORMULAIRE DE CRÉATION DE DÉPENSE -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-1">Enregistrer une dépense</h3>
            <p class="text-xs text-slate-500 mb-6">Électricité, transport, maintenance, salaires...</p>

            <form action="{{ route('admin.expenses.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Intitulé / Libellé <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Ex: Facture CIE, Réparation frigo..."
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Catégorie de charge <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="électricité">Électricité & Énergie</option>
                        <option value="eau">Eau & Utilités</option>
                        <option value="transport">Transport & Carburant</option>
                        <option value="entretien">Entretien & Nettoyage</option>
                        <option value="maintenance">Maintenance & Équipements</option>
                        <option value="salaire">Salaires & Primes</option>
                        <option value="fournitures">Fournitures & Emballages</option>
                        <option value="autre">Autre charge d'exploitation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Montant décaissé (FCFA) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="amount" required placeholder="Ex: 50000"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Date de dépense <span class="text-rose-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Notes & Justificatif</label>
                    <textarea name="notes" rows="2" placeholder="Numéro de reçu ou observations..."
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition">
                    Enregistrer la dépense
                </button>
            </form>
        </div>

        <!-- LISTE DES DÉPENSES -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-slate-800">Historique des Décaissements</h4>
                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-100">
                        Total : {{ number_format($totalExpenses, 0, ',', ' ') }} FCFA
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[10px] font-bold text-slate-500 uppercase">
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4">Libellé</th>
                                <th class="py-3 px-4">Catégorie</th>
                                <th class="py-3 px-4">Montant</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($expenses as $exp)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-3 px-4 text-slate-500">{{ $exp->expense_date->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ $exp->title }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                            {{ $exp->category }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-black text-rose-600">-{{ number_format($exp->amount, 0, ',', ' ') }} FCFA</td>
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route('admin.expenses.destroy', $exp) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                onclick="confirmAction(this.closest('form'), {
                                                    title: 'Supprimer cette dépense ?',
                                                    text: 'Êtes-vous sûr de vouloir supprimer la dépense &laquo; {{ addslashes($exp->title) }} &raquo; de {{ number_format($exp->amount, 0, ',', ' ') }} FCFA ?',
                                                    confirmText: 'Oui, supprimer',
                                                    confirmColor: '#e11d48',
                                                    icon: 'warning'
                                                })"
                                                class="p-1.5 text-rose-500 hover:text-rose-700 rounded-lg cursor-pointer" title="Supprimer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-slate-400">Aucune dépense enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($expenses->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
