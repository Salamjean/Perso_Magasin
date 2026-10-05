@extends('layouts.app')

@section('title', 'Détails de la Vente')
@section('page-title', 'Vente ' . $sale->sale_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- SUMMARY HEADER -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-bold font-mono text-slate-900">{{ $sale->sale_number }}</h3>
                @if($sale->status === 'completed')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Complétée</span>
                @elseif($sale->status === 'cancelled')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Annulée</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Date & Heure : <strong>{{ $sale->created_at->format('d/m/Y H:i') }}</strong>
                &nbsp;•&nbsp; Caissier : {{ $sale->user->full_name }}
                &nbsp;•&nbsp; Client : {{ $sale->customer->full_name ?? 'Client Comptoir' }}
            </p>
            <p class="text-xs text-slate-500 mt-1">
                Moyen de paiement : <span class="font-bold {{ $sale->payment_method === 'credit' ? 'text-amber-800' : 'text-slate-700' }} uppercase">{{ $sale->payment_method === 'credit' ? 'À CRÉDIT (DETTE CLIENT)' : $sale->payment_method }}</span>
                @if($sale->payment_method === 'credit')
                    &nbsp;•&nbsp; <span class="text-amber-800 font-bold">Mis en dette client : {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</span>
                @else
                    &nbsp;•&nbsp; Reçu : {{ number_format($sale->amount_received, 0, ',', ' ') }} FCFA
                    &nbsp;•&nbsp; Monnaie rendue : {{ number_format($sale->amount_change, 0, ',', ' ') }} FCFA
                    @if($sale->credit_amount > 0)
                        &nbsp;•&nbsp; <span class="text-amber-800 font-bold">Crédit accordé : {{ number_format($sale->credit_amount, 0, ',', ' ') }} FCFA</span>
                    @endif
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('caissier.pos.receipt', $sale) }}" target="_blank" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-200 transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Ticket / Facture</span>
            </a>
            <a href="{{ route('admin.sales.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Retour
            </a>
        </div>
    </div>

    @if($sale->status === 'cancelled')
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <strong class="block font-bold">Vente annulée</strong>
            <p class="mt-0.5">Motif : {{ $sale->cancellation_reason }}</p>
        </div>
    @endif

    <!-- ITEMS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-basket-shopping text-indigo-600"></i>
            <span>Articles de la vente</span>
        </h4>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[10px] font-bold text-slate-500 uppercase">
                        <th class="py-3 px-4">Produit</th>
                        <th class="py-3 px-4">Quantité</th>
                        <th class="py-3 px-4">Prix Unitaire</th>
                        <th class="py-3 px-4">Remise</th>
                        <th class="py-3 px-4 text-right">Total Ligne</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $item)
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $item->product_name }}</td>
                            <td class="py-3 px-4 text-slate-700 font-semibold">{{ (float)$item->quantity }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 text-slate-500">{{ number_format($item->discount, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">{{ number_format($item->total_price, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 text-xs">
                        <td colspan="4" class="py-2 px-4 text-right font-semibold text-slate-500">Sous-total :</td>
                        <td class="py-2 px-4 text-right font-bold text-slate-800">{{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if($sale->discount > 0)
                        <tr class="text-xs text-rose-600">
                            <td colspan="4" class="py-1 px-4 text-right font-semibold">Remise globale :</td>
                            <td class="py-1 px-4 text-right font-bold">- {{ number_format($sale->discount, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr class="border-t-2 border-slate-200 font-bold bg-slate-50">
                        <td colspan="4" class="py-4 px-4 text-right uppercase text-slate-700">Total Net Payé :</td>
                        <td class="py-4 px-4 text-right text-base text-indigo-700 font-black">{{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ANNULATION VENTE PAR L'ADMINISTRATEUR -->
    @if($sale->status === 'completed')
        <div class="bg-rose-50/60 rounded-2xl border border-rose-200 p-6">
            <h4 class="text-sm font-bold text-rose-900 mb-2 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Annulation & Réajustement de Stock (Droit Administrateur)</span>
            </h4>
            <p class="text-xs text-rose-700 mb-4">L'annulation de cette vente réinjectera automatiquement les quantités vendues dans les stocks du supermarché.</p>

            <form action="{{ route('admin.sales.cancel', $sale) }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="text" name="cancellation_reason" required placeholder="Motif de l'annulation (ex: erreur de caisse, retour marchandise...)"
                    class="flex-1 px-4 py-2.5 bg-white border border-rose-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500">
                <button type="button" 
                    onclick="const input = this.form.querySelector('input[name=cancellation_reason]');
                             if(!input.value.trim()){ Swal.fire({ icon: 'warning', title: 'Attention', text: 'Veuillez renseigner le motif d\'annulation.', confirmButtonColor: '#0056a6' }); return; }
                             confirmAction(this.form, {
                                 title: 'Annuler cette vente ?',
                                 text: 'Cette action réinjectera immédiatement les articles dans le stock.',
                                 confirmText: 'Oui, annuler la vente',
                                 confirmColor: '#e11d48',
                                 icon: 'warning'
                             })"
                    class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition shrink-0 cursor-pointer">
                    Annuler la vente
                </button>
            </form>
        </div>
    @endif

</div>
@endsection
