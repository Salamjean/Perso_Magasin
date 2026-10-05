@extends('layouts.app')

@section('title', 'Modifier l\'Employé')
@section('page_title', 'Gestion des Utilisateurs')

@section('content')
<div class="w-[80%] mx-auto space-y-6">

    <!-- HEADER / BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1 font-medium">
                <a href="{{ route('admin.users.index') }}" class="hover:text-[#0056a6] transition">Utilisateurs</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-700 font-bold">Édition</span>
            </div>
            <h2 class="text-lg font-black text-slate-900 tracking-tight">Modifier le compte : {{ $user->full_name }}</h2>
            <p class="text-xs text-slate-500">Mettez à jour les coordonnées, le rôle ou réinitialisez le mot de passe.</p>
        </div>

        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer self-start sm:self-auto">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Retour à la liste</span>
        </a>
    </div>

    <!-- FORMULAIRE PRINCIPAL (LAYOUT 2 COLONNES : PHOTO À GAUCHE / CHAMPS À DROITE) -->
    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- COLONNE GAUCHE (PHOTO DE PROFIL & INFOS COMPTE) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- CARTE AVATAR / PHOTO -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs text-center flex flex-col items-center">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-4 block">Photo de profil</span>
                    
                    <!-- CONTENEUR APERÇU PHOTO -->
                    <div class="relative w-36 h-36 mb-4 group">
                        <div id="avatar-preview" class="w-full h-full rounded-2xl bg-slate-100 border-2 border-slate-200 flex flex-col items-center justify-center overflow-hidden transition group-hover:border-[#0056a6]">
                            @if($user->photo)
                                <img id="avatar-img" src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                <i id="avatar-placeholder-icon" class="fa-solid fa-user text-4xl text-slate-400 mb-1 hidden"></i>
                                <span id="avatar-placeholder-text" class="text-[10px] text-slate-400 font-medium hidden">Aucune photo</span>
                            @else
                                <i id="avatar-placeholder-icon" class="fa-solid fa-user text-4xl text-slate-400 mb-1"></i>
                                <span id="avatar-placeholder-text" class="text-[10px] text-slate-400 font-medium">Aucune photo</span>
                                <img id="avatar-img" src="" alt="Aperçu" class="w-full h-full object-cover hidden">
                            @endif
                        </div>

                        <!-- BOUTON D'ACTION SUR LA PHOTO -->
                        <label for="photo-input" class="absolute -bottom-2 -right-2 w-9 h-9 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl flex items-center justify-center shadow-md cursor-pointer transition">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </label>
                    </div>

                    <input type="file" name="photo" id="photo-input" accept="image/*" class="hidden" onchange="previewAvatar(event)">

                    <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
                        Formats acceptés : <strong class="text-slate-700">JPG, PNG</strong><br>
                        Taille max : <strong class="text-slate-700">2 Mo</strong>
                    </p>

                    <button type="button" onclick="document.getElementById('photo-input').click()"
                            class="mt-4 w-full py-2 px-3 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition">
                        Changer la photo
                    </button>
                </div>

                <!-- CARTE STATUT ACTUEL DU COMPTE -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Statut du compte</span>
                        @if($user->isActive())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Actif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Bloqué
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="font-medium">Créé le</span>
                        <span class="font-bold text-slate-700">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

            </div>

            <!-- COLONNE DROITE (CHAMPS DE SAISIE DU FORMULAIRE) -->
            <div class="lg:col-span-8 space-y-6">

                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                    
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-user-pen text-[#0056a6]"></i>
                            <span>Édition des coordonnées & Rôle</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Modifiez les informations nécessaires puis cliquez sur enregistrer</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        
                        <!-- NOM DE FAMILLE (OBLIGATOIRE) -->
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nom de famille <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-user text-xs"></i>
                                </span>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                            </div>
                            @error('name')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- PRÉNOM(S) (OPTIONNEL) -->
                        <div>
                            <label for="firstname" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Prénom(s)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-user-tag text-xs"></i>
                                </span>
                                <input type="text" name="firstname" id="firstname" value="{{ old('firstname', $user->firstname) }}"
                                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                            </div>
                            @error('firstname')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NUMÉRO DE TÉLÉPHONE (OBLIGATOIRE - IDENTIFIANT DE CONNEXION) -->
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Téléphone (Identifiant) <span class="text-rose-500">*</span></span>
                                <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">Obligatoire</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                </span>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" required
                                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition font-mono">
                            </div>
                            @error('phone')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- EMAIL (OPTIONNEL) -->
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Adresse Email</span>
                                <span class="text-[10px] text-slate-400 font-semibold">Optionnel</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-envelope text-xs"></i>
                                </span>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" placeholder="nom@exemple.com"
                                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                            </div>
                            @error('email')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- RÔLE DE L'EMPLOYÉ (SANS ADMINISTRATEUR) -->
                        <div>
                            <label for="role" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Rôle attribué <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-briefcase text-xs"></i>
                                </span>
                                <select name="role" id="role" required
                                        class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition appearance-none cursor-pointer">
                                    <option value="caissier" {{ old('role', $user->role) == 'caissier' ? 'selected' : '' }}>Caissier — (Ventes & Caisse TPV)</option>
                                    <option value="magasinier" {{ old('role', $user->role) == 'magasinier' ? 'selected' : '' }}>Magasinier — (Gestion Stock & Rayons)</option>
                                    <option value="livreur" {{ old('role', $user->role) == 'livreur' ? 'selected' : '' }}>Livreur — (Expéditions & Livraisons)</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </span>
                            </div>
                            @error('role')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- STATUT DU COMPTE -->
                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Statut du compte <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-toggle-on text-xs"></i>
                                </span>
                                <select name="status" id="status" required
                                        class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition appearance-none cursor-pointer">
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Actif (Connexion autorisée)</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Bloqué (Accès refusé)</option>
                                </select>
                                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </span>
                            </div>
                            @error('status')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- MODIFIER LE MOT DE PASSE (OPTIONNEL EN ÉDITION) -->
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Nouveau mot de passe</span>
                                <span class="text-[10px] text-slate-400 font-semibold">Optionnel</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-key text-xs"></i>
                                </span>
                                <input type="password" name="password" id="password" placeholder="Nouveau mot de passe"
                                       class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                <button type="button" onclick="togglePasswordVisibility('password', 'password-eye')"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none">
                                    <i id="password-eye" class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CONFIRMATION DU NOUVEAU MOT DE PASSE -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Confirmer le mot de passe
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-lock-open text-xs"></i>
                                </span>
                                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Répéter le mot de passe"
                                       class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'password-confirm-eye')"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none">
                                    <i id="password-confirm-eye" class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ADRESSE GÉOGRAPHIQUE (OPTIONNEL) -->
                        <div class="sm:col-span-2">
                            <label for="address" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Adresse géographique / Domicile</span>
                                <span class="text-[10px] text-slate-400 font-semibold">Optionnel</span>
                            </label>
                            <div class="relative">
                                <span class="absolute top-3 left-3.5 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-location-dot text-xs"></i>
                                </span>
                                <textarea name="address" id="address" rows="2" placeholder="Commune, quartier, indications..."
                                          class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">{{ old('address', $user->address) }}</textarea>
                            </div>
                            @error('address')
                                <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <!-- BOUTONS D'ACTION SOUMISSION -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('admin.users.index') }}"
                           class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition cursor-pointer">
                            Annuler
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#0056a6] hover:bg-[#004485] text-white font-bold text-xs shadow-sm transition cursor-pointer">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>Enregistrer les modifications</span>
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
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatar-img');
                const placeholderIcon = document.getElementById('avatar-placeholder-icon');
                const placeholderText = document.getElementById('avatar-placeholder-text');

                img.src = e.target.result;
                img.classList.remove('hidden');
                if (placeholderIcon) placeholderIcon.classList.add('hidden');
                if (placeholderText) placeholderText.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
