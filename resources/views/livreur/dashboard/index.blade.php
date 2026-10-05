@extends('layouts.app')

@section('title', 'Tableau de Bord - Livreur')
@section('page_title', 'Espace Livreur & Suivi des Courses')

@section('content')
<div class="space-y-6 w-full">

    <!-- EN-TÊTE DU TABLEAU DE BORD LIVREUR -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        En service
                    </span>
                    <span class="text-xs text-slate-400 font-medium">{{ auth()->user()->full_name }}</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Tableau de Bord & Courses</h2>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('livreur.deliveries.index') }}" 
               class="px-4 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-900/15 flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-list-check text-xs"></i>
                <span>Toutes mes courses</span>
            </a>
            <div class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-left">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Taux succès</span>
                <span class="text-sm font-black font-mono text-emerald-600">{{ $successRate }}%</span>
            </div>
        </div>
    </div>

    <!-- GRILLE KPI (4 CARTES DESIGN PREMIUM) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- 1. À PRENDRE EN CHARGE -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Assignées
                </span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">À Prendre en charge</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl sm:text-3xl font-black font-mono text-slate-900">{{ $assignedCount }}</h3>
                    <span class="text-xs text-slate-500 font-medium">course(s)</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                <span>En attente de départ</span>
                <i class="fa-solid fa-arrow-right text-slate-400 text-[10px]"></i>
            </div>
        </div>

        <!-- 2. EN COURS DE ROUTE -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-motorcycle animate-pulse"></i>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                    Sur le terrain
                </span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">En Cours de Route</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl sm:text-3xl font-black font-mono text-sky-600">{{ $inTransitCount }}</h3>
                    <span class="text-xs text-slate-500 font-medium">en livraison</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                <span>À livrer incessamment</span>
                <i class="fa-solid fa-route text-sky-500 text-[10px]"></i>
            </div>
        </div>

        <!-- 3. ENCAISSEMENTS DU JOUR -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Aujourd'hui
                </span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Encaissé Aujourd'hui</p>
                <div class="flex items-baseline gap-1 mt-1">
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-emerald-700">{{ number_format($cashCollectedToday, 0, ',', ' ') }}</h3>
                    <span class="text-[11px] font-bold text-slate-500 font-sans">FCFA</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Restant à encaisser :</span>
                <strong class="text-slate-800 font-mono">{{ number_format($cashToCollect, 0, ',', ' ') }} F</strong>
            </div>
        </div>

        <!-- 4. TOTAL LIVRÉES DU JOUR & GLOBAL -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#0056a6] flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-[#0056a6] border border-blue-200">
                    Succès
                </span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Livrées Aujourd'hui</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl sm:text-3xl font-black font-mono text-slate-900">{{ $deliveredTodayCount }}</h3>
                    <span class="text-xs text-slate-500 font-medium">validée(s)</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Total historique :</span>
                <strong class="text-slate-800 font-mono">{{ $totalDeliveredCount }}</strong>
            </div>
        </div>

    </div>

    <!-- COURSES ACTIVES PRIORITAIRES (CARTES ÉPURÉES ET COMPACTES) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-sm font-bold shrink-0">
                    <i class="fa-solid fa-bolt text-amber-500"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">Courses Actives Prioritaires</h3>
                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-[#0056a6] text-[10px] font-black border border-blue-100">
                            {{ $activeDeliveries->count() }} active(s)
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500">Actions rapides, coordonnées clients et validation directe des remises.</p>
                </div>
            </div>
            <a href="{{ route('livreur.deliveries.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                <span>Toutes les courses</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        @if($activeDeliveries->count() > 0)
            <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @foreach($activeDeliveries as $deliv)
                    <div class="p-4 rounded-xl border transition-all duration-200 flex flex-col justify-between space-y-3 relative {{ $deliv->status === 'in_transit' ? 'border-sky-300 bg-sky-50/20 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-xs' }}">
                        
                        <!-- EN-TÊTE COMPACT -->
                        <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-slate-100">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono font-black text-slate-900 text-xs tracking-tight">{{ $deliv->delivery_number }}</span>
                                    @if($deliv->status === 'in_transit')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-black bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-600 animate-ping"></span>
                                            En Route
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wide">
                                            À démarrer
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5 truncate">
                                    {{ $deliv->created_at->format('d/m/Y H:i') }}
                                    @if($deliv->sale)
                                        &bull; <span class="text-[#0056a6] font-semibold">#{{ $deliv->sale->sale_number }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right shrink-0 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                                <div class="text-[9px] uppercase font-bold text-slate-400">À encaisser</div>
                                <div class="text-xs font-black font-mono {{ $deliv->total_amount > 0 ? 'text-emerald-700' : 'text-slate-600' }}">
                                    {{ number_format($deliv->total_amount, 0, ',', ' ') }} F
                                </div>
                            </div>
                        </div>

                        <!-- DESTINATAIRE & ADRESSE -->
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-900 truncate flex items-center gap-1.5 text-xs">
                                    <i class="fa-solid fa-user text-slate-400 text-[10px]"></i>
                                    <span class="truncate">{{ $deliv->recipient_name }}</span>
                                </span>
                                @if($deliv->recipient_phone)
                                    <a href="tel:{{ $deliv->recipient_phone }}" 
                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-bold text-[10px] shrink-0 transition" title="Appeler le client">
                                        <i class="fa-solid fa-phone text-[9px]"></i>
                                        <span>{{ $deliv->recipient_phone }}</span>
                                    </a>
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5 text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100/80">
                                <i class="fa-solid fa-location-dot text-rose-500 text-[10px] mt-0.5 shrink-0"></i>
                                <span class="line-clamp-1 leading-snug text-[11px]" title="{{ $deliv->delivery_address }}">{{ $deliv->delivery_address }}</span>
                            </div>

                            @if($deliv->notes)
                                <div class="text-[10px] text-slate-500 italic truncate" title="{{ $deliv->notes }}">
                                    <i class="fa-solid fa-circle-info text-blue-500 mr-0.5"></i> {{ $deliv->notes }}
                                </div>
                            @endif

                            @if($deliv->sale && $deliv->sale->items->count() > 0)
                                <div class="flex flex-wrap gap-1 pt-0.5">
                                    @foreach($deliv->sale->items->take(2) as $item)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[9px] text-slate-600 font-medium truncate max-w-[130px]">
                                            {{ (float) $item->quantity }}x {{ $item->product_name }}
                                        </span>
                                    @endforeach
                                    @if($deliv->sale->items->count() > 2)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[9px] text-slate-400 font-medium">+{{ $deliv->sale->items->count() - 2 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- ACTIONS COMPACTES -->
                        <div class="pt-2 border-t border-slate-100 flex items-center gap-1.5">
                            @if($deliv->status === 'assigned')
                                <form action="{{ route('livreur.deliveries.start', $deliv->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="w-full py-1.5 px-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-[11px] transition shadow-2xs flex items-center justify-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-motorcycle text-[10px]"></i>
                                        <span>Démarrer</span>
                                    </button>
                                </form>
                            @elseif($deliv->status === 'in_transit')
                                <button type="button" onclick="openValidateModal('{{ $deliv->id }}', '{{ $deliv->delivery_number }}', '{{ $deliv->recipient_name }}')"
                                        class="flex-1 py-1.5 px-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[11px] transition shadow-2xs flex items-center justify-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-check-circle text-[10px]"></i>
                                    <span>Valider</span>
                                </button>
                                <button type="button" onclick="openFailureModal('{{ $deliv->id }}', '{{ $deliv->delivery_number }}')"
                                        class="py-1.5 px-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg text-[11px] transition flex items-center justify-center cursor-pointer" title="Signaler un problème">
                                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                </button>
                            @endif

                            <a href="{{ route('livreur.deliveries.show', $deliv->id) }}" 
                               class="py-1.5 px-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px] transition flex items-center justify-center gap-1" title="Voir la fiche détaillée">
                                <i class="fa-solid fa-eye text-[10px]"></i>
                                <span>Fiche</span>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-400">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mx-auto mb-2">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <h4 class="text-xs font-bold text-slate-800">Aucune course active</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Toutes vos livraisons assignées ont été traitées.</p>
            </div>
        @endif
    </div>

    <!-- DERNIÈRES LIVRAISONS EFFECTUÉES -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Dernières Livraisons Réalisées</h3>
            </div>
            <span class="text-xs text-slate-400 font-medium">Historique récent</span>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            @forelse($recentCompleted as $comp)
                <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs shrink-0 font-bold">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900">{{ $comp->delivery_number }}</span>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Livrée</span>
                            </div>
                            <p class="text-slate-500 text-[11px] mt-0.5">Destinataire : <strong class="text-slate-700">{{ $comp->recipient_name }}</strong> &bull; {{ $comp->delivery_address }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0 pl-11 sm:pl-0">
                        <div class="text-right">
                            <div class="font-mono font-bold text-slate-900 text-xs">{{ number_format($comp->total_amount, 0, ',', ' ') }} FCFA</div>
                            <div class="text-[10px] text-slate-400">{{ $comp->delivered_at ? $comp->delivered_at->diffForHumans() : 'Récemment' }}</div>
                        </div>
                        <a href="{{ route('livreur.deliveries.show', $comp->id) }}" class="p-2 text-slate-400 hover:text-[#0056a6] hover:bg-blue-50 rounded-lg transition" title="Consulter la fiche">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    Aucune livraison terminée aujourd'hui. Vos courses finalisées apparaîtront ici.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- MODAL RAPIDE DE VALIDATION DIRECTE (SANS CODE) -->
<div id="quick-validate-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Validation de la Livraison</h3>
                    <p class="text-[11px] text-slate-400" id="modal-deliv-title">Course LIV-XXXX</p>
                </div>
            </div>
            <button type="button" onclick="closeValidateModal()" class="text-slate-400 hover:text-slate-600 p-1.5 cursor-pointer">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <form id="validate-delivery-form" method="POST" action="" class="space-y-4">
            @csrf
            
            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100 text-xs text-emerald-900 flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0 mt-0.5"></i>
                <div>
                    <strong class="font-bold block text-emerald-950">Confirmer la remise du colis</strong>
                    <span class="text-[11px] text-emerald-800">Le statut de la course sera marqué comme "Livrée avec succès".</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Note ou observation (Facultatif)
                </label>
                <textarea name="notes" id="validate_notes_input" rows="2" placeholder="Ex: Remis en mains propres au destinataire..." 
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeValidateModal()" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition cursor-pointer">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirmer la livraison</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL RAPIDE DE SIGNALEMENT D'ÉCHEC -->
<div id="quick-failure-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Signaler un Incident / Échec</h3>
                    <p class="text-[11px] text-slate-400" id="modal-failure-title">Course LIV-XXXX</p>
                </div>
            </div>
            <button type="button" onclick="closeFailureModal()" class="text-slate-400 hover:text-slate-600 p-1.5 cursor-pointer">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <form id="failure-delivery-form" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Motif de non-livraison <span class="text-rose-500">*</span>
                </label>
                <select name="failure_reason" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 focus:outline-none transition cursor-pointer">
                    <option value="">Sélectionner un motif</option>
                    <option value="client_absent">Client absent au domicile</option>
                    <option value="client_injoignable">Client injoignable par téléphone</option>
                    <option value="adresse_incorrecte">Adresse introuvable ou incorrecte</option>
                    <option value="commande_refusee">Commande refusée par le destinataire</option>
                    <option value="autre">Autre problème (préciser ci-dessous)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Détails ou explications complémentaires
                </label>
                <textarea name="notes" rows="2" placeholder="Précisions sur les tentatives d'appel ou la situation..." 
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 focus:outline-none transition"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeFailureModal()" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition cursor-pointer">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Enregistrer l'incident</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openValidateModal(delivId, delivNumber, recipientName) {
        const form = document.getElementById('validate-delivery-form');
        form.action = `{{ url('livreur/deliveries') }}/${delivId}/validate`;
        document.getElementById('modal-deliv-title').innerText = `${delivNumber} (${recipientName})`;
        document.getElementById('validate_notes_input').value = '';
        document.getElementById('quick-validate-modal').classList.remove('hidden');
    }

    function closeValidateModal() {
        document.getElementById('quick-validate-modal').classList.add('hidden');
    }

    function openFailureModal(delivId, delivNumber) {
        const form = document.getElementById('failure-delivery-form');
        form.action = `{{ url('livreur/deliveries') }}/${delivId}/failure`;
        document.getElementById('modal-failure-title').innerText = delivNumber;
        document.getElementById('quick-failure-modal').classList.remove('hidden');
    }

    function closeFailureModal() {
        document.getElementById('quick-failure-modal').classList.add('hidden');
    }
</script>
@endsection
