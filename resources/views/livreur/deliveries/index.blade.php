@extends('layouts.app')

@section('title', 'Journal des Livraisons - Livreur')
@section('page_title', 'Toutes Mes Livraisons')

@section('content')
<div class="space-y-5 w-full">
    <!-- EN-TÊTE -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-lg shrink-0 shadow-2xs">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Journal des Livraisons</h2>
                <p class="text-xs text-slate-500">Historique et gestion complète de toutes vos courses assignées.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('livreur.dashboard') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-gauge text-[11px]"></i>
                <span>Tableau de bord</span>
            </a>
        </div>
    </div>

    <!-- ONGLETS / FILTRES PAR STATUT -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('livreur.deliveries.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ !request('status') ? 'bg-[#0056a6] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
            Toutes
        </a>
        <a href="{{ route('livreur.deliveries.index', ['status' => 'assigned']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') === 'assigned' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
            À Démarrer
        </a>
        <a href="{{ route('livreur.deliveries.index', ['status' => 'in_transit']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') === 'in_transit' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
            En Route
        </a>
        <a href="{{ route('livreur.deliveries.index', ['status' => 'delivered']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') === 'delivered' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
            Livrées
        </a>
        <a href="{{ route('livreur.deliveries.index', ['status' => 'failed']) }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') === 'failed' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
            Échecs
        </a>
    </div>

    <!-- GRILLE COMPACTE À 5 CARTES PAR LIGNE SUR ÉCRAN LARGE -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
        @forelse($deliveries as $delivery)
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-150 p-3.5 flex flex-col justify-between space-y-3 relative group">
                
                <div>
                    <!-- EN-TÊTE COMPACT -->
                    <div class="flex items-center justify-between gap-1.5 pb-2 border-b border-slate-100">
                        <span class="font-mono font-black text-slate-900 text-xs tracking-tight">{{ $delivery->delivery_number }}</span>
                        
                        @if($delivery->status === 'assigned')
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 text-[9px] rounded-md font-black uppercase tracking-wide">
                                À démarrer
                            </span>
                        @elseif($delivery->status === 'in_transit')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-sky-50 text-sky-800 border border-sky-200 text-[9px] rounded-md font-black uppercase tracking-wide">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                En route
                            </span>
                        @elseif($delivery->status === 'delivered')
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[9px] rounded-md font-black uppercase tracking-wide">
                                Livrée
                            </span>
                        @elseif($delivery->status === 'failed')
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-800 border border-rose-200 text-[9px] rounded-md font-black uppercase tracking-wide">
                                Échec
                            </span>
                        @endif
                    </div>

                    <!-- INFOS DESTINATAIRE -->
                    <div class="space-y-1.5 pt-2 text-xs">
                        <div class="flex items-center justify-between gap-1">
                            <span class="font-bold text-slate-900 truncate text-[11px] flex items-center gap-1">
                                <i class="fa-solid fa-user text-slate-400 text-[10px]"></i>
                                <span class="truncate">{{ $delivery->recipient_name }}</span>
                            </span>
                            @if($delivery->recipient_phone)
                                <a href="tel:{{ $delivery->recipient_phone }}" 
                                   class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold shrink-0 hover:bg-emerald-100 transition" title="Appeler">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                            @endif
                        </div>

                        <div class="text-[10px] text-slate-500 leading-snug bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                            <p class="line-clamp-1" title="{{ $delivery->delivery_address }}">{{ $delivery->delivery_address }}</p>
                        </div>

                        <!-- MONTANT -->
                        <div class="flex items-center justify-between pt-1 text-[11px]">
                            <span class="text-slate-400 font-bold uppercase text-[9px]">À encaisser :</span>
                            <strong class="font-mono font-black {{ $delivery->total_amount > 0 ? 'text-emerald-700' : 'text-slate-700' }}">
                                {{ number_format($delivery->total_amount, 0, ',', ' ') }} F
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- BAS DE CARTE -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5">
                    <span class="text-[10px] text-slate-400 font-mono">
                        {{ $delivery->created_at->format('d/m H:i') }}
                    </span>
                    <a href="{{ route('livreur.deliveries.show', $delivery->id) }}" 
                       class="px-2.5 py-1.5 bg-[#0056a6] hover:bg-[#004485] text-white text-[11px] font-bold rounded-lg transition flex items-center gap-1 shadow-2xs">
                        <span>Fiche</span>
                        <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white p-12 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 text-xl mx-auto mb-2">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h4 class="text-xs font-bold text-slate-800">Aucune livraison trouvée</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Aucune course ne correspond à ce filtre.</p>
            </div>
        @endforelse
    </div>

    @if($deliveries->hasPages())
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $deliveries->links() }}
        </div>
    @endif
</div>
@endsection
