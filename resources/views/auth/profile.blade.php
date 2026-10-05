@extends('layouts.app')

@section('title', 'Mon Profil & Sécurité')
@section('page_title', 'Mon Profil & Sécurité du Compte')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- EN-TÊTE DU PROFIL -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#0056a6] text-white flex items-center justify-center font-black text-xl shrink-0 shadow-md shadow-blue-900/20">
                {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->firstname ?? '', 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                        @if($user->role === 'admin') bg-purple-50 text-purple-700 border border-purple-200
                        @elseif($user->role === 'magasinier') bg-amber-50 text-amber-700 border border-amber-200
                        @elseif($user->role === 'caissier') bg-emerald-50 text-emerald-700 border border-emerald-200
                        @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                        {{ strtoupper($user->role) }}
                    </span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs text-slate-500 font-medium">Compte Actif</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">{{ $user->full_name }}</h2>
                <p class="text-xs text-slate-400 font-mono">{{ $user->email ?? $user->phone }}</p>
            </div>
        </div>

        @if($user->role === 'admin')
            <a href="{{ route('admin.settings.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition self-start sm:self-auto">
                <i class="fa-solid fa-sliders text-xs"></i>
                <span>Paramètres Magasin</span>
            </a>
        @endif
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

    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1 : INFORMATIONS PERSONNELLES -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Informations Personnelles</h3>
                    <p class="text-[11px] text-slate-400">Vos coordonnées et identifiants de compte</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- NOM DE FAMILLE -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nom de famille <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        placeholder="Ex: KOUASSI"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                </div>

                <!-- PRÉNOM(S) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Prénom(s)
                    </label>
                    <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}"
                        placeholder="Ex: Jean-Marc"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:ring-2 focus:ring-[#0056a6]/20 focus:outline-none transition">
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Adresse E-mail {{ $user->role === 'admin' ? '(Identifiant de connexion)' : '' }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            placeholder="admin@gestmagasin.com"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                    </div>
                </div>

                <!-- TÉLÉPHONE -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Numéro de téléphone
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-phone"></i>
                        </span>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            placeholder="Ex: +225 07 00 00 00 00"
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                    </div>
                </div>

                <!-- ADRESSE GÉOGRAPHIQUE -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Adresse géographique / Domicile
                    </label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}"
                        placeholder="Ex: Abidjan, Cocody"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
                </div>
            </div>
        </div>

        <!-- SECTION 2 : SÉCURITÉ & CHANGEMENT DE MOT DE PASSE -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Sécurité & Modification du Mot de Passe</h3>
                    <p class="text-[11px] text-slate-400">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- MOT DE PASSE ACTUEL -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Mot de passe actuel
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="current_password" id="current_password"
                            placeholder="••••••••"
                            class="w-full pl-9 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-amber-500 focus:outline-none transition">
                        <button type="button" onclick="togglePassVisibility('current_password', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- NOUVEAU MOT DE PASSE -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nouveau mot de passe
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" name="new_password" id="new_password"
                            placeholder="Min. 6 caractères"
                            class="w-full pl-9 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-amber-500 focus:outline-none transition">
                        <button type="button" onclick="togglePassVisibility('new_password', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- CONFIRMATION DU NOUVEAU MOT DE PASSE -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Confirmer le mot de passe
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-check-double"></i>
                        </span>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                            placeholder="Répétez le mot de passe"
                            class="w-full pl-9 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-amber-500 focus:outline-none transition">
                        <button type="button" onclick="togglePassVisibility('new_password_confirmation', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOUTON DE VALIDATION -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" 
                    class="px-7 py-3 rounded-xl bg-[#0056a6] hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-900/20 transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                <span>Enregistrer les Modifications du Profil</span>
            </button>
        </div>

    </form>

</div>
@endsection

@section('scripts')
<script>
    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-solid fa-eye-slash text-xs';
        } else {
            input.type = 'password';
            icon.className = 'fa-solid fa-eye text-xs';
        }
    }
</script>
@endsection
