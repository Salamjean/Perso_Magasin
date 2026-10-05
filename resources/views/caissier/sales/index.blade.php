@extends('layouts.app')

@section('title', 'Mes Ventes - Caissier')
@section('page_title', 'Historique de mes Ventes')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Mes Ventes Réalisées</h2>
            <p class="text-sm text-slate-500">Consultez l'historique complet des tickets émis lors de vos sessions.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('caissier.pos.index') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fas fa-cash-register"></i> Ouvrir le Terminal POS
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <form action="{{ route('caissier.sales.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase">Recherche</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="N° Ticket ou Client..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase">Moyen de Paiement</label>
                <select name="payment_method" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Tous les modes</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Espèces</option>
                    <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>Vente à Crédit</option>
                    <option value="mobile_money" {{ request('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Carte bancaire</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-medium transition-all">
                    Filtrer
                </button>
                <a href="{{ route('caissier.sales.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm transition-all" title="Réinitialiser">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tableau des ventes (Éléments bien centrés) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-5">N° Ticket & Heure</th>
                        <th class="py-3.5 px-5 text-center">Client</th>
                        <th class="py-3.5 px-5 text-center">Articles</th>
                        <th class="py-3.5 px-5 text-center">Moyen de Paiement</th>
                        <th class="py-3.5 px-5 text-center">Montant Total</th>
                        <th class="py-3.5 px-5 text-center">Statut</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-5 align-middle">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <div>
                                        <span class="font-mono font-bold text-slate-900 block text-xs">
                                            {{ $sale->invoice_number ?? $sale->sale_number }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">
                                            {{ $sale->created_at->format('d/m/Y à H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($sale->customer)
                                    <span class="font-bold text-slate-900 block text-xs">{{ $sale->customer->full_name ?? $sale->customer->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $sale->customer->phone ?? '—' }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500">
                                        Client standard
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] rounded-lg font-bold border border-slate-200">
                                    {{ $sale->items_count ?? $sale->items->count() }} art.
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold
                                    @if($sale->payment_method === 'cash') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($sale->payment_method === 'credit') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($sale->payment_method === 'mobile_money') bg-amber-50 text-amber-700 border border-amber-200
                                    @elseif($sale->payment_method === 'card') bg-sky-50 text-sky-700 border border-sky-200
                                    @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                                    <i class="fa-solid {{ $sale->payment_method === 'cash' ? 'fa-money-bill-wave' : ($sale->payment_method === 'credit' ? 'fa-hand-holding-dollar' : ($sale->payment_method === 'mobile_money' ? 'fa-mobile-screen-button' : 'fa-credit-card')) }} text-[10px]"></i>
                                    <span class="capitalize">{{ $sale->payment_method === 'credit' ? 'Crédit' : $sale->payment_method }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="font-black text-slate-900 text-xs font-mono">
                                    {{ number_format($sale->total_amount, 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold text-slate-500">FCFA</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($sale->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Validée
                                    </span>
                                @elseif($sale->status === 'refunded' || $sale->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Annulée
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 text-[10px] rounded-full font-semibold border border-slate-200">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('caissier.sales.show', $sale->id) }}" class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#0056a6] flex items-center justify-center transition" title="Détails">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    @if($sale->delivery)
                                        <a href="{{ route('caissier.deliveries.show', $sale->delivery) }}" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 flex items-center justify-center transition" title="Suivi Livraison (#{{ $sale->delivery->delivery_number }})">
                                            <i class="fa-solid fa-motorcycle text-xs"></i>
                                        </a>
                                    @elseif($sale->status === 'completed')
                                        <a href="{{ route('caissier.deliveries.create', ['sale_id' => $sale->id]) }}" class="w-8 h-8 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 flex items-center justify-center transition" title="Programmer Livraison">
                                            <i class="fa-solid fa-motorcycle text-xs"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('caissier.pos.receipt', $sale->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition" title="Imprimer Ticket">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </a>
                                    @if($sale->status === 'completed')
                                        <form action="{{ route('caissier.sales.cancel', $sale->id) }}" method="POST" id="cancel-form-{{ $sale->id }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="cancellation_reason" id="reason-input-{{ $sale->id }}" value="">
                                            <button type="button" onclick="promptCancelSale({{ $sale->id }}, '{{ $sale->sale_number ?? $sale->invoice_number }}')" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition cursor-pointer" title="Annuler cette vente">
                                                <i class="fa-solid fa-ban text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Aucune vente trouvée</p>
                                <p class="text-[11px] text-slate-400 mt-1">Aucun ticket n'a été émis pour cette période.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function promptCancelSale(saleId, saleNumber) {
    Swal.fire({
        title: 'Annuler la vente ' + saleNumber + ' ?',
        text: 'Veuillez saisir le motif de l\'annulation :',
        input: 'text',
        inputPlaceholder: 'Ex: Erreur de frappe, retour client...',
        showCancelButton: true,
        confirmButtonText: 'Confirmer l\'annulation',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#e11d48',
        inputValidator: (value) => {
            if (!value || value.trim().length < 3) {
                return 'Veuillez renseigner un motif d\'au moins 3 caractères.';
            }
        },
        customClass: {
            popup: 'modern-swal-popup',
            title: 'modern-swal-title',
            confirmButton: 'modern-swal-confirm',
            cancelButton: 'modern-swal-cancel',
            actions: 'modern-swal-actions'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('reason-input-' + saleId).value = result.value.trim();
            document.getElementById('cancel-form-' + saleId).submit();
        }
    });
}
</script>
@endsection
