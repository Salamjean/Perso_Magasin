@extends('layouts.app')

@section('title', 'Rapport Global & Bilan Financier')
@section('page_title', 'Rapports Financiers, Ventes & Statistiques')

@section('content')
<div class="space-y-6 w-full">

    <!-- BARRE DE FILTRAGE PAR DATE & TÉLÉCHARGEMENT PDF -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 no-print">
        
        <!-- FORMULAIRE DE CHOIX DE DATE -->
        <form method="GET" action="{{ route('admin.reports.index') }}" id="report-filter-form" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Date début</label>
                <input type="date" name="start_date" id="start_date_input" value="{{ $startDate }}" 
                    class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Date fin</label>
                <input type="date" name="end_date" id="end_date_input" value="{{ $endDate }}" 
                    class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Actualiser</span>
                </button>
            </div>

            <!-- RACCOURCIS RAPIDES -->
            <div class="flex items-center gap-1.5 pl-2 border-l border-slate-200">
                <button type="button" onclick="setQuickDate('today')" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold transition">Aujourd'hui</button>
                <button type="button" onclick="setQuickDate('month')" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold transition">Ce mois</button>
                <button type="button" onclick="setQuickDate('last_month')" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold transition">Mois dernier</button>
                <button type="button" onclick="setQuickDate('year')" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold transition">Cette année</button>
            </div>
        </form>

        <!-- BOUTON TÉLÉCHARGER LE RAPPORT EN PDF (SANS BOUTON IMPRIMER) -->
        <button type="button" onclick="downloadReportPDF()" 
            class="px-5 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-file-pdf text-sm"></i>
            <span>Télécharger le Rapport (PDF)</span>
        </button>
    </div>

    <!-- ZONE DE CONTENU DU RAPPORT À EXPORTER EN PDF -->
    <div id="report-printable-area" class="space-y-6 bg-slate-50/50 p-2 sm:p-4 rounded-2xl">
        
        <!-- EN-TÊTE DU DOCUMENT PDF -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#0056a6] border border-blue-100 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">Rapport d'Activité & Bilan Financier</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Période analysée : <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong> au <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                    </p>
                </div>
            </div>

            <div class="text-left sm:text-right text-xs text-slate-400 font-mono">
                <p>GESTiMAG — Supermarché</p>
                <p class="text-[10px] mt-0.5">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        <!-- 1. SYNTHÈSE FINANCIÈRE & RENTABILITÉ (4 CARTES KPI MAJEURES) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- CHIFFRE D'AFFAIRES -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Chiffre d'Affaires</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-900 font-mono mt-2">{{ number_format($totalSalesAmount, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span></h3>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span>{{ $totalSalesCount }} ticket(s) émis</span>
                    <span class="font-bold text-slate-700">Panier : {{ number_format($averageBasket, 0, ',', ' ') }} F</span>
                </div>
            </div>

            <!-- COÛT DES MARCHANDISES VENDUES (COGS) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Coût d'Achat Marchandises</span>
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-700 font-mono mt-2">{{ number_format($totalCogs, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span></h3>
                <p class="text-[11px] text-slate-500 mt-1">Valeur de revient des articles vendus</p>
            </div>

            <!-- MARGE BRUTE -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Marge Brute Réalisée</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-[#0056a6] font-mono mt-2">{{ number_format($grossMargin, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span></h3>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span>Ventes - Coût d'achat</span>
                    <span class="font-bold text-indigo-700">Taux : {{ number_format($marginRate, 1) }}%</span>
                </div>
            </div>

            <!-- BÉNÉFICE NET DE LA PÉRIODE -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Résultat / Bénéfice Net</span>
                    <div class="w-8 h-8 rounded-lg {{ $netProfit >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center text-xs">
                        <i class="fa-solid {{ $netProfit >= 0 ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black font-mono mt-2 {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ number_format($netProfit, 0, ',', ' ') }} <span class="text-xs font-sans font-bold text-slate-500">FCFA</span>
                </h3>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                    <span>Après charges :</span>
                    <span class="text-rose-600 font-bold">-{{ number_format($totalExpensesAmount, 0, ',', ' ') }} F</span>
                </div>
            </div>

        </div>

        <!-- 2. VENTILATION DES ENCAISSEMENTS & ACHATS / DÉPENSES (2 GRANDES COLONNES) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- VENTILATION PAR MOYEN DE PAIEMENT -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-wallet text-[#0056a6]"></i>
                        <span>Modes de Règlement Encaissés</span>
                    </h4>
                    <span class="text-xs font-bold text-slate-700 font-mono">{{ number_format($totalSalesAmount, 0, ',', ' ') }} FCFA</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                        <span class="text-xs text-emerald-800 font-bold flex items-center gap-1.5"><i class="fa-solid fa-money-bill-wave text-emerald-600"></i> Espèces</span>
                        <p class="text-base font-black text-emerald-950 font-mono mt-1">{{ number_format($paymentBreakdown['cash'], 0, ',', ' ') }} FCFA</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-100">
                        <span class="text-xs text-amber-800 font-bold flex items-center gap-1.5"><i class="fa-solid fa-mobile-screen-button text-amber-600"></i> Mobile Money</span>
                        <p class="text-base font-black text-amber-950 font-mono mt-1">{{ number_format($paymentBreakdown['mobile_money'], 0, ',', ' ') }} FCFA</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-sky-50/70 border border-sky-100">
                        <span class="text-xs text-sky-800 font-bold flex items-center gap-1.5"><i class="fa-solid fa-credit-card text-sky-600"></i> Carte Bancaire</span>
                        <p class="text-base font-black text-sky-950 font-mono mt-1">{{ number_format($paymentBreakdown['card'], 0, ',', ' ') }} FCFA</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-purple-50/70 border border-purple-100">
                        <span class="text-xs text-purple-800 font-bold flex items-center gap-1.5"><i class="fa-solid fa-building-columns text-purple-600"></i> Virement / Autre</span>
                        <p class="text-base font-black text-purple-950 font-mono mt-1">{{ number_format($paymentBreakdown['transfer'], 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>

                @if($totalDiscounts > 0)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                        <span class="text-slate-600 font-medium"><i class="fa-solid fa-percent mr-1 text-slate-400"></i> Total des remises accordées :</span>
                        <strong class="font-mono text-slate-900 font-bold">{{ number_format($totalDiscounts, 0, ',', ' ') }} FCFA</strong>
                    </div>
                @endif
            </div>

            <!-- RÉAPPROVISIONNEMENTS (ACHATS) & DÉPENSES DE FONCTIONNEMENT -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-[#0056a6]"></i>
                    <span>Achats Fournisseurs & Dépenses d'Exploitation</span>
                </h4>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Commandes d'Achats (Fournisseurs)</span>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $purchasesCount }} commande(s) passée(s) sur la période</p>
                        </div>
                        <span class="text-base font-black text-slate-900 font-mono">{{ number_format($totalPurchasesAmount, 0, ',', ' ') }} FCFA</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-rose-50/60 rounded-xl border border-rose-100">
                        <div>
                            <span class="text-xs font-bold text-rose-950 block">Dépenses & Charges d'Exploitation</span>
                            <p class="text-[10px] text-rose-600/80 mt-0.5">Frais généraux, électricité, transport, entretien...</p>
                        </div>
                        <span class="text-base font-black text-rose-700 font-mono">{{ number_format($totalExpensesAmount, 0, ',', ' ') }} FCFA</span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-amber-50/60 rounded-xl border border-amber-100">
                        <div>
                            <span class="text-xs font-bold text-amber-950 block">Créances & Dettes Clients en cours</span>
                            <p class="text-[10px] text-amber-700/80 mt-0.5">{{ $customersInDebtCount }} client(s) avec solde débiteur</p>
                        </div>
                        <span class="text-base font-black text-amber-800 font-mono">{{ number_format($totalCustomerDebt, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. TOP PRODUITS VENDUS & VENTES PAR RAYON / CATÉGORIE -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- TOP 5 PRODUITS LES PLUS VENDUS -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    <span>Top 5 des Produits les Plus Vendus</span>
                </h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase">
                                <th class="py-2.5">Produit</th>
                                <th class="py-2.5 text-center">Quantité</th>
                                <th class="py-2.5 text-right">CA Généré</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($topProducts as $idx => $prod)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                                {{ $idx + 1 }}
                                            </span>
                                            <div>
                                                <span class="font-bold text-slate-900 block">{{ $prod['name'] }}</span>
                                                <span class="text-[10px] text-slate-400 font-semibold">{{ $prod['category'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center font-mono font-bold text-slate-800">
                                        {{ $prod['total_qty'] }}
                                    </td>
                                    <td class="py-3 text-right font-mono font-black text-slate-900">
                                        {{ number_format($prod['total_revenue'], 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">Aucune vente sur cette période.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VENTES PAR RAYON / CATÉGORIE -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-tags text-[#0056a6]"></i>
                    <span>Ventes par Rayon / Catégorie</span>
                </h4>

                <div class="space-y-3 pt-1">
                    @forelse($salesByCategory as $cat)
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: {{ $cat['color'] }}"></span>
                                    {{ $cat['category_name'] }} ({{ $cat['total_qty'] }} unités)
                                </span>
                                <span class="font-mono font-bold text-slate-900">{{ number_format($cat['total_revenue'], 0, ',', ' ') }} FCFA <span class="text-slate-400 text-[10px]">({{ $cat['percentage'] }}%)</span></span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }}"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-6 text-center">Aucune vente par rayon enregistrée.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- 4. PERFORMANCE CAISSIERS, VALORISATION DU STOCK & LIVRAISONS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- PERFORMANCE DES CAISSIERS -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#0056a6]"></i>
                    <span>Activité des Caissiers</span>
                </h4>

                <div class="space-y-3">
                    @forelse($cashierPerformance as $cashier)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">{{ $cashier['name'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $cashier['sales_count'] }} ticket(s) émis</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-slate-900 text-xs">{{ number_format($cashier['total_amount'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-6 text-center">Aucune activité caisse enregistrée.</p>
                    @endforelse
                </div>
            </div>

            <!-- VALORISATION DU STOCK MAGASIN -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-indigo-600"></i>
                    <span>Valorisation du Stock Actuel</span>
                </h4>

                <div class="space-y-3">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Valeur d'Achat du Stock</span>
                            <p class="text-[10px] text-slate-400">Capital immobilisé</p>
                        </div>
                        <span class="text-xs font-black text-slate-900 font-mono">{{ number_format($totalStockValue, 0, ',', ' ') }} FCFA</span>
                    </div>

                    <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-[#0056a6] block">Valeur Vente du Stock</span>
                            <p class="text-[10px] text-slate-500">Recettes potentielles</p>
                        </div>
                        <span class="text-xs font-black text-[#0056a6] font-mono">{{ number_format($totalStockSellValue, 0, ',', ' ') }} FCFA</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                        <span class="text-slate-600">Articles en Alerte / Rupture :</span>
                        <span class="font-bold {{ ($outOfStock + $lowStock) > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $lowStock }} alerte(s) • {{ $outOfStock }} rupture(s)
                        </span>
                    </div>
                </div>
            </div>

            <!-- BILAN DES LIVRAISONS -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-motorcycle text-sky-600"></i>
                    <span>Bilan des Livraisons</span>
                </h4>

                <div class="space-y-3">
                    <div class="p-3.5 bg-emerald-50/70 rounded-xl border border-emerald-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-emerald-900 block">Livraisons Effectuées</span>
                            <p class="text-[10px] text-emerald-600">Colis livrés au client</p>
                        </div>
                        <span class="text-base font-black text-emerald-700 font-mono">{{ $deliveredCount }}</span>
                    </div>

                    <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-amber-900 block">En Cours d'Acheminement</span>
                            <p class="text-[10px] text-amber-600">Attribuées / En transit</p>
                        </div>
                        <span class="text-base font-black text-amber-700 font-mono">{{ $inTransitCount }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                        <span class="text-slate-600">Total ordres émis :</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $deliveries->count() }} course(s)</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@section('scripts')
<!-- HTML2PDF CDN BUNDLE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    function setQuickDate(range) {
        const today = new Date();
        let startDate, endDate;

        const formatDate = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        if (range === 'today') {
            startDate = formatDate(today);
            endDate = formatDate(today);
        } else if (range === 'month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            startDate = formatDate(firstDay);
            endDate = formatDate(today);
        } else if (range === 'last_month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
            startDate = formatDate(firstDay);
            endDate = formatDate(lastDay);
        } else if (range === 'year') {
            const firstDay = new Date(today.getFullYear(), 0, 1);
            startDate = formatDate(firstDay);
            endDate = formatDate(today);
        }

        document.getElementById('start_date_input').value = startDate;
        document.getElementById('end_date_input').value = endDate;
        document.getElementById('report-filter-form').submit();
    }

    function downloadReportPDF() {
        const element = document.getElementById('report-printable-area');
        const start = document.getElementById('start_date_input').value;
        const end = document.getElementById('end_date_input').value;
        const filename = `Rapport_Activite_GestMagasin_${start}_au_${end}.pdf`;

        // Affichage d'un indicateur de progression avec SweetAlert2
        Swal.fire({
            title: 'Génération du PDF...',
            text: 'Création et téléchargement du document en cours.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const opt = {
            margin: [8, 8, 8, 8],
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            Swal.close();
            Swal.fire({
                icon: 'success',
                title: 'Téléchargement réussi !',
                text: 'Le fichier PDF a été enregistré sur votre appareil.',
                timer: 2200,
                showConfirmButton: false
            });
        }).catch((err) => {
            Swal.close();
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Impossible de générer le fichier PDF.'
            });
        });
    }
</script>
@endsection
