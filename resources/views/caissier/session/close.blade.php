@extends('layouts.app')

@section('title', 'Clôture de Caisse')
@section('page-title', 'Clôture de Service & Arrêté de Caisse')

@section('content')
<div class="max-w-5xl mx-auto py-2 sm:py-6 space-y-6">

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-3 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-base text-rose-600 flex-shrink-0"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <!-- CONTENEUR PRINCIPAL DU FORMULAIRE CENTRÉ -->
    <div class="bg-white rounded-3xl p-7 sm:p-9 border border-slate-200/80 shadow-xl shadow-slate-200/40 space-y-7">
        
        <!-- EN-TÊTE DU PANNEAU -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner flex-shrink-0">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Clôture de Session & Arrêté des Comptes</h2>
                    <p class="text-xs text-slate-500 font-medium">Vérifiez le bilan de votre service à gauche et saisissez le montant physique compté à droite</p>
                </div>
            </div>
            <a href="{{ route('caissier.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition self-start sm:self-auto">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Retour au Tableau de Bord</span>
            </a>
        </div>

        <form action="{{ route('caissier.session.close.process') }}" method="POST" id="closeSessionForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- COLONNE GAUCHE : BILAN ET RÉCAPITULATIF DE SESSION (7 colonnes) -->
                <div class="lg:col-span-7 space-y-5">
                    
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-rose-600 text-white text-xs flex items-center justify-center font-black">1</span>
                        <span>Bilan & Récapitulatif du Service</span>
                    </label>

                    <!-- CARTE DU POSTE DE CAISSE EN SERVICE -->
                    <div class="p-4 rounded-2xl border-2 border-slate-200/90 bg-slate-50/60 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center text-base font-bold shrink-0 shadow-sm">
                                <i class="fa-solid fa-cash-register"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-black text-slate-900 text-sm truncate">{{ $session->cashRegister->name }}</h4>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Clôture en cours
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 font-medium">
                                    Ouverte le {{ $session->opened_at->format('d/m/Y à H:i') }} &bull; Par {{ auth()->user()->full_name }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Fond Initial</span>
                            <span class="text-xs font-mono font-black text-slate-800">{{ number_format($session->opening_amount, 0, ',', ' ') }} F</span>
                        </div>
                    </div>

                    <!-- GRILLE DES 4 MODES D'ENCAISSEMENT -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Espèces Encaissées -->
                        <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-emerald-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i> Espèces
                                </span>
                                <span class="text-[10px] font-bold text-emerald-600 uppercase">Au tiroir</span>
                            </div>
                            <div class="text-lg font-black text-emerald-900 font-mono">{{ number_format($sessionStats['total_cash'], 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold">FCFA</span></div>
                            <span class="text-[10px] text-emerald-700 font-medium">Liquide perçu pendant le service</span>
                        </div>

                        <!-- Ventes à Crédit -->
                        <div class="p-3.5 rounded-2xl bg-purple-50/70 border border-purple-200">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-purple-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-hand-holding-dollar text-purple-600"></i> Crédits
                                </span>
                                <span class="text-[10px] font-bold text-purple-600 uppercase">Non perçu</span>
                            </div>
                            <div class="text-lg font-black text-purple-900 font-mono">{{ number_format($sessionStats['total_credits'], 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold">FCFA</span></div>
                            <span class="text-[10px] text-purple-700 font-medium">{{ $sessionStats['credits_count'] ?? 0 }} ticket(s) (exclu du tiroir)</span>
                        </div>

                        <!-- Mobile Money -->
                        <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-amber-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-mobile-screen-button text-amber-600"></i> Mobile Money
                                </span>
                            </div>
                            <div class="text-lg font-black text-amber-900 font-mono">{{ number_format($sessionStats['total_mobile_money'], 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold">FCFA</span></div>
                            <span class="text-[10px] text-amber-700 font-medium">Comptes électroniques</span>
                        </div>

                        <!-- Cartes Bancaires -->
                        <div class="p-3.5 rounded-2xl bg-sky-50/70 border border-sky-200">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-sky-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-credit-card text-sky-600"></i> Carte Bancaire
                                </span>
                            </div>
                            <div class="text-lg font-black text-sky-900 font-mono">{{ number_format($sessionStats['total_card'], 0, ',', ' ') }} <span class="text-[10px] font-sans font-bold">FCFA</span></div>
                            <span class="text-[10px] text-sky-700 font-medium">Paiements par TPE / Carte</span>
                        </div>
                    </div>

                    <!-- BANDEAU TOTAL THÉORIQUE ATTENDU -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-[#0056a6] text-white shadow-lg shadow-blue-900/20 border border-blue-400/20 relative overflow-hidden">
                        <!-- Effet décoratif discret -->
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10 pointer-events-none"></div>
                        <div class="absolute -left-6 -bottom-6 w-24 h-24 rounded-full bg-black/10 pointer-events-none"></div>

                        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-blue-100 flex items-center gap-1.5">
                                    <i class="fa-solid fa-vault text-blue-200"></i>
                                    Tiroir Théorique (Espèces Attendues)
                                </span>
                                <div class="text-3xl sm:text-4xl font-black font-mono mt-1 tracking-tight text-white">
                                    {{ number_format($session->closing_amount_theory, 0, ',', ' ') }} <span class="text-base font-sans font-bold text-blue-200">FCFA</span>
                                </div>
                                <span class="text-[11px] text-blue-100 font-medium mt-0.5 block">
                                    Total liquide physique qui doit être présent dans la caisse
                                </span>
                            </div>
                            <div class="text-left sm:text-right text-xs text-blue-100 font-medium leading-relaxed bg-white/15 backdrop-blur-sm p-3 rounded-xl border border-white/20 shrink-0">
                                <div class="flex items-center justify-between sm:justify-end gap-3">
                                    <span class="text-blue-200">Fond initial :</span>
                                    <strong class="font-mono text-white font-bold">{{ number_format($session->opening_amount, 0, ',', ' ') }} F</strong>
                                </div>
                                <div class="flex items-center justify-between sm:justify-end gap-3 pt-1 mt-1 border-t border-white/20">
                                    <span class="text-blue-200">+ Espèces nettes :</span>
                                    <strong class="font-mono text-emerald-300 font-bold">{{ number_format($sessionStats['total_cash'], 0, ',', ' ') }} F</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- COLONNE DROITE : DÉCLARATION DU MONTANT RÉEL COMPTÉ (5 colonnes) -->
                <div class="lg:col-span-5 space-y-6 lg:pl-6 lg:border-l lg:border-slate-100">
                    
                    <!-- SAISIE DU MONTANT RÉEL COMPTÉ -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-rose-600 text-white text-xs flex items-center justify-center font-black">2</span>
                            <span>Montant Réel Compté (Espèces Physiques) <span class="text-rose-500">*</span></span>
                        </label>

                        <div class="relative">
                            <input type="number" step="0.01" min="0" name="closing_amount_real" id="real_amount"
                                value="{{ old('closing_amount_real') }}" required autofocus
                                placeholder="Comptez vos billets..."
                                oninput="calculateDiscrepancy(this)"
                                class="w-full pl-5 pr-20 py-4 bg-slate-50 border-2 border-slate-200 rounded-2xl text-2xl font-black text-slate-900 focus:bg-white focus:border-[#0056a6] focus:ring-4 focus:ring-[#0056a6]/15 transition outline-none">
                            <span class="absolute right-5 top-5 text-sm font-black text-slate-400">FCFA</span>
                        </div>

                        <!-- Boutons de raccourcis rapides -->
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Raccourci :</span>
                            <button type="button" onclick="setRealAmount({{ (float)$session->closing_amount_theory }})" 
                                    class="px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 hover:bg-blue-100 text-[#0056a6] text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <i class="fa-solid fa-calculator text-[10px]"></i>
                                <span>Égal au Théorique ({{ number_format($session->closing_amount_theory, 0, ',', ' ') }} F)</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2 font-medium">
                            <i class="fa-solid fa-circle-info text-slate-400 mr-1"></i> Total exact des billets et pièces comptés physiquement dans le tiroir.
                        </p>
                    </div>

                    <!-- ÉCART DE CAISSE ESTIMÉ EN DIRECT -->
                    <div id="discrepancy-box" class="p-4 rounded-2xl border text-xs hidden transition-all">
                        <div class="flex items-center justify-between font-bold">
                            <span class="flex items-center gap-1.5" id="discrepancy-label">
                                <i class="fa-solid fa-scale-balanced"></i> Écart de caisse :
                            </span>
                            <span id="discrepancy-value" class="text-sm font-black font-mono">0 FCFA</span>
                        </div>
                    </div>

                    <!-- NOTES / JUSTIFICATIONS -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-rose-600 text-white text-xs flex items-center justify-center font-black">3</span>
                            <span>Observations / Justifications d'Écart</span>
                        </label>
                        <textarea name="notes" rows="2" placeholder="Ex: Clôture fin de journée, monnaie recomptée et vérifiée..."
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-xs font-medium focus:bg-white focus:border-rose-600 focus:ring-4 focus:ring-rose-600/15 transition outline-none">{{ old('notes') }}</textarea>
                    </div>

                    <!-- BOUTON DE CONFIRMATION DE CLÔTURE -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-sm shadow-xl shadow-rose-600/30 hover:shadow-rose-600/40 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-3 cursor-pointer">
                            <i class="fa-solid fa-lock text-base"></i>
                            <span>Confirmer la Clôture & Arrêter les Comptes</span>
                        </button>
                    </div>

                </div>

            </div>
        </form>

    </div>

</div>

<script>
    const theoreticalAmount = {{ (float)$session->closing_amount_theory }};

    function setRealAmount(amount) {
        const input = document.getElementById('real_amount');
        input.value = amount;
        calculateDiscrepancy(input);
    }

    function calculateDiscrepancy(input) {
        const val = parseFloat(input.value);
        const box = document.getElementById('discrepancy-box');
        const text = document.getElementById('discrepancy-value');
        const label = document.getElementById('discrepancy-label');

        if (isNaN(val)) {
            box.classList.add('hidden');
            return;
        }

        box.classList.remove('hidden');
        const diff = val - theoreticalAmount;

        if (Math.abs(diff) < 0.01) {
            box.className = 'p-4 rounded-2xl border-2 border-emerald-300 bg-emerald-50 text-emerald-900 text-xs shadow-xs';
            label.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> Caisse Parfaitement Équilibrée :';
            text.innerText = '0 FCFA (Écart nul)';
        } else if (diff < 0) {
            box.className = 'p-4 rounded-2xl border-2 border-rose-300 bg-rose-50 text-rose-900 text-xs shadow-xs';
            label.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i> Manquant en Caisse :';
            text.innerText = Number(diff).toLocaleString('fr-FR') + ' FCFA';
        } else {
            box.className = 'p-4 rounded-2xl border-2 border-blue-300 bg-blue-50 text-blue-900 text-xs shadow-xs';
            label.innerHTML = '<i class="fa-solid fa-circle-plus text-blue-600 text-sm"></i> Surplus en Caisse :';
            text.innerText = '+' + Number(diff).toLocaleString('fr-FR') + ' FCFA';
        }
    }
</script>
@endsection
