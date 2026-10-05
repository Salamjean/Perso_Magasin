@extends('layouts.app')

@section('title', 'Détails Livraison ' . $delivery->delivery_number . ' - Caissier')
@section('page_title', 'Livraison ' . $delivery->delivery_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- EN-TÊTE DE LA LIVRAISON -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-bold font-mono text-slate-900">{{ $delivery->delivery_number }}</h3>
                @if($delivery->status === 'delivered')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Livrée</span>
                @elseif($delivery->status === 'in_transit')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800">En cours</span>
                @elseif($delivery->status === 'assigned')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-[#0056a6]">Assignée</span>
                @elseif($delivery->status === 'failed')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Échec</span>
                @elseif($delivery->status === 'cancelled')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Annulée</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">En attente</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Destinataire : <strong>{{ $delivery->recipient_name }}</strong> ({{ $delivery->recipient_phone }})
                &nbsp;•&nbsp; Adresse : {{ $delivery->delivery_address }}
            </p>
            <p class="text-xs text-slate-400 mt-1">
                Programmée le : {{ $delivery->created_at->format('d/m/Y à H:i') }}
                @if($delivery->delivered_at) &nbsp;•&nbsp; Livrée le : {{ $delivery->delivered_at->format('d/m/Y à H:i') }} @endif
            </p>
        </div>

        <a href="{{ route('caissier.deliveries.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>

    <!-- CODE OTP & LIVREUR EN CHARGE -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        <!-- CODE OTP DE SÉCURITÉ -->
        <div class="rounded-2xl p-6 shadow-md flex items-center justify-between text-white" style="background-color: #0056a6;">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-200">Code de validation OTP Client</span>
                <h2 class="text-3xl font-black tracking-widest font-mono mt-1 text-amber-300">{{ $delivery->otp_code }}</h2>
                <p class="text-[11px] text-blue-100 mt-1">À communiquer au client pour la remise du colis</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-2xl text-amber-300 shadow-inner">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>

        <!-- LIVREUR AFFECTÉ -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Livreur en charge</span>
                @if($delivery->livreur)
                    <div class="flex items-center gap-3 mt-2">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-base">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $delivery->livreur->full_name }}</h4>
                            <p class="text-xs text-slate-500 font-mono">{{ $delivery->livreur->phone ?? 'En service' }}</p>
                        </div>
                    </div>
                @else
                    <div class="mt-2 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium">
                        <i class="fa-solid fa-clock mr-1"></i> Aucun livreur assigné pour le moment.
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Montant à recouvrer :</span>
                <strong class="font-mono text-sm font-black text-slate-900">{{ number_format($delivery->total_amount, 0, ',', ' ') }} FCFA</strong>
            </div>
        </div>
    </div>

    <!-- DÉTAIL DU CONTENU DE LA LIVRAISON (VENTE OU COLIS LIBRE) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-box text-[#0056a6]"></i>
                <span>Contenu de la livraison</span>
            </h4>
            @if($delivery->sale)
                <a href="{{ route('caissier.sales.show', $delivery->sale) }}" class="text-xs font-bold text-[#0056a6] hover:underline flex items-center gap-1">
                    <span>Consulter le ticket {{ $delivery->sale->sale_number }}</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            @endif
        </div>

        <div class="p-6 space-y-4">
            @if($delivery->sale && $delivery->sale->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="py-2.5 px-4">Article</th>
                                <th class="py-2.5 px-4 text-center">Quantité</th>
                                <th class="py-2.5 px-4 text-right">Prix Unitaire</th>
                                <th class="py-2.5 px-4 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($delivery->sale->items as $it)
                                <tr>
                                    <td class="py-2.5 px-4 font-bold text-slate-800">{{ $it->product_name }}</td>
                                    <td class="py-2.5 px-4 text-center font-bold text-slate-700">{{ (float) $it->quantity }}</td>
                                    <td class="py-2.5 px-4 text-right font-mono">{{ number_format($it->unit_price, 0, ',', ' ') }} FCFA</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">{{ number_format($it->total_price ?? $it->subtotal, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($delivery->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="py-2.5 px-4">Article</th>
                                <th class="py-2.5 px-4 text-center">Quantité</th>
                                <th class="py-2.5 px-4 text-right">Prix Unitaire</th>
                                <th class="py-2.5 px-4 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($delivery->items as $it)
                                <tr>
                                    <td class="py-2.5 px-4 font-bold text-slate-800">{{ $it->product_name }}</td>
                                    <td class="py-2.5 px-4 text-center font-bold text-slate-700">{{ (float) $it->quantity }}</td>
                                    <td class="py-2.5 px-4 text-right font-mono">{{ number_format($it->unit_price, 0, ',', ' ') }} FCFA</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">{{ number_format($it->total_price, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($delivery->notes)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                    <strong class="text-slate-800 block mb-1 font-bold">Notes / Description du colis :</strong>
                    <p class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $delivery->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($delivery->failure_reason)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <strong class="font-bold block mb-1">Rapport / Motif d'incident ou d'annulation :</strong>
            <p>{{ $delivery->failure_reason }}</p>
        </div>
    @endif

    <!-- OPTION D'ANNULATION SI LIVRAISON EN COURS OU EN ATTENTE -->
    @if(!in_array($delivery->status, ['delivered', 'cancelled']))
        <div class="p-5 rounded-2xl bg-rose-50/70 border border-rose-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <strong class="text-xs font-bold text-rose-900 block">Annuler cette programmation de livraison</strong>
                <p class="text-[11px] text-rose-700 mt-0.5">En cas de désistement du client ou changement d'avis avant la livraison.</p>
            </div>

            <form action="{{ route('caissier.deliveries.cancel', $delivery) }}" method="POST" id="cancel-delivery-form" class="w-full sm:w-auto">
                @csrf
                <input type="hidden" name="failure_reason" id="delivery_failure_reason" value="Annulation demandée au guichet caisse">
                <button type="button" onclick="confirmCancelDelivery()" 
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                    <i class="fa-solid fa-ban mr-1"></i> Annuler la livraison
                </button>
            </form>
        </div>

        <script>
        function confirmCancelDelivery() {
            Swal.fire({
                title: 'Annuler la livraison ?',
                text: 'Veuillez préciser le motif de l\'annulation :',
                input: 'text',
                inputValue: 'Annulation demandée au guichet caisse',
                showCancelButton: true,
                confirmButtonText: 'Confirmer l\'annulation',
                cancelButtonText: 'Retour',
                confirmButtonColor: '#e11d48',
                inputValidator: (value) => {
                    if (!value || value.trim().length < 3) {
                        return 'Veuillez saisir un motif valide.';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delivery_failure_reason').value = result.value;
                    document.getElementById('cancel-delivery-form').submit();
                }
            });
        }
        </script>
    @endif

</div>
@endsection
