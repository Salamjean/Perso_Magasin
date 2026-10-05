@extends('layouts.app')

@section('title', 'Nouveau Rayon / Catégorie')
@section('page_title', 'Ajouter un Rayon')

@section('content')
<div class="w-full lg:w-[80%] mx-auto space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-xl shrink-0">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">
                        <a href="{{ route('admin.categories.index') }}" class="hover:text-[#0056a6] transition">Rayons & Catégories</a> /
                    </span>
                    <span class="text-xs font-bold text-slate-600">Nouveau</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Ajouter un Rayon & Catégorie</h2>
            </div>
        </div>

        <a href="{{ route('admin.categories.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux rayons</span>
        </a>
    </div>

    <!-- FORMULAIRE PRINCIPAL -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        
        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>Veuillez corriger les erreurs suivantes :</span>
                </div>
                @foreach($errors->all() as $err)
                    <p class="pl-5">• {{ $err }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- COLONNE GAUCHE : APERÇU EN DIRECT DU BADGE RAYON -->
                <div class="lg:col-span-1 bg-slate-50/80 p-5 rounded-2xl border border-slate-200/70 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-3">Aperçu Visuel en Caisse</span>
                        
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3">
                            <div id="preview-color-box" class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-base shadow-xs shrink-0 transition-colors"
                                 style="background-color: {{ old('color', '#0056a6') }}">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                            <div class="min-w-0">
                                <p id="preview-name" class="text-xs font-black text-slate-900 truncate">
                                    {{ old('name', 'Nom du rayon') }}
                                </p>
                                <span class="text-[10px] text-slate-400 font-medium">Affichage tactile caisse</span>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-500 mt-4 leading-relaxed">
                            La couleur sélectionnée permet aux caissiers d'identifier immédiatement la famille de produits lors de la vente rapide sur l'écran tactile.
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-200/60">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Nuances rapides :</span>
                        <div class="flex items-center gap-2 flex-wrap">
                            @php
                                $presetColors = ['#0056a6', '#0284c7', '#0d9488', '#16a34a', '#d97706', '#dc2626', '#7c3aed', '#db2777', '#475569'];
                            @endphp
                            @foreach($presetColors as $colorHex)
                                <button type="button" 
                                        onclick="setCategoryColor('{{ $colorHex }}')"
                                        class="w-6 h-6 rounded-lg shadow-2xs border border-white hover:scale-110 transition cursor-pointer"
                                        style="background-color: {{ $colorHex }}"
                                        title="{{ $colorHex }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- COLONNE DROITE : CHAMPS DU FORMULAIRE -->
                <div class="lg:col-span-2 space-y-5">
                    
                    <!-- NOM DU RAYON -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                            Nom du Rayon / Catégorie <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-tag absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                            <input type="text" name="name" id="category-name-input" value="{{ old('name') }}" required 
                                   placeholder="Ex: Boissons & Jus, Épicerie Salée, Boucherie..."
                                   oninput="updatePreviewName(this.value)"
                                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border @error('name') border-rose-300 bg-rose-50/30 @else border-slate-200 @enderror rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                        </div>
                        @error('name')
                            <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- COULEUR PERSONNALISÉE -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                            Code Couleur Hexadécimal
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="category-color-picker" name="color" value="{{ old('color', '#0056a6') }}"
                                   oninput="updatePreviewColor(this.value)"
                                   class="w-12 h-10 p-1 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer">
                            <input type="text" id="category-color-text" value="{{ old('color', '#0056a6') }}"
                                   oninput="updatePreviewColor(this.value)"
                                   class="w-32 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-700 uppercase focus:bg-white focus:border-[#0056a6] focus:outline-none">
                            <span class="text-[11px] text-slate-400">Choisissez une couleur ou saisissez un code HEX</span>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                            Description du Rayon <span class="text-slate-400 font-normal lowercase">(optionnelle)</span>
                        </label>
                        <textarea name="description" rows="4" 
                                  placeholder="Brève description des types d'articles regroupés dans ce rayon..."
                                  class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">{{ old('description') }}</textarea>
                    </div>

                </div>

            </div>

            <!-- BOUTONS D'ACTION EN BAS -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.categories.index') }}" 
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-[#0056a6] hover:bg-[#004485] text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>Enregistrer la catégorie</span>
                </button>
            </div>
        </form>

    </div>

</div>

<script>
    function updatePreviewName(value) {
        const preview = document.getElementById('preview-name');
        if (preview) {
            preview.textContent = value.trim() ? value : 'Nom du rayon';
        }
    }

    function updatePreviewColor(color) {
        const box = document.getElementById('preview-color-box');
        const picker = document.getElementById('category-color-picker');
        const text = document.getElementById('category-color-text');
        
        if (box) box.style.backgroundColor = color;
        if (picker && picker.value !== color) picker.value = color;
        if (text && text.value !== color) text.value = color;
    }

    function setCategoryColor(color) {
        updatePreviewColor(color);
    }
</script>
@endsection
