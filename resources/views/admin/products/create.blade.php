@extends('layouts.app')

@section('title', 'Nouveau Produit')
@section('page_title', 'Gestion des Produits')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================= -->
    <!-- ÉTAPE 1 : ÉCRAN D'ATTENTE DU SCAN DE CODE-BARRES                         -->
    <!-- ========================================================================= -->
    <div id="step-scan" class="max-w-xl mx-auto space-y-6 transition-all duration-300">
        
        <!-- HEADER SCAN -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center font-bold">
                    <i class="fa-solid fa-barcode text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">Enregistrement Produit</h2>
                    <p class="text-xs text-slate-400">Étape 1 sur 2 : Détection du code-barres</p>
                </div>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i> Annuler
            </a>
        </div>

        <!-- CARTE CENTRALE ATTENTE SCAN -->
        <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-sm text-center relative overflow-hidden space-y-6">
            
            <!-- FAISCEAU LASER & ICÔNE ANIMÉE -->
            <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                <div class="absolute inset-0 rounded-3xl bg-blue-50/80 border-2 border-dashed border-[#0056a6]/40 animate-pulse"></div>
                <div class="w-20 h-20 rounded-2xl bg-[#0056a6] text-white flex items-center justify-center text-3xl shadow-lg shadow-[#0056a6]/30 relative z-10">
                    <i class="fa-solid fa-barcode"></i>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Prêt pour le scan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto leading-relaxed">
                    Pointez votre <strong>douchette laser</strong> sur le code-barres de l'article pour le récupérer instantanément.
                </p>
            </div>

            <!-- CHAMP RÉCEPTEUR SCAN (AUTOFOCUSÉ) -->
            <div class="max-w-xs mx-auto">
                <div class="relative">
                    <input type="text" id="scan-capture-input" autofocus autocomplete="off"
                           placeholder="Attente du signal douchette..."
                           class="w-full text-center px-4 py-3 bg-slate-50 border-2 border-[#0056a6]/30 rounded-2xl text-xs font-mono font-bold text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-4 focus:ring-[#0056a6]/10 focus:outline-none transition">
                </div>
                <p class="text-[10px] text-slate-400 mt-2 flex items-center justify-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Lecteur connecté et prêt</span>
                </p>
            </div>

            <!-- SÉPARATEUR OU -->
            <div class="relative flex py-2 items-center">
                <div class="flex-grow border-t border-slate-100"></div>
                <span class="flex-shrink mx-4 text-[11px] font-bold text-slate-400 uppercase tracking-wider">OU</span>
                <div class="flex-grow border-t border-slate-100"></div>
            </div>

            <!-- BOUTON ENREGISTREMENT MANUEL (SANS CODE-BARRES) -->
            <div>
                <button type="button" onclick="startManualRegistration()"
                        class="w-full py-3.5 px-5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition flex items-center justify-center gap-2.5 cursor-pointer group">
                    <i class="fa-solid fa-wand-magic-sparkles text-[#0056a6] group-hover:rotate-12 transition"></i>
                    <span>Cet article n'a pas de code-barres (Enregistrement manuel)</span>
                </button>
                <p class="text-[10px] text-slate-400 mt-1.5">
                    Un code-barres et son image seront automatiquement générés en arrière-plan.
                </p>
            </div>

        </div>

    </div>


    <!-- ========================================================================= -->
    <!-- ÉTAPE 2 : FORMULAIRE COMPLET D'ENREGISTREMENT DU PRODUIT                -->
    <!-- ========================================================================= -->
    <div id="step-form" class="hidden w-full lg:w-[80%] mx-auto space-y-6 transition-all duration-300">

        <!-- EN-TÊTE FORMULAIRE -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1 font-medium">
                    <button type="button" onclick="backToScanStep()" class="hover:text-[#0056a6] transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Re-scanner
                    </button>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    <span class="text-slate-700 font-bold">Fiche produit</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight" id="form-header-title">Compléter les informations du produit</h2>
                <p class="text-xs text-slate-500" id="form-header-subtitle">Renseignez le nom, prix de vente et stock initial.</p>
            </div>

            <button type="button" onclick="backToScanStep()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer self-start sm:self-auto">
                <i class="fa-solid fa-barcode text-xs text-[#0056a6]"></i>
                <span>Changer / Re-scanner</span>
            </button>
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

        <!-- FORMULAIRE (LAYOUT 2 COLONNES : IMAGE À GAUCHE / CHAMPS À DROITE) -->
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- CHAMP CODE-BARRES CACHÉ OU VISIBLE SELON LE MODE -->
            <input type="hidden" name="barcode" id="form-barcode-input" value="{{ old('barcode') }}">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- COLONNE GAUCHE (IMAGE & STATUT CODE-BARRES) -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- CARTE IMAGE DU PRODUIT -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs text-center flex flex-col items-center">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-4 block">Image du produit</span>
                        
                        <!-- CONTENEUR APERÇU IMAGE -->
                        <div class="relative w-44 h-44 mb-4 group">
                            <div id="product-image-preview" class="w-full h-full rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 flex flex-col items-center justify-center overflow-hidden transition group-hover:border-[#0056a6]">
                                <i id="product-placeholder-icon" class="fa-solid fa-box-open text-4xl text-slate-400 mb-1"></i>
                                <span id="product-placeholder-text" class="text-[10px] text-slate-400 font-medium">Aucune image</span>
                                <img id="product-img" src="" alt="Aperçu" class="w-full h-full object-cover hidden">
                            </div>

                            <!-- BOUTON D'ACTION SUR L'IMAGE -->
                            <label for="product-image-input" class="absolute -bottom-2 -right-2 w-9 h-9 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl flex items-center justify-center shadow-md cursor-pointer transition" title="Choisir une image">
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
                            Choisir une image
                        </button>
                    </div>

                    <!-- CARTE BADGE STATUT DU CODE-BARRES -->
                    <div id="barcode-mode-badge-card" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                            <i class="fa-solid fa-barcode text-[#0056a6]"></i>
                            <span id="barcode-badge-title">Code-barres Scanné</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                            <span id="barcode-badge-value" class="font-mono font-black text-sm text-slate-900 tracking-wider">--</span>
                            <span id="barcode-badge-type" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">Scanné</span>
                        </div>
                        <p id="barcode-badge-hint" class="text-[11px] text-slate-400">
                            L'image vectorielle du code-barres sera automatiquement générée.
                        </p>
                    </div>

                </div>

                <!-- COLONNE DROITE (CHAMPS DE SAISIE DU FORMULAIRE) -->
                <div class="lg:col-span-8 space-y-6">

                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                        
                        <!-- SECTION 1 : CLASSIFICATION & NOM -->
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                                <i class="fa-solid fa-tag text-[#0056a6]"></i>
                                <span>Désignation & Classification</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Nom de l'article, catégorie et fournisseur</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            
                            <!-- NOM DU PRODUIT (OBLIGATOIRE) -->
                            <div class="sm:col-span-2">
                                <label for="product-name" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Nom du produit <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="fa-solid fa-basket-shopping absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                    <input type="text" name="name" id="product-name" value="{{ old('name') }}" required
                                           placeholder="Ex: Riz Parfumé Dinor 5kg, Eau Minérale Awa 1.5L..."
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
                                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
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
                                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">{{ old('description') }}</textarea>
                            </div>

                        </div>

                        <!-- SECTION 2 : PRIX & GESTION DU STOCK -->
                        <div class="border-t border-slate-100 pt-6">
                            <div class="border-b border-slate-100 pb-4 mb-5">
                                <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                                    <i class="fa-solid fa-coins text-[#0056a6]"></i>
                                    <span>Tarification & Stock Initial</span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Définissez le prix de vente et les quantités de stock</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                                
                                <!-- PRIX UNITAIRE (OBLIGATOIRE) -->
                                <div>
                                    <label for="sell_price" class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Prix Unitaire (Vente) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <i class="fa-solid fa-coins absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                        <input type="number" step="1" min="0" name="sell_price" id="sell_price" value="{{ old('sell_price') }}" required
                                               placeholder="Ex: 1500"
                                               class="w-full pl-9 pr-14 py-2.5 bg-slate-50 border @error('sell_price') border-rose-300 bg-rose-50/30 @else border-slate-200 @enderror rounded-xl text-xs font-black text-slate-900 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                        <span class="absolute right-3 top-2.5 text-[11px] font-bold text-slate-400">FCFA</span>
                                    </div>
                                    @error('sell_price')
                                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- STOCK INITIAL -->
                                <div>
                                    <label for="stock_quantity" class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Stock Initial
                                    </label>
                                    <div class="relative">
                                        <i class="fa-solid fa-cubes absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                        <input type="number" step="1" min="0" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                                               placeholder="Ex: 50"
                                               class="w-full pl-9 pr-14 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                        <span class="absolute right-3 top-2.5 text-[11px] font-bold text-slate-400">unités</span>
                                    </div>
                                </div>

                                <!-- SEUIL D'ALERTE -->
                                <div>
                                    <label for="alert_threshold" class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Seuil d'alerte rupture
                                    </label>
                                    <div class="relative">
                                        <i class="fa-solid fa-triangle-exclamation absolute left-3.5 top-3 text-amber-500 text-xs"></i>
                                        <input type="number" step="1" min="0" name="alert_threshold" id="alert_threshold" value="{{ old('alert_threshold', 5) }}"
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
                            <span>Enregistrer le produit</span>
                        </button>
                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

