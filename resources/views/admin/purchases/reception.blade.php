@extends('layouts.app')

@section('title', 'Réceptionner la Commande Fournisseur')
@section('page_title', 'Pointage & Réception ' . $purchase->reference)

@section('content')
<div class="space-y-6 w-full">

    <!-- BANNIÈRE DE STATUT DE RÉCEPTION -->
    @if($purchase->status === 'received')
        <div class="p-5 bg-emerald-50 border-2 border-emerald-200/90 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-emerald-600/20">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h4 class="font-black text-base text-emerald-950">Commande Déjà Réceptionnée & Stocks à Jour</h4>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300/60 font-mono">
                            <i class="fa-solid fa-check text-[10px]"></i> 100% Livrée
                        </span>
                    </div>
                    <p class="text-xs text-emerald-800">
                        Tous les articles de cette commande ont été réceptionnés et ajoutés au stock 
                        @if($purchase->received_date) 
                            le <strong class="text-emerald-950">{{ \Carbon\Carbon::parse($purchase->received_date)->format('d/m/Y à H:i') }}</strong>
                        @endif
                        @if($purchase->receivedBy) 
                            par <strong class="text-emerald-950 bg-emerald-100/80 px-1.5 py-0.5 rounded">{{ $purchase->receivedBy->full_name }}</strong>
                        @endif.
                    </p>
                    <p class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                        <span>En tant qu'administrateur, vous pouvez réajuster les volumes pointés ci-dessous à tout moment.</span>
                    </p>
                </div>
            </div>

            <div class="shrink-0 self-end sm:self-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-emerald-800 text-xs font-bold border border-emerald-200 shadow-2xs">
                    <i class="fa-solid fa-pen-to-square text-[11px] text-emerald-600"></i>
                    <span>Mode Réajustement Actif</span>
                </span>
            </div>
        </div>
    @elseif($purchase->status === 'partial')
        <div class="p-5 bg-amber-50 border-2 border-amber-200/90 rounded-2xl flex items-start sm:items-center gap-4 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-amber-500/20">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="space-y-1 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h4 class="font-black text-base text-amber-950">Réception Partielle en Cours</h4>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 font-mono">
                        Livraison en attente du solde
                    </span>
                </div>
                <p class="text-xs text-amber-800">
                    Une partie des articles a déjà été réceptionnée. Pointez les arrivages complémentaires ci-dessous.
                </p>
            </div>
        </div>
    @endif

    <!-- SUMMARY HEADER CARD -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center gap-4 flex-1">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div class="space-y-1.5 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-900">{{ $purchase->reference }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                        {{ $purchase->supplier->company_name }}
                    </span>
                    @if($purchase->status === 'received')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Complète
                        </span>
                    @elseif($purchase->status === 'partial')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Partielle
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            En attente
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span><strong class="text-slate-700">Commandée le :</strong> {{ $purchase->order_date->format('d/m/Y') }}</span>
                    <span>•</span>
                    <span><strong class="text-slate-700">Téléphone :</strong> {{ $purchase->supplier->phone ?? 'Non renseigné' }}</span>
                    <span>•</span>
                    <span><strong class="text-slate-700">Montant total :</strong> <span class="font-bold text-slate-900 font-mono">{{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA</span></span>
                    @if($purchase->received_date && $purchase->receivedBy)
                        <span>•</span>
                        <span><strong class="text-slate-700">Pointé par :</strong> <span class="text-emerald-700 font-semibold">{{ $purchase->receivedBy->full_name }}</span></span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0 self-end lg:self-center">
            <a href="{{ route('admin.purchases.show', $purchase) }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Retour à la commande</span>
            </a>
        </div>
    </div>

    <!-- 4 CARTES KPI DYNAMIQUES DE POINTAGE -->
    @php
        $totalLines = $purchase->items->count();
        $totalOrdered = $purchase->items->sum('quantity_ordered');
        $totalAlreadyReceived = $purchase->items->sum('quantity_received');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Articles Commandés</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ $totalLines }}</h4>
                <span class="text-[10px] text-slate-400">Références distinctes</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Volume Prévu</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1 font-mono" id="kpi-total-ordered">{{ (float)$totalOrdered }}</h4>
                <span class="text-[10px] text-slate-400">Unités commandées</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Volume Reçu Pointé</p>
                <h4 class="text-2xl font-black text-emerald-600 mt-1 font-mono" id="kpi-total-received">{{ (float)($totalAlreadyReceived > 0 ? $totalAlreadyReceived : $totalOrdered) }}</h4>
                <span class="text-[10px] text-emerald-700 font-semibold">Unités effectives</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-box-open"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Progression</p>
                <span class="text-xs font-black text-indigo-600 font-mono" id="kpi-percent">100%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-1.5">
                <div class="h-2 rounded-full bg-indigo-600 transition-all duration-300" id="kpi-progress-bar" style="width: 100%"></div>
            </div>
            <span class="text-[10px] text-slate-400" id="kpi-progress-label">Pointage complet</span>
        </div>

    </div>

    <!-- FORMULAIRE DE POINTAGE RÉCEPTION -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h4 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-indigo-600"></i>
                    <span>Feuille de Pointage & Réception Magasin</span>
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($purchase->status === 'received')
                        Cette commande a été intégralement réceptionnée. Ajustez les quantités si nécessaire.
                    @else
                        Indiquez la quantité totale effectivement reçue en magasin. Les stocks seront automatiquement incrémentés du différentiel.
                    @endif
                </p>
            </div>

            <!-- BOUTON RAPIDE TOUT RECEVOIR -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="fillAllReceived()" 
                    class="px-3.5 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/80 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-check-double text-emerald-600"></i>
                    <span>Tout pointer comme reçu</span>
                </button>
                <button type="button" onclick="resetAllZero()" 
                    class="px-3 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer" title="Remettre à zéro">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('admin.purchases.reception.process', $purchase) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-6">Produit & Référence</th>
                            <th class="py-3.5 px-4 text-center">Stock Actuel Magasin</th>
                            <th class="py-3.5 px-4 text-center">Quantité Prévue</th>
                            <th class="py-3.5 px-4 text-center">Déjà Reçu</th>
                            <th class="py-3.5 px-6 w-64 text-center">Quantité Totale Reçue <span class="text-rose-500">*</span></th>
                            <th class="py-3.5 px-6 text-center">Statut Pointage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($purchase->items as $idx => $item)
                            <tr class="hover:bg-slate-50/80 reception-row transition">
                                
                                <!-- PRODUIT -->
                                <td class="py-4 px-6">
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 shadow-2xs">
                                            <i class="fa-solid fa-box text-sm"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block text-sm">{{ $item->product->name ?? 'Article' }}</span>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-slate-400 font-mono">{{ $item->product->reference ?? 'REF-N/A' }}</span>
                                                @if($item->product && $item->product->barcode)
                                                    <span class="text-[10px] text-slate-500 font-mono bg-slate-100 px-1.5 py-0.2 rounded border border-slate-200/60">
                                                        <i class="fa-solid fa-barcode text-[9px] mr-0.5"></i> {{ $item->product->barcode }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- STOCK MAGASIN -->
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-mono font-bold text-xs">
                                        {{ (float)($item->product->stock_quantity ?? 0) }} {{ $item->product->unit ?? 'u' }}
                                    </span>
                                </td>

                                <!-- QTÉ PRÉVUE -->
                                <td class="py-4 px-4 text-center font-bold text-slate-900 text-sm font-mono planned-qty" data-val="{{ (float)$item->quantity_ordered }}">
                                    {{ (float)$item->quantity_ordered }}
                                </td>

                                <!-- DÉJÀ REÇU -->
                                <td class="py-4 px-4 text-center text-slate-600 font-mono font-medium already-received-qty" data-val="{{ (float)$item->quantity_received }}">
                                    {{ (float)$item->quantity_received }}
                                </td>

                                <!-- INPUT QUANTITÉ REÇUE AVEC STEPPERS -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="stepDown(this)" 
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold flex items-center justify-center transition cursor-pointer border border-slate-200">
                                            <i class="fa-solid fa-minus text-[10px]"></i>
                                        </button>

                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $idx }}][received_qty]" 
                                            value="{{ (float)($item->quantity_received > 0 ? $item->quantity_received : $item->quantity_ordered) }}" 
                                            required
                                            class="w-28 px-3 py-1.5 text-center bg-slate-50 border border-slate-300 text-slate-900 focus:bg-white focus:border-indigo-600 border rounded-xl text-xs font-black font-mono focus:outline-none transition received-input"
                                            oninput="checkReceptionDelta(this)">

                                        <button type="button" onclick="stepUp(this)" 
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold flex items-center justify-center transition cursor-pointer border border-slate-200">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- STATUT POINTAGE / DELTA -->
                                <td class="py-4 px-6 text-center delta-cell font-bold">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check text-[10px]"></i> Complet
                                    </span>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ACTIONS FOOTER -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-slate-100">
                <p class="text-xs text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span>
                        @if($purchase->status === 'received')
                            Commande clôturée. Tout ajustement sera consigné dans le journal d'audit.
                        @else
                            Un mouvement de stock d'entrée sera automatiquement généré pour chaque article réceptionné.
                        @endif
                    </span>
                </p>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('admin.purchases.show', $purchase) }}" 
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                        Annuler
                    </a>

                    @if($purchase->status === 'received')
                        <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Mettre à jour le pointage</span>
                        </button>
                    @else
                        <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>Valider la réception & Actualiser le stock</span>
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

