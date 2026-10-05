@extends('layouts.app')

@section('title', 'Détail Vente #' . ($sale->sale_number ?? $sale->invoice_number) . ' - Caissier')
@section('page_title', 'Détail de la Vente #' . ($sale->sale_number ?? $sale->invoice_number))

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- En-tête -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold font-mono text-slate-900">{{ $sale->sale_number ?? $sale->invoice_number }}</h2>
                @if($sale->status === 'completed')
                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs rounded-full font-bold">Validée</span>
                @elseif($sale->status === 'cancelled' || $sale->status === 'refunded')
                    <span class="px-2.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 text-xs rounded-full font-bold">Annulée</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">Enregistrée le {{ $sale->created_at->format('d/m/Y à H:i:s') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            @if($sale->status === 'completed')
                @if($sale->delivery)
                    <a href="{{ route('caissier.deliveries.show', $sale->delivery) }}" class="px-3.5 py-2 bg-blue-50 text-[#0056a6] border border-blue-200 hover:bg-blue-100 font-bold rounded-xl text-xs transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-motorcycle text-xs"></i> 
                        <span>Livraison ({{ $sale->delivery->delivery_number }})</span>
                    </a>
                @else
                    <a href="{{ route('caissier.deliveries.create', ['sale_id' => $sale->id]) }}" class="px-3.5 py-2 bg-[#0056a6] hover:bg-[#004485] text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-motorcycle text-xs"></i> 
                        <span>Programmer Livraison</span>
                    </a>
                @endif
            @endif
            <a href="{{ route('caissier.pos.receipt', $sale->id) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-print text-xs"></i> Réimprimer
            </a>
            <a href="{{ route('caissier.sales.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-all">
                <i class="fa-solid fa-arrow-left mr-1"></i> Retour
            </a>
        </div>
    </div>

    @if($sale->delivery)
        <div class="p-4 rounded-2xl bg-blue-50/80 border border-blue-200 text-xs flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white text-[#0056a6] border border-blue-200 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-motorcycle text-sm"></i>
                </div>
                <div>
                    <strong class="text-slate-900 font-bold">Livraison programmée pour cette vente (#{{ $sale->delivery->delivery_number }})</strong>
                    <p class="text-slate-600 mt-0.5">Destinataire : {{ $sale->delivery->recipient_name }} ({{ $sale->delivery->recipient_phone }}) • Statut : <span class="font-bold text-[#0056a6] uppercase">{{ $sale->delivery->status }}</span></p>
                </div>
            </div>
            <a href="{{ route('caissier.deliveries.show', $sale->delivery) }}" class="px-3 py-1.5 bg-white border border-blue-300 text-[#0056a6] hover:bg-blue-100 font-bold rounded-xl text-xs transition shrink-0">
                Suivi Livraison
            </a>
        </div>
    @endif

    @if($sale->status === 'cancelled')
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <strong class="block font-bold mb-1">Vente annulée</strong>
            <p>Motif : {{ $sale->cancellation_reason ?? 'Non précisé' }}</p>
        </div>
    @endif

    <!-- Informations Générales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Informations Caisse</h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-500">Caissier :</span> <strong class="text-slate-800">{{ $sale->user->full_name ?? 'Moi-même' }}</strong></div>
                <div><span class="text-slate-500">Caisse :</span> <strong class="text-slate-800">{{ $sale->cashSession->cashRegister->name ?? 'Caisse Principale' }}</strong></div>
                <div><span class="text-slate-500">Mode :</span> <strong class="text-slate-800 uppercase">{{ $sale->payment_method === 'credit' ? 'À Crédit (Dette)' : $sale->payment_method }}</strong></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Informations Client</h3>
            <div class="space-y-2 text-xs">
                @if($sale->customer)
                    <div><span class="text-slate-500">Nom :</span> <strong class="text-slate-800">{{ $sale->customer->full_name }}</strong></div>
                    <div><span class="text-slate-500">Téléphone :</span> <strong class="text-slate-800">{{ $sale->customer->phone ?? '—' }}</strong></div>
                    <div><span class="text-slate-500">Dette actuelle :</span> <strong class="text-amber-800">{{ number_format($sale->customer->debt_balance, 0, ',', ' ') }} FCFA</strong></div>
                @else
                    <p class="text-slate-400 italic">Client Comptoir (Passage)</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Règlement</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between"><span class="text-slate-500">Total Vente :</span> <strong class="text-slate-900 font-mono font-bold">{{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</strong></div>
                @if($sale->payment_method === 'credit')
                    <div class="flex justify-between"><span class="text-amber-700 font-bold">Porté en dette :</span> <strong class="text-amber-800 font-mono">{{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</strong></div>
                @else
                    <div class="flex justify-between"><span class="text-slate-500">Montant reçu :</span> <span class="text-slate-800 font-mono">{{ number_format($sale->amount_received ?? $sale->received_amount, 0, ',', ' ') }} FCFA</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Monnaie rendue :</span> <strong class="text-emerald-600 font-mono">{{ number_format($sale->amount_change ?? $sale->change_amount, 0, ',', ' ') }} FCFA</strong></div>
                @endif
            </div>
        </div>
    </div>

    <!-- Détail des articles -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 font-bold text-slate-800 text-xs flex items-center justify-between">
            <span>Articles du Panier ({{ $sale->items->count() }})</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Article</th>
                        <th class="px-6 py-3.5 text-center">Quantité</th>
                        <th class="px-6 py-3.5 text-right">Prix Unitaire</th>
                        <th class="px-6 py-3.5 text-right">Remise</th>
                        <th class="px-6 py-3.5 text-right">Total Ligne</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-800">
                                {{ $item->product_name }}
                            </td>
                            <td class="px-6 py-3.5 text-center font-bold text-slate-700">
                                {{ (float) $item->quantity }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-mono">
                                {{ number_format($item->unit_price, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-3.5 text-right text-rose-600 font-mono">
                                {{ $item->discount > 0 ? '-' . number_format($item->discount, 0, ',', ' ') . ' FCFA' : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900 font-mono">
                                {{ number_format($item->total_price ?? $item->subtotal, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="4" class="px-6 py-2.5 text-right text-slate-500 font-medium">Sous-total Brut :</td>
                        <td class="px-6 py-2.5 text-right font-bold text-slate-800 font-mono">{{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if($sale->discount > 0)
                        <tr>
                            <td colspan="4" class="px-6 py-2 text-right text-rose-600 font-medium">Remise Globale :</td>
                            <td class="px-6 py-2 text-right font-bold text-rose-600 font-mono">-{{ number_format($sale->discount, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr class="border-t border-slate-200">
                        <td colspan="4" class="px-6 py-3.5 text-right text-slate-900 font-bold text-sm uppercase">TOTAL NET :</td>
                        <td class="px-6 py-3.5 text-right font-black text-[#0056a6] text-base font-mono">{{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- BLOC D'ANNULATION DIRECTE PAR LE CAISSIER -->
    @if($sale->status === 'completed')
        <div class="bg-rose-50/60 rounded-2xl border border-rose-200 p-6">
            <h4 class="text-sm font-bold text-rose-900 mb-2 flex items-center gap-2">
                <i class="fa-solid fa-ban text-rose-600"></i>
                <span>Annulation Directe de cette Vente</span>
            </h4>
            <p class="text-xs text-rose-700 mb-4">L'annulation réintègre immédiatement les articles dans le stock et réajuste votre caisse.</p>

            <form action="{{ route('caissier.sales.cancel', $sale) }}" method="POST" id="cancel-sale-form" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="text" name="cancellation_reason" id="show_cancellation_reason" required placeholder="Motif de l'annulation (ex: erreur de saisie, client a changé d'avis...)"
                    class="flex-1 px-4 py-2.5 bg-white border border-rose-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">
                <button type="button" onclick="confirmCancelThisSale()"
                    class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition shrink-0 cursor-pointer">
                    <i class="fa-solid fa-trash-can mr-1"></i> Annuler cette vente
                </button>
            </form>
        </div>

        <script>
        function confirmCancelThisSale() {
            const reason = document.getElementById('show_cancellation_reason').value.trim();
            if (!reason || reason.length < 3) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Motif obligatoire',
                    text: 'Veuillez renseigner le motif de l\'annulation (au moins 3 caractères).',
                    confirmButtonText: 'D\'accord'
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Confirmer l\'annulation ?',
                text: 'Les articles seront réintégrés au stock et la caisse sera réajustée.',
                showCancelButton: true,
                confirmButtonText: 'Oui, annuler la vente',
                cancelButtonText: 'Non, retour',
                confirmButtonColor: '#e11d48'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('cancel-sale-form').submit();
                }
            });
        }
        </script>
    @endif

</div>
@endsection
