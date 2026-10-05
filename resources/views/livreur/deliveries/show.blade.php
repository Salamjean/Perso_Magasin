@extends('layouts.app')

@section('title', 'Course #' . $delivery->delivery_number . ' - Livreur')
@section('page_title', 'Détails & Validation de la Course')

@section('content')
<div class="space-y-6 w-full">
    <!-- EN-TÊTE DE LA PAGE -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <h2 class="text-xl font-black font-mono text-slate-900">{{ $delivery->delivery_number }}</h2>
                    @if($delivery->status === 'assigned')
                        <span class="px-2.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 text-[11px] rounded-full font-bold">
                            <i class="fa-solid fa-clock mr-1"></i> À Démarrer
                        </span>
                    @elseif($delivery->status === 'in_transit')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-sky-50 text-sky-800 border border-sky-200 text-[11px] rounded-full font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                            En Route
                        </span>
                    @elseif($delivery->status === 'delivered')
                        <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] rounded-full font-bold">
                            <i class="fa-solid fa-circle-check mr-1"></i> Livrée avec succès
                        </span>
                    @elseif($delivery->status === 'failed')
                        <span class="px-2.5 py-0.5 bg-rose-50 text-rose-800 border border-rose-200 text-[11px] rounded-full font-bold">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> Échec de livraison
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500">
                    Assignée le {{ $delivery->created_at->format('d/m/Y à H:i') }}
                    @if($delivery->sale)
                        &bull; Liée à la vente <span class="text-[#0056a6] font-semibold font-mono">#{{ $delivery->sale->sale_number }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('livreur.deliveries.index') }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Retour aux courses</span>
            </a>
        </div>
    </div>

    <!-- GRILLE PRINCIPALE (INFOS À GAUCHE - ACTIONS À DROITE) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- ================= COLONNE GAUCHE : INFOS DESTINATAIRE & COMMANDE (7/12) ================= -->
        <div class="lg:col-span-7 space-y-6">

            <!-- CARTE 1 : COORDONNÉES DU DESTINATAIRE & ITINÉRAIRE -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-user-location"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Coordonnées du Destinataire</h3>
                    </div>
                    @if($delivery->recipient_phone)
                        <a href="tel:{{ $delivery->recipient_phone }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-bold text-xs rounded-xl transition shadow-2xs">
                            <i class="fa-solid fa-phone text-[11px]"></i>
                            <span>Appeler le client</span>
                        </a>
                    @endif
                </div>

                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Nom du Destinataire</span>
                            <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                                <span>{{ $delivery->recipient_name }}</span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Téléphone de Contact</span>
                            <div class="text-sm font-bold font-mono text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-phone text-slate-400 text-xs"></i>
                                <span>{{ $delivery->recipient_phone ?: 'Non renseigné' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ADRESSE DE LIVRAISON -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Adresse de livraison</span>
                        <p class="text-xs text-slate-800 leading-relaxed font-medium flex items-start gap-2">
                            <i class="fa-solid fa-location-dot text-rose-500 text-sm shrink-0 mt-0.5"></i>
                            <span>{{ $delivery->delivery_address }}</span>
                        </p>
                    </div>

                    @if($delivery->notes)
                        <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-amber-600 text-sm shrink-0 mt-0.5"></i>
                            <div>
                                <strong class="font-bold">Instructions / Remarques :</strong>
                                <p class="mt-0.5">{{ $delivery->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CARTE 2 : DÉTAILS DE LA COMMANDE & ARTICLES -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Articles & Contenu du Colis</h3>
                    </div>
                    @if($delivery->sale && $delivery->sale->items)
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold font-mono">
                            {{ $delivery->sale->items->count() }} article(s)
                        </span>
                    @endif
                </div>

                <div class="p-5">
                    @if($delivery->sale && $delivery->sale->items->count() > 0)
                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                            @foreach($delivery->sale->items as $item)
                                <div class="p-3.5 bg-white flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold shrink-0">
                                            <i class="fa-solid fa-cube"></i>
                                        </div>
                                        <div>
                                            <strong class="text-slate-900 font-semibold block">{{ $item->product_name }}</strong>
                                            <span class="text-[11px] text-slate-400">Prix unitaire : {{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 rounded-lg bg-blue-50 text-[#0056a6] font-bold font-mono text-xs">
                                            x{{ (float) $item->quantity }}
                                        </span>
                                        <div class="text-[11px] font-bold font-mono text-slate-700 mt-1">
                                            {{ number_format($item->total_price, 0, ',', ' ') }} F
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-xl">
                            <i class="fa-solid fa-box-open text-2xl mb-1 text-slate-300"></i>
                            <p class="text-xs">Colis direct ou aucun article spécifié dans le ticket.</p>
                        </div>
                    @endif

                    <!-- RÉCAPITULATIF FINANCIER -->
                    <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Montant à Encaisser</span>
                            <span class="text-xs text-slate-500">
                                Mode prévu : <strong>{{ $delivery->sale ? strtoupper($delivery->sale->payment_method) : 'Espèces' }}</strong>
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black font-mono {{ $delivery->total_amount > 0 ? 'text-emerald-700' : 'text-slate-700' }}">
                                {{ number_format($delivery->total_amount, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= COLONNE DROITE : VALIDATION & ACTIONS (5/12) ================= -->
        <div class="lg:col-span-5 space-y-6">

            <!-- BLOC D'ACTION SELON LE STATUT -->
            @if($delivery->status === 'in_transit')
                <!-- ACTION DIRECTE : CONFIRMATION RAPIDE SANS CODE -->
                <div class="bg-white rounded-2xl border-2 border-emerald-500 shadow-md p-6 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl shrink-0">
                            <i class="fa-solid fa-handshake-angle"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900">Remise au Destinataire</h3>
                            <p class="text-xs text-slate-500">Confirmez que la livraison a bien été effectuée.</p>
                        </div>
                    </div>

                    <form action="{{ route('livreur.deliveries.validate', $delivery->id) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Note de livraison (facultatif)</label>
                            <input type="text" 
                                   name="notes" 
                                   placeholder="Ex: Remis en mains propres..." 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- BOUTON UNIQUE DE CONFIRMATION -->
                        <button type="submit" 
                                class="w-full py-4 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-2xl text-sm transition shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                            <span>Confirmer la livraison</span>
                        </button>
                    </form>

                    <!-- SIGNALER UN PROBLÈME -->
                    <div class="pt-3 border-t border-slate-100 text-center">
                        <button type="button" 
                                onclick="document.getElementById('failure-modal').classList.remove('hidden')" 
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Signaler un problème ou échec</span>
                        </button>
                    </div>
                </div>

            @elseif($delivery->status === 'assigned')
                <!-- ACTION : DÉMARRER LA COURSE -->
                <div class="bg-white rounded-2xl border-2 border-sky-400 shadow-md p-6 space-y-4 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center text-2xl mx-auto">
                        <i class="fa-solid fa-motorcycle"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Prêt pour le départ ?</h3>
                        <p class="text-xs text-slate-500 mt-1">Cliquez sur démarrer pour indiquer que vous prenez la route.</p>
                    </div>

                    <form action="{{ route('livreur.deliveries.start', $delivery->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="w-full py-3.5 px-5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-2xl text-sm transition shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-route"></i>
                            <span>Prendre la route</span>
                        </button>
                    </form>
                </div>

            @elseif($delivery->status === 'delivered')
                <!-- ÉTAT VALIDÉ -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 text-emerald-900 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-200/70 text-emerald-800 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black">Course Validée et Clôturée</h3>
                            <p class="text-xs text-emerald-700">Livrée le {{ $delivery->delivered_at ? $delivery->delivered_at->format('d/m/Y à H:i:s') : 'Validé' }}</p>
                        </div>
                    </div>
                    @if($delivery->notes)
                        <div class="p-3 bg-white/70 rounded-xl border border-emerald-200 text-xs text-emerald-800 italic">
                            « {{ $delivery->notes }} »
                        </div>
                    @endif
                </div>

            @elseif($delivery->status === 'failed')
                <!-- ÉTAT ÉCHEC -->
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6 text-rose-900 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-rose-200/70 text-rose-800 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black">Livraison Non Effectuée</h3>
                            <p class="text-xs text-rose-700">Incident signalé</p>
                        </div>
                    </div>
                    <div class="p-3 bg-white/70 rounded-xl border border-rose-200 text-xs text-rose-800">
                        <strong>Motif :</strong> {{ $delivery->failure_reason }}
                    </div>
                </div>
            @endif

            <!-- RÉSUMÉ RAPIDE -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-3 text-xs">
                <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[10px]">Informations Complémentaires</h4>
                
                <div class="divide-y divide-slate-100">
                    <div class="py-2 flex justify-between items-center">
                        <span class="text-slate-500">Livreur en charge :</span>
                        <strong class="text-slate-800">{{ auth()->user()->full_name }}</strong>
                    </div>
                    <div class="py-2 flex justify-between items-center">
                        <span class="text-slate-500">Date d'assignation :</span>
                        <span class="font-mono text-slate-700">{{ $delivery->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="py-2 flex justify-between items-center">
                        <span class="text-slate-500">Statut actuel :</span>
                        @if($delivery->status === 'assigned')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">À Démarrer</span>
                        @elseif($delivery->status === 'in_transit')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200">En Route</span>
                        @elseif($delivery->status === 'delivered')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Livrée</span>
                        @elseif($delivery->status === 'failed')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">Échec</span>
                        @elseif($delivery->status === 'cancelled')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Annulée</span>
                        @else
                            <strong class="text-slate-800 font-bold capitalize">{{ $delivery->status }}</strong>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- MODAL SIGNALEMENT ÉCHEC -->
<div id="failure-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i> Déclarer un Incident de Livraison
            </h3>
            <button onclick="document.getElementById('failure-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('livreur.deliveries.failure', $delivery->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Motif de non-livraison <span class="text-rose-500">*</span></label>
                <select name="failure_reason" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">Sélectionnez une raison</option>
                    <option value="client_absent">Client absent au domicile</option>
                    <option value="adresse_incorrecte">Adresse introuvable ou incorrecte</option>
                    <option value="client_injoignable">Client injoignable par téléphone</option>
                    <option value="commande_refusee">Commande refusée par le client</option>
                    <option value="autre">Autre motif</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Précisions supplémentaires</label>
                <textarea name="notes" rows="3" placeholder="Informations utiles pour le service client..." class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('failure-modal').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                    Annuler
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                    Confirmer l'incident
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
