@extends('layouts.app')

@section('title', 'Gestion des Fournisseurs')
@section('page-title', 'Fournisseurs & Partenaires Commerciaux')

@section('content')
<div class="space-y-6">

    <!-- EN-TÊTE PRINCIPALE & ACTIONS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        
        <!-- RECHERCHE & FILTRES -->
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Entreprise, contact, tel, ville..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-[#0056a6] focus:ring-1 focus:ring-[#0056a6] focus:outline-none transition">
            </div>

            <!-- FILTRE PAR STATUT -->
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-[#0056a6] focus:outline-none">
                <option value="" {{ request('status') == '' ? 'selected' : '' }}>Tous les statuts ({{ $stats['total'] }})</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs uniquement ({{ $stats['active'] }})</option>
                <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Masqués uniquement ({{ $stats['hidden'] }})</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.suppliers.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-medium">
                    Réinitialiser
                </a>
            @endif
        </form>

        <!-- BOUTON AJOUT -->
        <a href="{{ route('admin.suppliers.create') }}" class="px-4 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2 shrink-0 self-start md:self-auto">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Nouveau Fournisseur</span>
        </a>
    </div>

    <!-- TABLEAU DES FOURNISSEURS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Entreprise & Contact</th>
                        <th class="py-3.5 px-5">Coordonnées</th>
                        <th class="py-3.5 px-5">Ville & Adresse</th>
                        <th class="py-3.5 px-5 text-center">État</th>
                        <th class="py-3.5 px-5 text-center">Articles</th>
                        <th class="py-3.5 px-5 text-center">Commandes</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-50/60 transition {{ !$supplier->is_active ? 'bg-slate-50/40 opacity-75' : '' }}">
                            
                            <!-- ENTREPRISE & CONTACT -->
                            <td class="py-3.5 px-5 align-middle">
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" class="font-bold text-slate-900 hover:text-[#0056a6] transition text-sm block">
                                    {{ $supplier->company_name }}
                                </a>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    <i class="fa-solid fa-user-tie text-[10px] mr-1"></i>Contact: {{ $supplier->contact_name ?? 'Non spécifié' }}
                                </p>
                            </td>

                            <!-- COORDONNÉES -->
                            <td class="py-3.5 px-5 align-middle">
                                <div class="font-medium text-slate-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-phone text-[10px] text-slate-400"></i>
                                    {{ $supplier->phone ?? 'N/A' }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <i class="fa-solid fa-envelope text-[10px] text-slate-400"></i>
                                    {{ $supplier->email ?? 'N/A' }}
                                </div>
                            </td>

                            <!-- VILLE & ADRESSE -->
                            <td class="py-3.5 px-5 align-middle text-slate-600">
                                <span class="font-semibold text-slate-800">{{ $supplier->city ?? 'Non renseigné' }}</span>
                                <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ $supplier->address ?? 'Aucune adresse' }}</p>
                            </td>

                            <!-- STATUT (ACTIF / MASQUÉ) -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($supplier->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Masqué
                                    </span>
                                @endif
                            </td>

                            <!-- ARTICLES FOURNIS -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-[#0056a6] border border-blue-100">
                                    {{ $supplier->products_count }}
                                </span>
                            </td>

                            <!-- COMMANDES D'ACHAT -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                    {{ $supplier->purchases_count }}
                                </span>
                            </td>

                            <!-- ACTIONS -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- CONSULTER -->
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" 
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                                       title="Consulter la fiche">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    <!-- MODIFIER -->
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" 
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" 
                                       title="Modifier le fournisseur">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>

                                    <!-- MASQUER / RÉACTIVER (SANS SUPPRESSION) -->
                                    <form action="{{ route('admin.suppliers.toggle-status', $supplier) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($supplier->is_active)
                                            <button type="button" 
                                                onclick="confirmAction(this.closest('form'), {
                                                    title: 'Masquer ce fournisseur ?',
                                                    text: 'Le fournisseur « {{ addslashes($supplier->company_name) }} » sera masqué. Il ne sera plus proposé pour les nouveaux achats, mais l\'historique restera intact.',
                                                    confirmText: 'Oui, masquer',
                                                    icon: 'question'
                                                })"
                                                class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 flex items-center justify-center transition cursor-pointer" 
                                                title="Masquer le fournisseur">
                                                <i class="fa-solid fa-eye-slash text-xs"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                onclick="confirmAction(this.closest('form'), {
                                                    title: 'Réactiver ce fournisseur ?',
                                                    text: 'Le fournisseur « {{ addslashes($supplier->company_name) }} » sera à nouveau actif et proposé dans le système.',
                                                    confirmText: 'Oui, réactiver',
                                                    icon: 'question'
                                                })"
                                                class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 flex items-center justify-center transition cursor-pointer" 
                                                title="Réactiver le fournisseur">
                                                <i class="fa-solid fa-check text-xs"></i>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                    <i class="fa-solid fa-truck-ramp-box"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">Aucun fournisseur trouvé</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Modifiez vos critères de recherche ou ajoutez un nouveau fournisseur.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
