@extends('layouts.app')

@section('title', 'Paramètres Généraux')
@section('page_title', 'Configuration & Identité du Magasin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#0056a6] flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-medium text-slate-400">Système & Administration /</span>
                    <span class="text-xs font-bold text-slate-600">Configuration</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Paramètres Généraux du Magasin</h2>
                <p class="text-xs text-slate-500 mt-0.5">Gérez l'identité visuelle, les coordonnées, les mentions de facturation et les règles globales</p>
            </div>
        </div>

        <!-- RACCOURCI PROFIL & SÉCURITÉ -->
        <a href="{{ route('profile') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 hover:bg-amber-50 hover:text-amber-800 hover:border-amber-200 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold transition shadow-2xs self-start sm:self-auto shrink-0">
            <i class="fa-solid fa-key text-amber-500 text-xs"></i>
            <span>Modifier mon mot de passe</span>
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

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. IDENTITÉ VISUELLE & LOGO -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                    <i class="fa-solid fa-image"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Identité Visuelle & Logo</h3>
                    <p class="text-[11px] text-slate-400">Logo officiel affiché sur l'application, les tickets et les reçus</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pt-2">
                
                <!-- APERÇU DU LOGO ACTUEL -->
                <div class="flex flex-col items-center gap-2 shrink-0">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Aperçu Actuel</span>
                    <div class="w-32 h-32 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center p-2.5 overflow-hidden shadow-2xs relative group">
                        @php
                            $hasCustomLogo = !empty($settings['store_logo']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['store_logo']);
                            $currentLogoUrl = $hasCustomLogo 
                                ? asset('storage/' . $settings['store_logo']) 
                                : asset('images/logo.png');
                        @endphp
                        <img id="logo_preview" src="{{ $currentLogoUrl }}" alt="Logo du magasin" class="max-h-full max-w-full object-contain transition-transform group-hover:scale-105">
                    </div>

                    @if($hasCustomLogo)
                        <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 cursor-pointer font-semibold mt-1">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                            <span>Supprimer le logo</span>
                        </label>
                    @endif
                </div>

                <!-- ZONE D'UPLOAD -->
                <div class="flex-1 w-full space-y-3">
                    <label class="block text-xs font-bold text-slate-700">
                        Téléverser un nouveau logo <span class="text-[10px] text-slate-400 font-normal">(PNG, JPG, SVG, WebP - max 3 Mo)</span>
                    </label>

                    <div class="relative border-2 border-dashed border-slate-300 hover:border-[#0056a6] rounded-2xl p-6 bg-slate-50/60 hover:bg-blue-50/30 transition-all flex flex-col items-center justify-center text-center cursor-pointer group">
                        <input type="file" name="store_logo" id="logo_input" accept="image/*" onchange="previewLogoImage(this)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-500 flex items-center justify-center mb-2 group-hover:scale-110 group-hover:text-[#0056a6] group-hover:border-blue-200 transition-all shadow-2xs">
                            <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-700 group-hover:text-[#0056a6] transition-colors">
                            Cliquez ici ou déposez votre fichier image
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Recommandé : image sur fond transparent pour un rendu optimal
                        </p>
                    </div>

                    <div id="file_name_display" class="hidden text-xs text-emerald-700 font-bold bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 flex items-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span id="file_name_text">Image sélectionnée</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- 2. COORDONNÉES & CONTACTS -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Coordonnées de l'Établissement</h3>
                    <p class="text-[11px] text-slate-400">Informations imprimées sur les tickets et documents de vente</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- NOM DU MAGASIN -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nom officiel de l'établissement <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-shop"></i>
                        </span>
                        <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name']) }}" required
                            placeholder="Ex: Supermarché GestMagasin Abidjan"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                    </div>
                </div>

                <!-- TÉLÉPHONE -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Numéro de téléphone principal / WhatsApp
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-phone"></i>
                        </span>
                        <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store_phone']) }}"
                            placeholder="Ex: +225 07 00 00 00 00"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                    </div>
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Adresse e-mail de contact
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" name="store_email" value="{{ old('store_email', $settings['store_email']) }}"
                            placeholder="Ex: contact@supermarche.ci"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                    </div>
                </div>

                <!-- ADRESSE PHYSIQUE -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Adresse géographique / Localisation
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <input type="text" name="store_address" value="{{ old('store_address', $settings['store_address']) }}"
                            placeholder="Ex: Cocody Angré 8ème Tranche, Abidjan"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. PARAMÈTRES MONÉTAIRES & STOCKS -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Devise & Règles de Stock</h3>
                    <p class="text-[11px] text-slate-400">Unité monétaire et seuils automatiques</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- DEVISE -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Devise monétaire d'affichage <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="currency" value="{{ old('currency', $settings['currency']) }}" required
                        placeholder="Ex: FCFA, EUR, USD"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                </div>

                <!-- SEUIL ALERTE GLOBAL -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Seuil alerte rupture par défaut <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" min="1" name="low_stock_global_threshold" value="{{ old('low_stock_global_threshold', $settings['low_stock_global_threshold']) }}" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400 text-[11px]">
                            unités
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. PERSONNALISATION DU TICKET DE CAISSE -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Personnalisation des Reçus / Tickets</h3>
                    <p class="text-[11px] text-slate-400">Mentions de courtoisie et remerciements en bas de ticket</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Message de pied de page sur ticket de caisse
                </label>
                <textarea name="receipt_footer" rows="2" placeholder="Ex: Les marchandises vendues ne sont ni reprises ni échangées. Merci pour votre fidélité !"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition leading-relaxed">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
            </div>
        </div>

        <!-- BOUTON D'ENREGISTREMENT -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" 
                    class="px-7 py-3 rounded-xl bg-[#0056a6] hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-900/20 transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                <span>Enregistrer les Paramètres & le Logo</span>
            </button>
        </div>

    </form>

</div>
@endsection

@section('scripts')
<script>
    function previewLogoImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.getElementById('logo_preview');
                if (img) {
                    img.src = e.target.result;
                }
            };

            reader.readAsDataURL(file);

            const nameDisplay = document.getElementById('file_name_display');
            const nameText = document.getElementById('file_name_text');
            if (nameDisplay && nameText) {
                nameText.textContent = file.name + ' (' + (Math.round(file.size / 1024)) + ' Ko)';
                nameDisplay.classList.remove('hidden');
            }
        }
    }
</script>
@endsection
