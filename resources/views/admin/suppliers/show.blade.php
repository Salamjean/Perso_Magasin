@extends('layouts.app')

@section('title', 'Fiche Fournisseur')
@section('page-title', 'Détails du Fournisseur')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- SUPPLIER CARD -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-bold text-slate-900">{{ $supplier->company_name }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">Fournisseur</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Contact : <strong>{{ $supplier->contact_name ?? 'Non spécifié' }}</strong>
                &nbsp;•&nbsp; Téléphone : {{ $supplier->phone ?? 'N/A' }}
                &nbsp;•&nbsp; Email : {{ $supplier->email ?? 'N/A' }}
            </p>
            <p class="text-xs text-slate-400 mt-1">
                <i class="fa-solid fa-location-dot mr-1"></i> {{ $supplier->address ?? 'Aucune adresse' }}, {{ $supplier->city }}
                @if($supplier->tax_number) &nbsp;•&nbsp; N° Fiscal : {{ $supplier->tax_number }} @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-200 transition">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Modifier
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Retour
            </a>
        </div>
    </div>

    <!-- PRODUCTS SUPPLIED -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-boxes-stacked text-indigo-600"></i>
            <span>Catalogue des Produits Livrés par ce Fournisseur</span>
        </h4>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[10px] font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Produit</th>
                        <th class="py-3 px-4">Réf</th>
                        <th class="py-3 px-4">Prix Achat</th>
                        <th class="py-3 px-4">Prix Vente</th>
                        <th class="py-3 px-4">Stock Actuel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $p->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $p->reference }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-600">{{ number_format($p->buy_price, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ number_format($p->sell_price, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4">
                                <span class="font-bold {{ $p->isOutOfStock() ? 'text-rose-600' : ($p->isLowStock() ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ (float)$p->stock_quantity }} {{ $p->unit }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400">Aucun produit rattaché à ce fournisseur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
