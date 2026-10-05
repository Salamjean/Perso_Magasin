@extends('layouts.app')

@section('title', 'Ouverture de Caisse')
@section('page-title', 'Prise de Poste & Ouverture de Caisse')

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
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xl shadow-inner flex-shrink-0">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Déclaration d'Ouverture de Caisse</h2>
                    <p class="text-xs text-slate-500 font-medium">Sélectionnez votre poste à gauche et saisissez votre fond de caisse à droite</p>
                </div>
            </div>
            <a href="{{ route('caissier.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition self-start sm:self-auto">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Retour au Tableau de Bord</span>
            </a>
        </div>

        <form action="{{ route('caissier.session.open.process') }}" method="POST" id="openSessionForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- COLONNE GAUCHE : SÉLECTION DU POSTE DE CAISSE SUR 2 COLONNES (7 colonnes) -->
                <div class="lg:col-span-7 space-y-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#0056a6] text-white text-xs flex items-center justify-center font-black">1</span>
                        <span>Poste de Caisse <span class="text-rose-500">*</span></span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="registerCards">
                        @forelse($registers as $reg)
                            @php
                                $isOccupied = ($reg->status === 'open');
                                $occupiedBy = $reg->currentSession->user->name ?? 'Autre caissier';
                            @endphp
                            <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 transition cursor-pointer group {{ $isOccupied ? 'border-slate-200 bg-slate-50 opacity-60' : 'border-slate-200 hover:border-[#0056a6] bg-white hover:bg-blue-50/20' }} register-option">
                                <input type="radio" name="cash_register_id" value="{{ $reg->id }}"
                                    {{ old('cash_register_id', $loop->first && !$isOccupied ? $reg->id : '') == $reg->id ? 'checked' : '' }}
                                    {{ $isOccupied ? 'disabled' : '' }}
                                    class="sr-only register-radio"
                                    required>

                                <div class="flex items-start justify-between gap-2">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold group-hover:bg-[#0056a6] group-hover:text-white transition flex-shrink-0">
                                        <i class="fa-solid fa-cash-register"></i>
                                    </div>
                                    @if($isOccupied)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Occupée
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disponible
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3">
                                    <h4 class="font-black text-slate-900 text-sm register-name truncate">{{ $reg->name }}</h4>
                                    <p class="text-[11px] text-slate-500 font-medium">Code : {{ $reg->code }}</p>
                                    @if($isOccupied)
                                        <p class="text-[10px] text-amber-700 font-bold mt-1 truncate">Par : {{ $occupiedBy }}</p>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <div class="col-span-2 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                                Aucun poste de caisse n'est actuellement configuré dans le système.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- COLONNE DROITE : SAISIE DES DONNÉES DU FOND DE CAISSE (5 colonnes) -->
                <div class="lg:col-span-5 space-y-6 lg:pl-6 lg:border-l lg:border-slate-100">
                    
                    <!-- FOND DE CAISSE INITIAL -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#0056a6] text-white text-xs flex items-center justify-center font-black">2</span>
                            <span>Fond de Caisse Initial Physique <span class="text-rose-500">*</span></span>
                        </label>

                        <div class="relative">
                            <input type="number" step="100" min="0" name="opening_amount" id="opening_amount"
                                value="{{ old('opening_amount', 0) }}" required autofocus
                                class="w-full pl-5 pr-20 py-4 bg-slate-50 border-2 border-slate-200 rounded-2xl text-2xl font-black text-slate-900 focus:bg-white focus:border-[#0056a6] focus:ring-4 focus:ring-[#0056a6]/15 transition outline-none">
                            <span class="absolute right-5 top-5 text-sm font-black text-slate-400">FCFA</span>
                        </div>

                        <!-- Boutons de présélection rapide -->
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Raccourcis :</span>
                            <button type="button" onclick="setAmount(0)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-extrabold transition">
                                0 F
                            </button>
                            <button type="button" onclick="setAmount(25000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-50 hover:text-[#0056a6] text-slate-700 text-xs font-extrabold transition">
                                25 000 F
                            </button>
                            <button type="button" onclick="setAmount(50000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-50 hover:text-[#0056a6] text-slate-700 text-xs font-extrabold transition">
                                50 000 F
                            </button>
                            <button type="button" onclick="setAmount(100000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-50 hover:text-[#0056a6] text-slate-700 text-xs font-extrabold transition">
                                100 000 F
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2 font-medium">
                            <i class="fa-solid fa-circle-info text-slate-400 mr-1"></i> Montant présent physiquement dans le tiroir au démarrage.
                        </p>
                    </div>

                    <!-- NOTES / REMARQUES -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#0056a6] text-white text-xs flex items-center justify-center font-black">3</span>
                            <span>Notes de Prise de Service (Optionnel)</span>
                        </label>
                        <textarea name="notes" rows="2" placeholder="Ex: Prise de poste matin, tiroir et rouleaux de monnaie vérifiés..."
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-xs font-medium focus:bg-white focus:border-[#0056a6] focus:ring-4 focus:ring-[#0056a6]/15 transition outline-none"></textarea>
                    </div>

                    <!-- BOUTON DE SOUMISSION BLEU -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 rounded-2xl bg-[#0056a6] hover:bg-[#004482] text-white font-black text-sm shadow-xl shadow-[#0056a6]/30 hover:shadow-[#0056a6]/40 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-3 cursor-pointer">
                            <i class="fa-solid fa-check-double text-base"></i>
                            <span>Valider & Démarrer ma Session de Caisse</span>
                        </button>
                    </div>

                </div>

            </div>
        </form>

    </div>

</div>

<script>
    function setAmount(amount) {
        document.getElementById('opening_amount').value = amount;
    }

    // Gestion du style visuel des cartes de sélection de caisse
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('.register-radio');
        const options = document.querySelectorAll('.register-option');

        function updateSelection() {
            radios.forEach((radio, index) => {
                const card = options[index];
                if (radio.checked) {
                    card.classList.add('border-[#0056a6]', 'bg-blue-50/40', 'ring-2', 'ring-[#0056a6]/20');
                    card.classList.remove('border-slate-200', 'bg-white');
                } else if (!radio.disabled) {
                    card.classList.remove('border-[#0056a6]', 'bg-blue-50/40', 'ring-2', 'ring-[#0056a6]/20');
                    card.classList.add('border-slate-200', 'bg-white');
                }
            });
        }

        radios.forEach(radio => {
            radio.addEventListener('change', updateSelection);
        });

        updateSelection();
    });
</script>
@endsection
