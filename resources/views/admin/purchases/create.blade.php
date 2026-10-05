@extends('layouts.app')

@section('title', 'Passer une Commande Fournisseur')
@section('page-title', 'Nouvelle Commande Approvisionnement')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between pb-6 mb-6 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800">Commande auprès d'un fournisseur</h3>
                <p class="text-xs text-slate-500">Sélectionnez le fournisseur et ajoutez les articles avec prix d'achat convenus</p>
            </div>
            <a href="{{ route('admin.purchases.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                @foreach($errors->all() as $err)
                    <p>• {{ $err }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.purchases.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Fournisseur <span class="text-rose-500">*</span></label>
                    <select name="supplier_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sélectionner un fournisseur</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->company_name }} ({{ $sup->city }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Date de commande <span class="text-rose-500">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Notes & Instructions pour la livraison</label>
                    <textarea name="notes" rows="2" placeholder="Ex: Livraison demandée pour jeudi matin..."
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- ARTICLES COMMANDE -->
            <div class="pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Articles à commander</h4>
                    <button type="button" onclick="addProductRow()" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> Ajouter une ligne
                    </button>
                </div>

                <div id="product-rows" class="space-y-3">
                    <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs items-center product-row">
                        <div class="col-span-6">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Produit</label>
                            <select name="products[0][id]" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs" onchange="updatePrice(this)">
                                <option value="">Choisir un produit</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-price="{{ $prod->buy_price }}">{{ $prod->name }} (Réf: {{ $prod->reference }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-3">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Quantité commandée</label>
                            <input type="number" step="0.01" name="products[0][quantity]" value="1" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Prix Achat unitaire (FCFA)</label>
                            <input type="number" step="0.01" name="products[0][price]" value="0" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs price-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.purchases.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                    Valider la commande
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let rowIndex = 1;
    function addProductRow() {
        const container = document.getElementById('product-rows');
        const firstRow = container.children[0];
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/\d+/, rowIndex));
            }
            if (el.tagName === 'INPUT') {
                if (el.type === 'number' && el.classList.contains('price-input')) el.value = 0;
                else if (el.type === 'number') el.value = 1;
            } else if (el.tagName === 'SELECT') {
                el.selectedIndex = 0;
            }
        });

        container.appendChild(newRow);
        rowIndex++;
    }

    function updatePrice(select) {
        const opt = select.options[select.selectedIndex];
        const price = opt.getAttribute('data-price') || 0;
        const row = select.closest('.product-row');
        const priceInput = row.querySelector('.price-input');
        if (priceInput) priceInput.value = price;
    }
</script>
@endsection