</div>

<script>
    function checkReceptionDelta(input) {
        const row = input.closest('.reception-row');
        const planned = parseFloat(row.querySelector('.planned-qty').getAttribute('data-val')) || 0;
        const received = parseFloat(input.value) || 0;
        const cell = row.querySelector('.delta-cell');

        if (received >= planned && received > 0) {
            if (received === planned) {
                cell.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                    <i class="fa-solid fa-check text-[10px]"></i> Complet (OK)
                </span>`;
            } else {
                const surplus = (received - planned).toFixed(2).replace(/\.?0+$/, '');
                cell.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200 font-mono">
                    <i class="fa-solid fa-plus text-[10px]"></i> Surplus (+${surplus})
                </span>`;
            }
        } else if (received > 0) {
            const missing = (planned - received).toFixed(2).replace(/\.?0+$/, '');
            cell.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                <i class="fa-solid fa-clock text-[10px]"></i> Partiel (-${missing})
            </span>`;
        } else {
            cell.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 font-mono">
                <i class="fa-solid fa-xmark text-[10px]"></i> Non reçu
            </span>`;
        }

        updateGlobalCounters();
    }

    function stepUp(btn) {
        const input = btn.parentElement.querySelector('.received-input');
        input.value = (parseFloat(input.value) || 0) + 1;
        checkReceptionDelta(input);
    }

    function stepDown(btn) {
        const input = btn.parentElement.querySelector('.received-input');
        const cur = parseFloat(input.value) || 0;
        if (cur > 0) {
            input.value = cur - 1;
            checkReceptionDelta(input);
        }
    }

    function fillAllReceived() {
        document.querySelectorAll('.reception-row').forEach(row => {
            const planned = row.querySelector('.planned-qty').getAttribute('data-val');
            const input = row.querySelector('.received-input');
            input.value = planned;
            checkReceptionDelta(input);
        });
    }

    function resetAllZero() {
        document.querySelectorAll('.reception-row').forEach(row => {
            const input = row.querySelector('.received-input');
            input.value = 0;
            checkReceptionDelta(input);
        });
    }

    function updateGlobalCounters() {
        let totalPlanned = 0;
        let totalReceived = 0;

        document.querySelectorAll('.reception-row').forEach(row => {
            const planned = parseFloat(row.querySelector('.planned-qty').getAttribute('data-val')) || 0;
            const input = row.querySelector('.received-input');
            const received = parseFloat(input.value) || 0;
            totalPlanned += planned;
            totalReceived += received;
        });

        const kpiReceived = document.getElementById('kpi-total-received');
        if (kpiReceived) kpiReceived.innerText = totalReceived.toFixed(2).replace(/\.?0+$/, '');

        const percent = totalPlanned > 0 ? Math.min(100, Math.round((totalReceived / totalPlanned) * 100)) : 0;
        const kpiPercent = document.getElementById('kpi-percent');
        const kpiBar = document.getElementById('kpi-progress-bar');
        const kpiLabel = document.getElementById('kpi-progress-label');

        if (kpiPercent) kpiPercent.innerText = percent + '%';
        if (kpiBar) {
            kpiBar.style.width = percent + '%';
            if (percent === 100) {
                kpiBar.className = 'h-2 rounded-full bg-emerald-500 transition-all duration-300';
            } else if (percent > 0) {
                kpiBar.className = 'h-2 rounded-full bg-amber-500 transition-all duration-300';
            } else {
                kpiBar.className = 'h-2 rounded-full bg-slate-300 transition-all duration-300';
            }
        }
        if (kpiLabel) {
            kpiLabel.innerText = percent === 100 ? 'Pointage complet' : (percent > 0 ? 'Pointage partiel' : 'Aucun article pointé');
        }
    }

    // Initial check on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.received-input').forEach(input => checkReceptionDelta(input));
    });
</script>
@endsection
