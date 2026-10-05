@extends('layouts.app')

@section('title', 'Annulation & Retour de Vente - Caissier')
@section('page_title', 'Annulation de Vente & Retour d\'Articles')

@section('content')
<div class="space-y-6">
    <!-- Notice Annulation directe & Réajustements automatiques -->
    <div class="bg-blue-50/70 border border-blue-200 p-5 rounded-2xl flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-[#0056a6] shrink-0 text-lg">
            <i class="fa-solid fa-rotate-left"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 text-sm">Annulation Directe de Vente par le Caissier</h3>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                Vous pouvez annuler directement une vente enregistrée lors d'une erreur ou d'un retour client. L'annulation réintègre automatiquement les articles vendus dans les stocks et réajuste immédiatement le montant de votre session de caisse ainsi que le compte client si un crédit avait été accordé.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulaire d'Annulation Directe -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs h-fit">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-ban text-rose-600"></i> Annuler un Ticket / Vente
            </h3>

            <form action="{{ route('caissier.returns.cancel') }}" method="POST" id="cancel-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">N° de Vente / Ticket <span class="text-rose-500">*</span></label>
                    <input type="text" name="sale_number" id="sale_number" value="{{ old('sale_number') }}" required placeholder="Ex: VNT-20261005-XXXXX" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#0056a6] focus:border-[#0056a6] focus:outline-none">
                    @error('sale_number')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Motif de l'annulation <span class="text-rose-500">*</span></label>
                    <textarea name="reason" id="cancellation_reason" rows="4" required placeholder="Ex: Erreur de saisie d'article, client a changé d'avis avant d'emporter, produit défectueux..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-[#0056a6] focus:border-[#0056a6] focus:outline-none">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="button" onclick="confirmDirectCancel()" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-rose-600/20 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-trash-can"></i> Valider l'Annulation Immédiate
                </button>
            </form>
        </div>

        <!-- Liste des Ventes Récentes pour Sélection Rapide -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Mes Ventes Récentes Validées</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Cliquez sur « Annuler » pour annuler directement un ticket.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-5">N° Vente</th>
                            <th class="py-3 px-5 text-center">Date & Heure</th>
                            <th class="py-3 px-5 text-center">Articles</th>
                            <th class="py-3 px-5 text-center">Mode Paiement</th>
                            <th class="py-3 px-5 text-center">Montant Total</th>
                            <th class="py-3 px-5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSales as $sale)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                    {{ $sale->sale_number }}
                                </td>
                                <td class="py-3.5 px-5 text-center text-slate-500 font-mono">
                                    {{ $sale->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md font-bold text-[10px]">
                                        {{ $sale->items->count() }} art.
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    @if($sale->payment_method === 'credit')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Crédit</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 capitalize">{{ $sale->payment_method }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-center font-black text-slate-900 font-mono">
                                    {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <button type="button" onclick="selectSaleForCancel('{{ $sale->sale_number }}')" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg transition-all cursor-pointer">
                                        <i class="fa-solid fa-ban mr-1"></i> Annuler
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                    Aucune vente récente validée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recentSales->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $recentSales->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function selectSaleForCancel(saleNumber) {
    document.getElementById('sale_number').value = saleNumber;
    document.getElementById('cancellation_reason').focus();
    Swal.fire({
        icon: 'info',
        title: 'Vente sélectionnée',
        text: 'Le ticket ' + saleNumber + ' est prêt pour annulation. Veuillez renseigner le motif et valider.',
        timer: 2000,
        showConfirmButton: false,
        customClass: {
            popup: 'modern-swal-popup',
            title: 'modern-swal-title',
            htmlContainer: 'modern-swal-text'
        }
    });
}

function confirmDirectCancel() {
    const saleNumber = document.getElementById('sale_number').value.trim();
    const reason = document.getElementById('cancellation_reason').value.trim();

    if (!saleNumber) {
        Swal.fire({
            icon: 'warning',
            title: 'Numéro de vente requis',
            text: 'Veuillez saisir ou sélectionner un numéro de vente.',
            confirmButtonText: 'D\'accord',
            customClass: {
                popup: 'modern-swal-popup',
                title: 'modern-swal-title',
                confirmButton: 'modern-swal-confirm'
            }
        });
        return;
    }

    if (!reason || reason.length < 3) {
        Swal.fire({
            icon: 'warning',
            title: 'Motif obligatoire',
            text: 'Veuillez renseigner le motif de l\'annulation (au moins 3 caractères).',
            confirmButtonText: 'D\'accord',
            customClass: {
                popup: 'modern-swal-popup',
                title: 'modern-swal-title',
                confirmButton: 'modern-swal-confirm'
            }
        });
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Confirmer l\'annulation immédiate ?',
        html: `<p class="text-xs text-slate-600">Vous vous apprêtez à annuler définitivement la vente <strong>${saleNumber}</strong>.<br><br>Les articles seront automatiquement réintégrés en stock et le montant de la session réajusté.</p>`,
        showCancelButton: true,
        confirmButtonText: 'Oui, annuler la vente',
        cancelButtonText: 'Non, fermer',
        confirmButtonColor: '#e11d48',
        customClass: {
            popup: 'modern-swal-popup',
            title: 'modern-swal-title',
            htmlContainer: 'modern-swal-text',
            confirmButton: 'modern-swal-confirm',
            cancelButton: 'modern-swal-cancel',
            actions: 'modern-swal-actions'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancel-form').submit();
        }
    });
}
</script>
@endsection
