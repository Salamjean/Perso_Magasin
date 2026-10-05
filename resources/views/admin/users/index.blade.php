@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')
@section('page_title', 'Gestion des Utilisateurs')

@section('content')
<div class="space-y-6">

    <!-- KPI STATS SUMMARY (EMPLOYÉS UNIQUEMENT - DESIGN ÉPURÉ SANS DÉGRADÉ) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- TOTAL EMPLOYÉS -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Employés</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-lg">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- COMPTES ACTIFS -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Comptes Actifs</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['active'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <!-- COMPTES BLOQUÉS -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Comptes Bloqués</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['inactive'] }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-lock"></i>
            </div>
        </div>
    </div>

    <!-- BARRE DE RECHERCHE, FILTRES & BOUTON D'AJOUT -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        
        <!-- FORMULAIRE DE FILTRE -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            
            <!-- RECHERCHE TEXTE -->
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Rechercher par nom, email, téléphone..."
                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#0056a6] focus:ring-1 focus:ring-[#0056a6] focus:outline-none transition">
            </div>

            <!-- FILTRE PAR RÔLE (EMPLOYÉS SEULEMENT) -->
            <div class="w-full sm:w-auto">
                <select name="role" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:ring-1 focus:ring-[#0056a6] focus:outline-none transition">
                    <option value="">Tous les postes</option>
                    <option value="caissier" {{ request('role') == 'caissier' ? 'selected' : '' }}>Caissier</option>
                    <option value="magasinier" {{ request('role') == 'magasinier' ? 'selected' : '' }}>Magasinier</option>
                    <option value="livreur" {{ request('role') == 'livreur' ? 'selected' : '' }}>Livreur</option>
                </select>
            </div>

            <!-- FILTRE PAR STATUT -->
            <div class="w-full sm:w-auto">
                <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:ring-1 focus:ring-[#0056a6] focus:outline-none transition">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Bloqué</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filtrer</span>
                </button>
                @if(request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Réinitialiser les filtres">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- BOUTON NOUVEL UTILISATEUR -->
        <a href="{{ route('admin.users.create') }}" 
            class="px-4 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 shrink-0">
            <i class="fa-solid fa-user-plus"></i>
            <span>Nouvel Utilisateur</span>
        </a>
    </div>

    <!-- TABLE DES UTILISATEURS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Utilisateur</th>
                        <th class="py-3.5 px-5 text-center">Rôle</th>
                        <th class="py-3.5 px-5 text-center">Contact</th>
                        <th class="py-3.5 px-5 text-center">Statut</th>
                        <th class="py-3.5 px-5 text-center">Créé le</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/60 transition {{ $user->status === 'inactive' ? 'bg-slate-50/40 opacity-80' : '' }}">
                            
                            <!-- UTILISATEUR (AVATAR + NOM + CONTACT) -->
                            <td class="py-3.5 px-5 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 text-[#0056a6] font-bold text-xs flex items-center justify-center overflow-hidden shrink-0">
                                        @if($user->photo)
                                            <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->firstname ?? '', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $user) }}" class="font-bold text-slate-900 hover:text-[#0056a6] transition block">
                                            {{ $user->full_name }}
                                        </a>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $user->email ?? $user->phone }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- RÔLE -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($user->role === 'magasinier')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-boxes-stacked text-[10px] text-amber-600"></i>
                                        Magasinier
                                    </span>
                                @elseif($user->role === 'caissier')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-[#0056a6] border border-blue-200">
                                        <i class="fa-solid fa-cash-register text-[10px] text-[#0056a6]"></i>
                                        Caissier
                                    </span>
                                @elseif($user->role === 'livreur')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                        <i class="fa-solid fa-motorcycle text-[10px] text-teal-600"></i>
                                        Livreur
                                    </span>
                                @endif
                            </td>

                            <!-- CONTACT -->
                            <td class="py-3.5 px-5 text-center align-middle text-slate-600">
                                <div class="font-medium text-slate-800 font-mono">{{ $user->phone ?? '—' }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[160px] mx-auto">{{ $user->address ?? 'Aucune adresse' }}</div>
                            </td>

                            <!-- STATUT (ACTIF OU BLOQUÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Bloqué
                                    </span>
                                @endif
                            </td>

                            <!-- DATE DE CRÉATION -->
                            <td class="py-3.5 px-5 text-center align-middle text-slate-500 text-[11px] font-mono">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>

                            <!-- ACTIONS (CONSULTER, MODIFIER, BLOQUER/DÉBLOQUER - AUCUNE SUPPRESSION) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    
                                    <!-- VOIR DÉTAILS -->
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                                        title="Voir détails">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    <!-- MODIFIER -->
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                        class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                                        title="Modifier">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>

                                    <!-- BLOQUER / DÉBLOQUER AVEC SWEETALERT2 -->
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        
                                        @if($user->status === 'active')
                                            <button type="button" 
                                                onclick="confirmAction(this.closest('form'), {
                                                    title: 'Bloquer cet utilisateur ?',
                                                    text: 'L\'employé {{ addslashes($user->full_name) }} ne pourra plus se connecter.',
                                                    confirmText: 'Oui, bloquer',
                                                    confirmColor: '#e11d48',
                                                    icon: 'warning'
                                                })"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[11px] flex items-center gap-1.5 transition cursor-pointer" 
                                                title="Bloquer l'utilisateur">
                                                <i class="fa-solid fa-user-slash text-xs"></i>
                                                <span>Bloquer</span>
                                            </button>
                                        @else
                                            <button type="button" 
                                                onclick="confirmAction(this.closest('form'), {
                                                    title: 'Débloquer cet utilisateur ?',
                                                    text: 'L\'employé {{ addslashes($user->full_name) }} aura de nouveau accès à son compte.',
                                                    confirmText: 'Oui, débloquer',
                                                    confirmColor: '#0056a6',
                                                    icon: 'question'
                                                })"
                                                class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-[11px] flex items-center gap-1.5 transition cursor-pointer" 
                                                title="Débloquer l'utilisateur">
                                                <i class="fa-solid fa-user-check text-xs"></i>
                                                <span>Débloquer</span>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-users-slash"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Aucun utilisateur trouvé</p>
                                <p class="text-[11px] text-slate-400 mt-1">Modifiez vos critères de recherche ou ajoutez un nouvel utilisateur.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

