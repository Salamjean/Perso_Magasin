@extends('layouts.app')

@section('title', 'Modifier le Fournisseur : ' . $supplier->company_name)
@section('page-title', 'Modifier le Fournisseur')

@section('content')
<div class="w-full lg:w-[80%] mx-auto space-y-6">

    <!-- EN-TÊTE DE LA PAGE -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-xl shrink-0">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs font-medium text-slate-400">
                        <a href="{{ route('admin.suppliers.index') }}" class="hover:text-[#0056a6] transition">Fournisseurs</a> /
                    </span>
                    <span class="text-xs font-bold text-slate-600">Édition</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Modifier : {{ $supplier->company_name }}</h2>
            </div>
        </div>

        <a href="{{ route('admin.suppliers.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Retour aux fournisseurs</span>
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

    <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- GRILLE EN 2 COLONNES BIEN STYLISÉE -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- COLONNE GAUCHE : IDENTITÉ & COORDONNÉES -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Identité & Coordonnées</h3>
                        <p class="text-[11px] text-slate-400">Informations de la société et contact direct</p>
                    </div>
                </div>

                <!-- NOM DE L'ENTREPRISE -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Nom de l'entreprise / Société <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-building absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" required 
                               placeholder="Ex: Solibra CI, Brassivoire, DistriFood..."
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border @error('company_name') border-rose-300 bg-rose-50/30 @else border-slate-200 @enderror rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                    </div>
                    @error('company_name')
                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NOM DU CONTACT -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Nom du Responsable Commercial / Contact
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-user-tie absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" 
                               placeholder="Ex: M. Kouadio Michel"
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                    </div>
                </div>

                <!-- TÉLÉPHONE DIRECT -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Numéro de Téléphone <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" required
                               placeholder="Ex: +225 07 00 00 00 00 / 27 20..."
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border @error('phone') border-rose-300 bg-rose-50/30 @else border-slate-200 @enderror rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                    </div>
                    @error('phone')
                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ADRESSE EMAIL -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Adresse E-mail <span class="text-slate-400 font-normal lowercase">(optionnelle)</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}" 
                               placeholder="Ex: commandes@fournisseur.ci"
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                    </div>
                </div>

            </div>

            <!-- COLONNE DROITE : LOCALISATION, FISCALITÉ & CONDITIONS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Localisation & Conditions</h3>
                        <p class="text-[11px] text-slate-400">Emplacement géographique, fiscalité et notes</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- VILLE -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                            Ville / Région
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-city absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                            <input type="text" name="city" value="{{ old('city', $supplier->city) }}" 
                                   placeholder="Ex: Abidjan"
                                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- NUMÉRO FISCAL -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                            N° Fiscal (CC / IFU)
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-file-invoice absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                            <input type="text" name="tax_number" value="{{ old('tax_number', $supplier->tax_number) }}" 
                                   placeholder="Ex: CI-ABJ-01..."
                                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- ADRESSE COMPLÈTE -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Adresse Géographique / Entrepôt
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-map-pin absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" name="address" value="{{ old('address', $supplier->address) }}" 
                               placeholder="Ex: Zone industrielle de Vridi, Rue des Brasseries..."
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                    </div>
                </div>

                <!-- CONDITIONS & NOTES -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Conditions Commerciales & Notes <span class="text-slate-400 font-normal lowercase">(optionnelle)</span>
                    </label>
                    <textarea name="notes" rows="3" 
                              placeholder="Ex: Paiement à 30 jours, commande minimum 100 000 FCFA, livraison sous 48h..."
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">{{ old('notes', $supplier->notes) }}</textarea>
                </div>

            </div>

        </div>

        <!-- BARRE DE BOUTONS D'ACTION CENTRÉE / ALIGNÉE -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-end gap-3">
            <a href="{{ route('admin.suppliers.index') }}" 
               class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                Annuler
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#0056a6] hover:bg-[#004485] text-white font-bold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-check text-xs"></i>
                <span>Enregistrer les modifications</span>
            </button>
        </div>
    </form>

</div>
@endsection
