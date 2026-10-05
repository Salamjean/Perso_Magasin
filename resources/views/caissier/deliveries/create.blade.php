@extends('layouts.app')

@section('title', 'Programmer une Livraison - Caissier')
@section('page_title', 'Programmer un Ordre de Livraison')

@section('content')
<div class="w-full space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">
                        <a href="{{ route('caissier.deliveries.index') }}" class="hover:text-[#0056a6] transition">Livraisons</a> /
                    </span>
                    <span class="text-xs font-bold text-slate-600">Nouvelle programmation</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Programmer un Ordre de Livraison</h2>
            </div>
        </div>

        <a href="{{ route('caissier.deliveries.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux livraisons</span>
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1 shadow-xs">
            <div class="font-bold flex items-center gap-2 mb-1">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Veuillez corriger les erreurs ci-dessous :</span>
            </div>
            @foreach($errors->all() as $err)
                <p class="pl-5">• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <!-- FORMULAIRE PRINCIPAL EN 2 COLONNES : ARTICLES À GAUCHE / CHAMPS À DROITE -->
    <form action="{{ route('caissier.deliveries.store') }}" method="POST" id="delivery-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ========================================================================= -->
            <!-- COLONNE GAUCHE (5/12 sur grand écran) : ARTICLES & CONTENU DU COLIS       -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-6 space-y-5">
                
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                    
                    <!-- EN-TÊTE DE LA CARTE GAUCHE -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-sm font-bold shrink-0">
                                <i class="fa-solid fa-boxes-packing"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Articles & Contenu</h3>
                                <p class="text-[11px] text-slate-400">Articles achetés ou colis libre non acheté</p>
                            </div>
                        </div>
                    </div>

                    <!-- SÉLECTEUR DE TYPE : ARTICLES NON ACHETÉS (PREMIER) vs ARTICLES ACHETÉS (DEUXIÈME) -->
                    @php
                        $isSaleDefault = ($selectedSale || old('sale_id') || old('delivery_type') === 'sale');
                    @endphp
                    <div class="grid grid-cols-2 gap-3">
                        <label class="delivery-mode-pill relative flex flex-col items-center text-center p-3.5 rounded-2xl border-2 cursor-pointer transition {{ !$isSaleDefault ? 'border-[#0056a6] bg-blue-50/50 text-[#0056a6]' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700' }}" id="pill-custom">
                            <input type="radio" name="delivery_type" value="custom" {{ !$isSaleDefault ? 'checked' : '' }} onchange="toggleDeliveryMode('custom')" class="sr-only">
                            <i class="fa-solid fa-box-open text-lg mb-1.5 text-purple-600"></i>
                            <span class="text-xs font-bold leading-tight">Articles Non Achetés</span>
                            <span class="text-[10px] text-slate-500 mt-0.5">Course / Colis libre / Catalogue</span>
                        </label>

                        <label class="delivery-mode-pill relative flex flex-col items-center text-center p-3.5 rounded-2xl border-2 cursor-pointer transition {{ $isSaleDefault ? 'border-[#0056a6] bg-blue-50/50 text-[#0056a6]' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700' }}" id="pill-sale">
                            <input type="radio" name="delivery_type" value="sale" {{ $isSaleDefault ? 'checked' : '' }} onchange="toggleDeliveryMode('sale')" class="sr-only">
                            <i class="fa-solid fa-receipt text-lg mb-1.5 text-[#0056a6]"></i>
                            <span class="text-xs font-bold leading-tight">Articles Achetés</span>
                            <span class="text-[10px] text-slate-500 mt-0.5">Lié à une vente / ticket</span>
                        </label>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- BLOC 1 : SI ARTICLES NON ACHETÉS (LIVRAISON LIBRE / CATALOGUE PRODUITS)   -->
                    <!-- ========================================================================= -->
                    <div id="section-custom-items" class="{{ !$isSaleDefault ? '' : 'hidden' }} space-y-4">
                        


                        <!-- SÉLECTEUR DE PRODUITS DU CATALOGUE AVEC RECHERCHE INTÉGRÉE -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-3">
                            <label class="block text-xs font-bold text-slate-700 flex items-center justify-between">
                                <span>Ajouter un article du magasin au colis</span>
                                <span class="text-[10px] text-slate-400 font-normal">({{ $products->count() }} produits disponibles)</span>
                            </label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                                <!-- SÉLECTEUR PERSONNALISÉ AVEC BARRE DE RECHERCHE DEDANS -->
                                <div class="sm:col-span-7 relative" id="searchable-product-container">
                                    <label class="block text-[10px] font-bold text-slate-400 mb-1">Sélectionner un produit</label>
                                    
                                    <!-- Bouton déclencheur -->
                                    <button type="button" onclick="toggleProductDropdown()" id="product-dropdown-btn"
                                            class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 flex items-center justify-between focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition cursor-pointer shadow-2xs text-left">
                                        <span id="product-dropdown-btn-text" class="truncate">-- Rechercher ou choisir un produit ({{ $products->count() }}) --</span>
                                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200 ml-2" id="product-dropdown-arrow"></i>
                                    </button>

                                    <!-- Panneau déroulant avec champ de recherche direct -->
                                    <div id="product-dropdown-menu" 
                                         class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 p-2 space-y-2 hidden">
                                        
                                        <!-- Champ de recherche en haut du select -->
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </span>
                                            <input type="text" id="product-search-input" oninput="filterProductList(this.value)" placeholder="Tapez pour filtrer les produits..."
                                                   class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition"
                                                   autocomplete="off">
                                        </div>

                                        <!-- Liste des options filtrables -->
                                        <div class="max-h-56 overflow-y-auto divide-y divide-slate-50 space-y-0.5" id="product-options-list">
                                            @foreach($products as $prod)
                                                <div class="product-option-item p-2 rounded-xl hover:bg-blue-50 cursor-pointer transition flex items-center justify-between gap-2 text-xs group"
                                                     data-id="{{ $prod->id }}"
                                                     data-name="{{ $prod->name }}"
                                                     data-price="{{ $prod->sell_price }}"
                                                     data-stock="{{ (float)$prod->stock_quantity }}"
                                                     onclick="selectSearchableProduct({{ $prod->id }}, @js($prod->name), {{ $prod->sell_price }}, {{ (float)$prod->stock_quantity }})">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-bold text-slate-800 group-hover:text-[#0056a6] truncate">{{ $prod->name }}</div>
                                                        <div class="text-[10px] text-slate-400">Réf: {{ $prod->reference ?? '—' }}</div>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <div class="font-bold font-mono text-[#0056a6]">{{ number_format($prod->sell_price, 0, ',', ' ') }} F</div>
                                                        <div class="text-[10px] {{ $prod->stock_quantity > 0 ? 'text-emerald-600 font-semibold' : 'text-rose-500 font-bold' }}">
                                                            Stock: {{ (float)$prod->stock_quantity }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div id="no-product-found-msg" class="py-6 text-center text-xs text-slate-400 hidden">
                                                <i class="fa-solid fa-box-open text-xl mb-1 text-slate-300 block"></i>
                                                Aucun produit ne correspond à votre recherche.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Champ masqué pour stocker l'ID du produit -->
                                    <input type="hidden" id="custom_product_select" value="">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold text-slate-400 mb-1">Quantité</label>
                                    <input type="number" id="custom_product_qty" min="1" step="1" value="1" 
                                           class="w-full px-2.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 text-center focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none">
                                </div>

                                <div class="sm:col-span-3">
                                    <button type="button" onclick="addCustomProductToDelivery()" 
                                            class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                        <span>Ajouter</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TABLEAU DES ARTICLES AJOUTÉS AU COLIS -->
                        <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs font-bold text-slate-700">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-list-check text-purple-600"></i>
                                    Articles du colis
                                </span>
                                <span class="text-[11px] font-bold text-purple-700 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded-lg" id="custom-items-count">0 article(s)</span>
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50/80 text-[10px] font-bold uppercase text-slate-400 border-b border-slate-100 sticky top-0">
                                        <tr>
                                            <th class="py-2.5 px-4">Article</th>
                                            <th class="py-2.5 px-3 text-center">Qté</th>
                                            <th class="py-2.5 px-3 text-right">P.U</th>
                                            <th class="py-2.5 px-4 text-right">Total</th>
                                            <th class="py-2.5 px-2 text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="custom-items-tbody" class="divide-y divide-slate-100">
                                        <tr id="empty-custom-items-row">
                                            <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                                                <i class="fa-solid fa-box-open text-2xl mb-1.5 block text-slate-300"></i>
                                                Aucun article sélectionné. Choisissez des produits ci-dessus.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="custom-items-hidden-inputs"></div>
                        </div>



                    </div>

                    <!-- ========================================================================= -->
                    <!-- BLOC 2 : SI ARTICLES ACHETÉS (CHOIX DU TICKET DE VENTE)                  -->
                    <!-- ========================================================================= -->
                    <div id="section-sale-items" class="{{ $isSaleDefault ? '' : 'hidden' }} space-y-4">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Sélectionner le ticket de vente <span class="text-rose-500">*</span></span>
                                @if($selectedSale)
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                                        <i class="fa-solid fa-check text-emerald-500 mr-1"></i>Ticket Pré-sélectionné
                                    </span>
                                @endif
                            </label>
                            
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-receipt"></i>
                                </span>
                                <select name="sale_id" id="sale_id_select" onchange="onSaleSelected(this)"
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition cursor-pointer">
                                    <option value="">Sélectionner une vente récente</option>
                                    @if($selectedSale)
                                        <option value="{{ $selectedSale->id }}" selected>
                                            🎯 {{ $selectedSale->sale_number }} — {{ number_format($selectedSale->total_amount, 0, ',', ' ') }} FCFA ({{ $selectedSale->customer->full_name ?? 'Client comptoir' }})
                                        </option>
                                    @endif
                                    @foreach($recentSales as $s)
                                        @if(!$selectedSale || $selectedSale->id !== $s->id)
                                            <option value="{{ $s->id }}" {{ old('sale_id') == $s->id ? 'selected' : '' }}>
                                                {{ $s->sale_number }} — {{ number_format($s->total_amount, 0, ',', ' ') }} FCFA ({{ $s->created_at->format('d/m H:i') }} - {{ $s->customer->full_name ?? 'Client comptoir' }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- BANDEAU RÉCAPITULATIF DE LA VENTE SÉLECTIONNÉE -->
                        <div id="sale-summary-card" class="{{ $selectedSale ? '' : 'hidden' }} p-4 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Vente N°</span>
                                    <strong class="text-slate-900 font-mono font-bold text-sm" id="summary-sale-number">{{ $selectedSale->sale_number ?? '—' }}</strong>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-400 text-[10px] uppercase font-bold block">Règlement</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase" id="summary-payment-badge">
                                        {{ $selectedSale ? ($selectedSale->payment_method === 'credit' ? 'Crédit' : $selectedSale->payment_method) : '—' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-blue-200/60">
                                <span class="text-slate-500">Montant total du ticket :</span>
                                <strong class="text-[#0056a6] font-mono font-black text-sm" id="summary-total-amount">{{ $selectedSale ? number_format($selectedSale->total_amount, 0, ',', ' ') . ' FCFA' : '0 FCFA' }}</strong>
                            </div>
                        </div>

                        <!-- TABLEAU DES ARTICLES ACHETÉS -->
                        <div class="rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs font-bold text-slate-700">
                                <span>Articles du Panier</span>
                                <span class="text-[11px] font-semibold text-slate-500" id="sale-items-count">{{ $selectedSale ? $selectedSale->items->count() . ' article(s)' : '0 article' }}</span>
                            </div>
                            <div class="max-h-72 overflow-y-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50/80 text-[10px] font-bold uppercase text-slate-400 border-b border-slate-100 sticky top-0">
                                        <tr>
                                            <th class="py-2.5 px-4">Article</th>
                                            <th class="py-2.5 px-3 text-center">Qté</th>
                                            <th class="py-2.5 px-3 text-right">P.U</th>
                                            <th class="py-2.5 px-4 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sale-items-tbody" class="divide-y divide-slate-100">
                                        @if($selectedSale && $selectedSale->items->count() > 0)
                                            @foreach($selectedSale->items as $item)
                                                <tr class="hover:bg-slate-50/60">
                                                    <td class="py-2.5 px-4 font-bold text-slate-800">{{ $item->product_name }}</td>
                                                    <td class="py-2.5 px-3 text-center font-bold text-slate-700 font-mono">{{ (float) $item->quantity }}</td>
                                                    <td class="py-2.5 px-3 text-right text-slate-500 font-mono">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                                                    <td class="py-2.5 px-4 text-right font-bold text-slate-900 font-mono">{{ number_format($item->total_price ?? $item->subtotal, 0, ',', ' ') }} F</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr id="empty-items-row">
                                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                                    <i class="fa-solid fa-receipt text-2xl mb-1.5 block text-slate-300"></i>
                                                    Sélectionnez une vente pour afficher ses articles.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- COLONNE DROITE (6/12 sur grand écran) : CHAMPS À RENSEIGNER & PARAMÈTRES   -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-6 space-y-5">
                
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                    
                    <!-- EN-TÊTE DE LA CARTE DROITE -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold shrink-0">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Destinataire & Paramètres</h3>
                                <p class="text-[11px] text-slate-400">Coordonnées de livraison, montant et coursier</p>
                            </div>
                        </div>
                    </div>

                    <!-- LIGNE 1 : CLIENT EXISTANT, NOM DU DESTINATAIRE & TÉLÉPHONE SUR LA MÊME LIGNE -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- 1. CLIENT EXISTANT -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Client existant <span class="text-[10px] text-slate-400 font-normal">(Optionnel)</span></span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-address-book"></i>
                                </span>
                                <select name="customer_id" id="customer_id_select" onchange="onCustomerSelected(this)"
                                    class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition cursor-pointer">
                                    <option value="">-- Choisir client --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" 
                                                data-name="{{ $c->full_name }}" 
                                                data-phone="{{ $c->phone }}" 
                                                data-address="{{ $c->address }}"
                                                {{ (old('customer_id') == $c->id || ($selectedSale && $selectedSale->customer_id == $c->id)) ? 'selected' : '' }}>
                                            {{ $c->full_name }} ({{ $c->phone ?? 'Sans tél' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 2. NOM DU DESTINATAIRE -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nom du destinataire <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <input type="text" name="recipient_name" id="recipient_name" 
                                    value="{{ old('recipient_name', $selectedSale->customer->full_name ?? '') }}" required 
                                    placeholder="Ex: Kouadio Marc"
                                    class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                            </div>
                        </div>

                        <!-- 3. TÉLÉPHONE DESTINATAIRE -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Téléphone de contact <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-phone"></i>
                                </span>
                                <input type="text" name="recipient_phone" id="recipient_phone" 
                                    value="{{ old('recipient_phone', $selectedSale->customer->phone ?? '') }}" required 
                                    placeholder="Ex: +225 07 00 00 00"
                                    class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- ADRESSE DE LIVRAISON -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Adresse exacte / Repère de livraison <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute top-3 left-3 pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </span>
                            <textarea name="delivery_address" id="delivery_address" rows="2" required 
                                placeholder="Commune, quartier, rue, repère ou indications pour le livreur..."
                                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition leading-relaxed">{{ old('delivery_address', $selectedSale->customer->address ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- GRILLE MONTANT À ENCAISSER & LIVREUR -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- MONTANT À ENCAISSER / FRAIS DE LIVRAISON -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Montant à recouvrer <span class="text-rose-500">*</span></span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </span>
                                <input type="number" step="0.01" name="total_amount" id="total_amount_input" 
                                    value="{{ old('total_amount', 0) }}" required 
                                    class="w-full pl-9 pr-14 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-[11px] font-bold text-slate-400">
                                    FCFA
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Mettre <strong class="text-slate-700">0 FCFA</strong> si déjà payé.</p>
                        </div>

                        <!-- ASSIGNATION DU LIVREUR -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Assigner un coursier
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-helmet-safety"></i>
                                </span>
                                <select name="livreur_id" id="livreur_id_select"
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition cursor-pointer">
                                    <option value="">Assigner plus tard (En attente)</option>
                                    @foreach($livreurs as $l)
                                        <option value="{{ $l->id }}" {{ old('livreur_id') == $l->id ? 'selected' : '' }}>
                                            🛵 {{ $l->full_name }} ({{ $l->phone ?? 'En service' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- INSTRUCTIONS DE LIVRAISON -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Instructions / Consignes particulières pour le livreur
                        </label>
                        <div class="relative">
                            <span class="absolute top-3 left-3 pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-comment-dots"></i>
                            </span>
                            <textarea name="notes" id="notes_input" rows="2" 
                                placeholder="Ex: Appeler dès l'arrivée au portail, monnaie requise sur 10.000 FCFA..."
                                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition leading-relaxed">{{ old('notes') }}</textarea>
                        </div>
                    </div>



                    <!-- BOUTONS D'ACTION EN BAS DE LA COLONNE DROITE -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('caissier.deliveries.index') }}" 
                           class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-[#0056a6] hover:bg-[#004485] text-white font-bold text-xs shadow-md shadow-blue-900/20 transition flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Enregistrer la programmation</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </form>

</div>
@endsection

@section('scripts')
<script>
    // Données des ventes récentes disponibles côté JS pour affichage dynamique instantané
    const recentSalesData = @json($recentSales);
    const selectedSaleData = @json($selectedSale);

    function toggleDeliveryMode(mode) {
        const secSale = document.getElementById('section-sale-items');
        const secCustom = document.getElementById('section-custom-items');
        const pillSale = document.getElementById('pill-sale');
        const pillCustom = document.getElementById('pill-custom');
        const saleSelect = document.getElementById('sale_id_select');

        if (mode === 'sale') {
            secSale.classList.remove('hidden');
            secCustom.classList.add('hidden');
            pillSale.classList.add('border-[#0056a6]', 'bg-blue-50/50', 'text-[#0056a6]');
            pillSale.classList.remove('border-slate-200', 'bg-white', 'text-slate-700');
            pillCustom.classList.remove('border-[#0056a6]', 'bg-blue-50/50', 'text-[#0056a6]');
            pillCustom.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
            saleSelect.setAttribute('required', '');
        } else {
            secSale.classList.add('hidden');
            secCustom.classList.remove('hidden');
            pillCustom.classList.add('border-[#0056a6]', 'bg-blue-50/50', 'text-[#0056a6]');
            pillCustom.classList.remove('border-slate-200', 'bg-white', 'text-slate-700');
            pillSale.classList.remove('border-[#0056a6]', 'bg-blue-50/50', 'text-[#0056a6]');
            pillSale.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
            saleSelect.removeAttribute('required');
            saleSelect.value = '';
            document.getElementById('sale-summary-card').classList.add('hidden');
            renderSaleItemsTable([]);
        }
    }

    function onSaleSelected(select) {
        const saleId = parseInt(select.value);
        if (!saleId) {
            document.getElementById('sale-summary-card').classList.add('hidden');
            renderSaleItemsTable([]);
            return;
        }

        // Trouver la vente dans selectedSaleData ou recentSalesData
        let sale = null;
        if (selectedSaleData && selectedSaleData.id === saleId) {
            sale = selectedSaleData;
        } else {
            sale = recentSalesData.find(s => s.id === saleId);
        }

        if (!sale) return;

        // Afficher le récapitulatif
        document.getElementById('summary-sale-number').innerText = sale.sale_number || ('#' + sale.id);
        document.getElementById('summary-payment-badge').innerText = (sale.payment_method === 'credit' ? 'Crédit' : sale.payment_method).toUpperCase();
        document.getElementById('summary-total-amount').innerText = Number(sale.total_amount).toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('sale-summary-card').classList.remove('hidden');

        // Pré-remplir les coordonnées du client s'il est associé à la vente
        if (sale.customer) {
            const custSelect = document.getElementById('customer_id_select');
            if (custSelect) custSelect.value = sale.customer.id;
            document.getElementById('recipient_name').value = sale.customer.full_name || '';
            document.getElementById('recipient_phone').value = sale.customer.phone || '';
            document.getElementById('delivery_address').value = sale.customer.address || '';
        }

        // Rendre le tableau des articles
        renderSaleItemsTable(sale.items || []);
    }

    function renderSaleItemsTable(items) {
        const tbody = document.getElementById('sale-items-tbody');
        const countSpan = document.getElementById('sale-items-count');

        if (!items || items.length === 0) {
            countSpan.innerText = '0 article';
            tbody.innerHTML = `
                <tr id="empty-items-row">
                    <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-receipt text-2xl mb-1.5 block text-slate-300"></i>
                        Sélectionnez une vente pour afficher ses articles.
                    </td>
                </tr>
            `;
            return;
        }

        countSpan.innerText = `${items.length} article(s)`;
        let html = '';
        items.forEach(it => {
            const lineTotal = it.total_price || (it.unit_price * it.quantity);
            html += `
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="py-2.5 px-4 font-bold text-slate-800">${it.product_name}</td>
                    <td class="py-2.5 px-3 text-center font-bold text-slate-700 font-mono">${parseFloat(it.quantity)}</td>
                    <td class="py-2.5 px-3 text-right text-slate-500 font-mono">${Number(it.unit_price).toLocaleString('fr-FR')} F</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900 font-mono">${Number(lineTotal).toLocaleString('fr-FR')} F</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    let customItemsList = @json(old('items', []));
    let selectedCustomProduct = null;

    document.addEventListener('DOMContentLoaded', function() {
        if (customItemsList && customItemsList.length > 0) {
            renderCustomItemsTable();
        }
    });

    function toggleProductDropdown() {
        const menu = document.getElementById('product-dropdown-menu');
        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            openProductDropdown();
        } else {
            closeProductDropdown();
        }
    }

    function openProductDropdown() {
        const menu = document.getElementById('product-dropdown-menu');
        const arrow = document.getElementById('product-dropdown-arrow');
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        const input = document.getElementById('product-search-input');
        input.value = '';
        filterProductList('');
        setTimeout(() => input.focus(), 50);
    }

    function closeProductDropdown() {
        const menu = document.getElementById('product-dropdown-menu');
        const arrow = document.getElementById('product-dropdown-arrow');
        if (menu) menu.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
    }

    function filterProductList(query) {
        const q = query.toLowerCase().trim();
        const items = document.querySelectorAll('.product-option-item');
        let visibleCount = 0;
        items.forEach(item => {
            const text = (item.getAttribute('data-name') || '').toLowerCase();
            if (text.includes(q)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });
        const noMsg = document.getElementById('no-product-found-msg');
        if (noMsg) {
            if (visibleCount === 0) {
                noMsg.classList.remove('hidden');
            } else {
                noMsg.classList.add('hidden');
            }
        }
    }

    function selectSearchableProduct(id, name, price, stock) {
        selectedCustomProduct = { id, name, price, stock };
        document.getElementById('custom_product_select').value = id;
        document.getElementById('product-dropdown-btn-text').innerHTML = `
            <span class="font-bold text-slate-900">${name}</span> 
            <span class="text-xs text-[#0056a6] font-mono ml-1 font-bold">(${Number(price).toLocaleString('fr-FR')} FCFA)</span>
            <span class="text-[10px] text-emerald-600 font-semibold ml-1">[Stock: ${stock}]</span>
        `;
        closeProductDropdown();
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('searchable-product-container');
        if (container && !container.contains(e.target)) {
            closeProductDropdown();
        }
    });

    function addCustomProductToDelivery() {
        const prodId = parseInt(document.getElementById('custom_product_select').value);
        const qtyInput = document.getElementById('custom_product_qty');
        const qty = parseFloat(qtyInput.value) || 1;

        if (!prodId || !selectedCustomProduct) {
            Swal.fire({
                icon: 'warning',
                title: 'Produit requis',
                text: 'Veuillez rechercher et sélectionner un produit.',
                confirmButtonText: 'D\'accord'
            });
            return;
        }

        const existing = customItemsList.find(i => i.product_id === prodId);
        if (existing) {
            existing.quantity += qty;
        } else {
            customItemsList.push({
                product_id: prodId,
                product_name: selectedCustomProduct.name,
                unit_price: selectedCustomProduct.price,
                quantity: qty,
                stock: selectedCustomProduct.stock
            });
        }

        // Réinitialiser la sélection
        selectedCustomProduct = null;
        document.getElementById('custom_product_select').value = '';
        document.getElementById('product-dropdown-btn-text').innerText = '-- Rechercher ou choisir un produit ({{ $products->count() }}) --';
        qtyInput.value = 1;
        renderCustomItemsTable();

        // Calcul automatique du total suggéré
        const totalSuggested = customItemsList.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
        const totalInput = document.getElementById('total_amount_input');
        if (totalInput && parseFloat(totalInput.value) === 0) {
            totalInput.value = totalSuggested;
        }
    }

    function removeCustomProductFromDelivery(index) {
        customItemsList.splice(index, 1);
        renderCustomItemsTable();
    }

    function changeCustomItemQty(index, delta) {
        if (!customItemsList[index]) return;
        customItemsList[index].quantity += delta;
        if (customItemsList[index].quantity <= 0) {
            customItemsList.splice(index, 1);
        }
        renderCustomItemsTable();
    }

    function renderCustomItemsTable() {
        const tbody = document.getElementById('custom-items-tbody');
        const hiddenInputs = document.getElementById('custom-items-hidden-inputs');
        const countSpan = document.getElementById('custom-items-count');

        if (customItemsList.length === 0) {
            countSpan.innerText = '0 article(s)';
            tbody.innerHTML = `
                <tr id="empty-custom-items-row">
                    <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-box-open text-2xl mb-1.5 block text-slate-300"></i>
                        Aucun article sélectionné. Choisissez des produits ci-dessus ou décrivez le colis.
                    </td>
                </tr>
            `;
            hiddenInputs.innerHTML = '';
            return;
        }

        countSpan.innerText = `${customItemsList.length} article(s)`;
        let html = '';
        let inputsHtml = '';

        customItemsList.forEach((it, idx) => {
            const lineTotal = it.unit_price * it.quantity;
            html += `
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="py-2.5 px-4 font-bold text-slate-800">${it.product_name}</td>
                    <td class="py-2.5 px-3 text-center">
                        <div class="inline-flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden shadow-2xs">
                            <button type="button" onclick="changeCustomItemQty(${idx}, -1)" class="w-5 h-5 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold text-[10px]">-</button>
                            <span class="w-6 text-center font-bold text-slate-900 text-xs font-mono">${it.quantity}</span>
                            <button type="button" onclick="changeCustomItemQty(${idx}, 1)" class="w-5 h-5 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold text-[10px]">+</button>
                        </div>
                    </td>
                    <td class="py-2.5 px-3 text-right text-slate-500 font-mono">${Number(it.unit_price).toLocaleString('fr-FR')} F</td>
                    <td class="py-2.5 px-4 text-right font-bold text-slate-900 font-mono">${Number(lineTotal).toLocaleString('fr-FR')} F</td>
                    <td class="py-2.5 px-2 text-center">
                        <button type="button" onclick="removeCustomProductFromDelivery(${idx})" class="w-6 h-6 rounded-lg text-rose-500 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer" title="Supprimer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </td>
                </tr>
            `;

            inputsHtml += `
                <input type="hidden" name="items[${idx}][product_id]" value="${it.product_id}">
                <input type="hidden" name="items[${idx}][product_name]" value="${it.product_name}">
                <input type="hidden" name="items[${idx}][quantity]" value="${it.quantity}">
                <input type="hidden" name="items[${idx}][unit_price]" value="${it.unit_price}">
            `;
        });

        tbody.innerHTML = html;
        hiddenInputs.innerHTML = inputsHtml;
    }

    function onCustomerSelected(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('recipient_name').value = opt.getAttribute('data-name') || '';
            document.getElementById('recipient_phone').value = opt.getAttribute('data-phone') || '';
            document.getElementById('delivery_address').value = opt.getAttribute('data-address') || '';
        }
    }
</script>
@endsection
