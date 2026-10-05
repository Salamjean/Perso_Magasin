@extends('layouts.app')

@section('title', 'Modifier le Produit : ' . $product->name)
@section('page_title', 'Gestion des Produits')

@section('content')
<div class="w-full lg:w-[80%] mx-auto space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1 font-medium">
                <a href="{{ route('admin.products.index') }}" class="hover:text-[#0056a6] transition">Produits</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-700 font-bold">Édition produit</span>
            </div>
            <h2 class="text-lg font-black text-slate-900 tracking-tight">Modifier : {{ $product->name }}</h2>
            <p class="text-xs text-slate-500">Mettez à jour les informations, le prix ou l'image de l'article.</p>
        </div>

        <a href="{{ route('admin.products.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer self-start sm:self-auto">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux produits</span>
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1 shadow-xs">
            <div class="font-bold flex items-center gap-2 mb-1">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Veuillez corriger les erreurs suivantes :</span>
            </div>
            @foreach($errors->all() as $err)
                <p class="pl-5">• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <!-- FORMULAIRE PRINCIPAL (LAYOUT 2 COLONNES : IMAGE À GAUCHE / CHAMPS À DROITE) -->
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- COLONNE GAUCHE (IMAGE DU PRODUIT) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- CARTE IMAGE DU PRODUIT -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs text-center flex flex-col items-center">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-4 block">Image du produit</span>
                    
                    <!-- CONTENEUR APERÇU IMAGE -->
                    <div class="relative w-44 h-44 mb-4 group">
                        <div id="product-image-preview" class="w-full h-full rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 flex flex-col items-center justify-center overflow-hidden transition group-hover:border-[#0056a6]">
                            @if($product->image)
                                <img id="product-img" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                <i id="product-placeholder-icon" class="fa-solid fa-box-open text-4xl text-slate-400 mb-1 hidden"></i>
                                <span id="product-placeholder-text" class="text-[10px] text-slate-400 font-medium hidden">Aucune image</span>
                            @else
                                <i id="product-placeholder-icon" class="fa-solid fa-box-open text-4xl text-slate-400 mb-1"></i>
                                <span id="product-placeholder-text" class="text-[10px] text-slate-400 font-medium">Aucune image</span>
                                <img id="product-img" src="" alt="Aperçu" class="w-full h-full object-cover hidden">
                            @endif
                        </div>

                        <!-- BOUTON D'ACTION SUR L'IMAGE -->
                        <label for="product-image-input" class="absolute -bottom-2 -right-2 w-9 h-9 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl flex items-center justify-center shadow-md cursor-pointer transition" title="Changer l'image">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </label>
                    </div>

                    <input type="file" name="image" id="product-image-input" accept="image/*" class="hidden" onchange="previewProductImage(event)">

                    <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
                        Formats : <strong class="text-slate-700">JPG, PNG, WebP</strong><br>
                        Taille max : <strong class="text-slate-700">2 Mo</strong>
                    </p>

                    <button type="button" onclick="document.getElementById('product-image-input').click()"
                            class="mt-4 w-full py-2 px-3 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Changer l'image
                    </button>
                </div>

                <!-- CARTE ÉTAT DU STOCK ACTUEL -->
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 text-slate-700 space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-500 uppercase tracking-wider text-[10px]">Stock en réserve & rayon</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ (float)$product->stock_quantity }} {{ $product->unit }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Réf interne : <span class="font-mono font-bold text-slate-700">{{ $product->reference }}</span>
                    </p>
                </div>

            </div>

            <!-- COLONNE DROITE (CHAMPS DE SAISIE DU FORMULAIRE) -->
            <div class="lg:col-span-8 space-y-6">

                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                    
                    <!-- EN-TÊTE SECTION 1 : INFORMATIONS DE BASE -->
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-tag text-[#0056a6]"></i>
                            <span>Informations & Classification</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Désignation, code-barres et catégorie du produit</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        
                        <!-- CODE-BARRES (SCAN OU GÉNÉRATION) -->
                        <div class="sm:col-span-2">
                            <label for="barcode-input" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Code-barres (EAN / Douchette)
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <i class="fa-solid fa-barcode absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                    <input type="text" name="barcode" id="barcode-input" value="{{ old('barcode', $product->barcode) }}"
                                           placeholder="Scannez avec la douchette ou tapez..."
                                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border @error('barcode') border-rose-300 bg-rose-50/30 @else border-slate-200 @enderror rounded-xl text-xs font-mono font-bold text-slate-800 placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                </div>
                                <button type="button" onclick="generateBarcode()"
                                        class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 cursor-pointer"
                                        title="Générer un code-barres aléatoire unique">
                                    <i class="fa-solid fa-wand-magic-sparkles text-xs text-[#0056a6]"></i>
                                    <span>Générer</span>
                                </button>
                            </div>
                            @error('barcode')
                                <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NOM DU PRODUIT (OBLIGATOIRE) -->
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nom du produit <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-basket-shopping absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                                       placeholder="Ex: Riz Parfumé Dinor 5kg, Huile Végétale Dinor 1L..."
                                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border @error('name') border-rose-300 bg-rose-50/30 @else border-slate-200 @enderror rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                            </div>
                            @error('name')
                                <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CATÉGORIE / RAYON -->
                        <div>
                            <label for="category_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Rayon / Catégorie
                            </label>
                            <div class="relative">
                                <select name="category_id" id="category_id"
                                        class="w-full px-4 py-2.5 bg-slate-50 border @error('category_id') border-rose-300 @else border-slate-200 @enderror rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                    <option value="">Sélectionner un rayon</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- FOURNISSEUR -->
                        <div>
                            <label for="supplier_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Fournisseur
                            </label>
                            <div class="relative">
                                <select name="supplier_id" id="supplier_id"
                                        class="w-full px-4 py-2.5 bg-slate-50 border @error('supplier_id') border-rose-300 @else border-slate-200 @enderror rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                    <option value="">Aucun fournisseur associé</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>
                                            {{ $sup->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Description <span class="text-slate-400 font-normal lowercase">(optionnelle)</span>
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      placeholder="Brève description ou caractéristiques du produit..."
                                      class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">{{ old('description', $product->description) }}</textarea>
                        </div>

                    </div>

                    <!-- EN-TÊTE SECTION 2 : PRIX & SEUIL -->
                    <div class="border-t border-slate-100 pt-6">
                        <div class="border-b border-slate-100 pb-4 mb-5">
                            <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                                <i class="fa-solid fa-coins text-[#0056a6]"></i>
                                <span>Tarification & Seuil d'Alerte</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Mettez à jour le prix de vente unitaire</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            
                            <!-- PRIX UNITAIRE (OBLIGATOIRE) -->
                            <div>
                                <label for="sell_price" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Prix Unitaire (Vente) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="fa-solid fa-coins absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                    <input type="number" step="1" min="0" name="sell_price" id="sell_price" value="{{ old('sell_price', (int)$product->sell_price) }}" required
                                           placeholder="Ex: 1500"
                                           class="w-full pl-9 pr-14 py-2.5 bg-slate-50 border @error('sell_price') border-rose-300 bg-rose-50/30 @else border-slate-200 @enderror rounded-xl text-xs font-black text-slate-900 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                    <span class="absolute right-3 top-2.5 text-[11px] font-bold text-slate-400">FCFA</span>
                                </div>
                                @error('sell_price')
                                    <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- SEUIL D'ALERTE -->
                            <div>
                                <label for="alert_threshold" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Seuil d'alerte rupture
                                </label>
                                <div class="relative">
                                    <i class="fa-solid fa-triangle-exclamation absolute left-3.5 top-3 text-amber-500 text-xs"></i>
                                    <input type="number" step="1" min="0" name="alert_threshold" id="alert_threshold" value="{{ old('alert_threshold', (int)$product->alert_threshold) }}"
                                           placeholder="Ex: 5"
                                           class="w-full pl-9 pr-14 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                    <span class="absolute right-3 top-2.5 text-[11px] font-bold text-slate-400">unités</span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- ACTIONS DU FORMULAIRE EN BAS -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-end gap-3">
                    <a href="{{ route('admin.products.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#0056a6] hover:bg-[#004485] text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Enregistrer les modifications</span>
                    </button>
                </div>

            </div>

        </div>

    </form>

</div>

<script>
    function previewProductImage(event) {
        const file = event.target.files[0];
        const previewImg = document.getElementById('product-img');
        const placeholderIcon = document.getElementById('product-placeholder-icon');
        const placeholderText = document.getElementById('product-placeholder-text');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                if (placeholderIcon) placeholderIcon.classList.add('hidden');
                if (placeholderText) placeholderText.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    function generateBarcode() {
        const input = document.getElementById('barcode-input');
        if (!input) return;

        let code = '200' + Math.floor(Math.random() * 900000000 + 100000000).toString();
        let checksum = 0;
        for (let i = 0; i < 12; i++) {
            checksum += parseInt(code[i]) * (i % 2 === 0 ? 1 : 3);
        }
        let checkDigit = (10 - (checksum % 10)) % 10;
        code += checkDigit.toString();

        input.value = code;
        input.focus();
    }
</script>
@endsection