<script>
    let currentMode = 'scan'; // 'scan' ou 'manual'

    /**
     * Initialisation du récepteur de scan
     */
    document.addEventListener('DOMContentLoaded', function() {
        const scanInput = document.getElementById('scan-capture-input');
        
        // Si la page est rechargée avec d'anciennes valeurs d'erreur
        @if(old('name') || $errors->any())
            const oldBarcode = '{{ old('barcode') }}';
            if (oldBarcode) {
                proceedToForm(oldBarcode, 'scan');
            } else {
                startManualRegistration();
            }
            return;
        @endif

        // Autofocus sur le champ récepteur
        if (scanInput) {
            scanInput.focus();

            // Détection du scan à la douchette (généralement suivi de Enter)
            scanInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const code = this.value.trim();
                    if (code.length > 0) {
                        proceedToForm(code, 'scan');
                    }
                }
            });

            // Détection si la douchette tape très vite
            let scanTimer;
            scanInput.addEventListener('input', function() {
                clearTimeout(scanTimer);
                const code = this.value.trim();
                if (code.length >= 8) {
                    scanTimer = setTimeout(() => {
                        proceedToForm(code, 'scan');
                    }, 250);
                }
            });
        }
    });

    /**
     * Bascule vers l'étape formulaire avec un code scanné
     */
    function proceedToForm(barcode, mode) {
        currentMode = mode;
        const stepScan = document.getElementById('step-scan');
        const stepForm = document.getElementById('step-form');
        const barcodeInput = document.getElementById('form-barcode-input');
        const badgeValue = document.getElementById('barcode-badge-value');
        const badgeType = document.getElementById('barcode-badge-type');
        const badgeTitle = document.getElementById('barcode-badge-title');
        const badgeHint = document.getElementById('barcode-badge-hint');
        const nameInput = document.getElementById('product-name');

        if (barcodeInput) barcodeInput.value = barcode || '';
        
        if (badgeValue) badgeValue.textContent = barcode || 'Attribution auto';
        if (badgeType) {
            badgeType.textContent = mode === 'scan' ? 'Scanné' : 'Généré Auto';
            badgeType.className = mode === 'scan' ? 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800' : 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-[#0056a6]';
        }
        if (badgeTitle) badgeTitle.textContent = mode === 'scan' ? 'Code-barres Scanné' : 'Code-barres Automatique';
        if (badgeHint) badgeHint.textContent = mode === 'scan' ? 'Code d\'usine scanné à la douchette.' : 'Code unique et étiquette générés automatiquement à l\'enregistrement.';

        if (stepScan) stepScan.classList.add('hidden');
        if (stepForm) stepForm.classList.remove('hidden');

        // Focus immédiat sur le nom du produit
        if (nameInput) {
            setTimeout(() => nameInput.focus(), 100);
        }
    }

    /**
     * Mode manuel : aucun code requis, génération automatique en arrière-plan
     */
    function startManualRegistration() {
        // En mode manuel, on vide le champ barcode (il sera généré par le serveur ou un code pré-généré)
        proceedToForm('', 'manual');
    }

    /**
     * Retour à l'étape 1 de scan
     */
    function backToScanStep() {
        const stepScan = document.getElementById('step-scan');
        const stepForm = document.getElementById('step-form');
        const scanInput = document.getElementById('scan-capture-input');

        if (stepForm) stepForm.classList.add('hidden');
        if (stepScan) stepScan.classList.remove('hidden');

        if (scanInput) {
            scanInput.value = '';
            scanInput.focus();
        }
    }

    /**
     * Prévisualisation image produit
     */
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
</script>
@endsection
