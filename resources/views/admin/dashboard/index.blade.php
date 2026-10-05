@extends('layouts.app')

@section('title', 'Tableau de bord Administrateur')
@section('page_title', 'Pilotage & Statistiques Générales')

@section('content')
<div class="space-y-6 sm:space-y-8">

    <!-- BANDEAU EXÉCUTIF & ACTIONS RAPIDES -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-[#0056a6] flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Données en direct
                    </span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs font-semibold text-slate-500">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
                </div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Console de Pilotage Général</h2>
            </div>
        </div>

        <!-- RACCOURCIS HAUT DE PAGE -->
        <div class="flex flex-wrap items-center gap-2 self-start md:self-auto">
            <a href="{{ route('admin.stock.entry') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-arrow-down text-emerald-600 text-xs"></i>
                <span>Entrée Stock</span>
            </a>
            <a href="{{ route('admin.purchases.create') }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-blue-50 hover:text-[#0056a6] hover:border-blue-200 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-dolly text-[#0056a6] text-xs"></i>
                <span>Nouvel Achat</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" 
               class="px-3.5 py-2 rounded-xl bg-[#0056a6] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-900/20 transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-chart-line text-xs"></i>
                <span>Rapports Financiers</span>
            </a>
        </div>
    </div>

    <!-- 4 CARTES KPI STATISTIQUES & FINANCIÈRES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">

        <!-- 1. CA DU JOUR -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition group relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Recette du Jour</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1.5 font-mono">
                        {{ number_format($caToday, 0, ',', ' ') }}
                        <span class="text-xs font-bold text-slate-400 font-sans">FCFA</span>
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold mt-2">
                        <i class="fa-solid fa-cart-shopping text-[11px]"></i>
                        <span>{{ $salesCountToday }} vente(s) aujourd'hui</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-500"></div>
        </div>

        <!-- 2. CA DU MOIS -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition group relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Chiffre d'Affaires (Mois)</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1.5 font-mono">
                        {{ number_format($caMonth, 0, ',', ' ') }}
                        <span class="text-xs font-bold text-slate-400 font-sans">FCFA</span>
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mt-2">
                        <i class="fa-solid fa-calendar-week text-slate-400 text-[10px]"></i>
                        <span>Semaine : <strong class="text-slate-700">{{ number_format($caWeek, 0, ',', ' ') }} F</strong></span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500"></div>
        </div>

        <!-- 3. BÉNÉFICE ESTIMÉ & CHARGES -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition group relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Marge Nette Estimée</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1.5 font-mono">
                        {{ number_format($estimatedProfitMonth, 0, ',', ' ') }}
                        <span class="text-xs font-bold text-slate-400 font-sans">FCFA</span>
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs text-rose-600 font-medium mt-2">
                        <i class="fa-solid fa-money-bill-wave text-[10px]"></i>
                        <span>Charges : {{ number_format($totalExpensesMonth, 0, ',', ' ') }} F</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-500"></div>
        </div>

        <!-- 4. SANTÉ DU STOCK & ALERTES -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition group relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Stock & Disponibilité</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1.5 font-mono">
                        {{ $totalProducts }}
                        <span class="text-xs font-semibold text-slate-400 font-sans">articles</span>
                    </h3>
                    <div class="flex items-center gap-2 text-xs font-bold mt-2">
                        <span class="text-rose-600 flex items-center gap-1">
                            <i class="fa-solid fa-circle-xmark text-[10px]"></i> {{ $outOfStockCount }} rupture(s)
                        </span>
                        <span class="text-amber-600 flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> {{ $lowStockCount }} alerte(s)
                        </span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-rose-500"></div>
        </div>

    </div>

    <!-- GRAPHIQUE DES VENTES & TOP PRODUITS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- GRAPHIQUE D'ÉVOLUTION SUR 7 JOURS -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                        <i class="fa-solid fa-chart-area"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Évolution des Ventes (7 Derniers Jours)</h3>
                        <p class="text-xs text-slate-400">Total des encaissements journaliers</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-blue-50 text-[#0056a6] font-bold text-xs rounded-xl border border-blue-200 self-start sm:self-auto font-mono">
                    Vue Hebdomadaire
                </span>
            </div>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- TOP 3 ARTICLES LES PLUS VENDUS -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-fire"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Top 3 Produits Vedettes</h3>
                            <p class="text-xs text-slate-400">Articles les plus demandés</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($topProducts as $idx => $prod)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-6 h-6 rounded-lg {{ $idx === 0 ? 'bg-amber-500 text-white' : ($idx === 1 ? 'bg-slate-400 text-white' : ($idx === 2 ? 'bg-amber-700 text-white' : 'bg-slate-200 text-slate-700')) }} font-mono font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-900 block truncate">{{ $prod->product_name }}</span>
                                    <span class="text-[10px] text-indigo-600 font-bold font-mono">{{ (int)$prod->total_qty }} vendus</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-black text-slate-800 font-mono">{{ number_format($prod->total_revenue, 0, ',', ' ') }}</span>
                                <span class="block text-[9px] text-slate-400 font-bold">FCFA</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-8">Aucune vente enregistrée.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100">
                <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-[#0056a6] hover:underline flex items-center justify-center gap-1.5">
                    <span>Explorer tout le catalogue</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- SECTION INFÉRIEURE : ÉTAT DES CAISSES, DERNIÈRES VENTES & AUDIT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- 1. ÉTAT DES CAISSES EN DIRECT -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">État des Caisses</h3>
                            <p class="text-xs text-slate-400">Sessions et encaissements en direct</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.cash-registers.index') }}" class="text-xs font-bold text-[#0056a6] hover:underline">
                        Gérer
                    </a>
                </div>

                <div class="space-y-3">
                    @foreach($cashRegisters as $register)
                        <div class="p-4 rounded-xl border {{ $register->status === 'open' ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $register->status === 'open' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span class="text-xs font-bold text-slate-800">{{ $register->name }}</span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $register->status === 'open' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $register->status === 'open' ? 'Ouverte' : 'Fermée' }}
                                </span>
                            </div>

                            @if($register->currentSession)
                                <div class="mt-2.5 pt-2.5 border-t border-slate-200/60 text-xs space-y-1">
                                    <p class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400">Caissier :</span>
                                        <strong class="text-slate-800">{{ $register->currentSession->user->full_name }}</strong>
                                    </p>
                                    <p class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400">Fond initial :</span>
                                        <span class="font-mono text-slate-700">{{ number_format($register->currentSession->opening_amount, 0, ',', ' ') }} F</span>
                                    </p>
                                    <p class="flex items-center justify-between text-slate-700 pt-1 border-t border-dashed border-slate-200">
                                        <span class="font-bold">Total théorique :</span>
                                        <strong class="font-mono text-emerald-700 font-bold">{{ number_format($register->currentSession->closing_amount_theory, 0, ',', ' ') }} FCFA</strong>
                                    </p>
                                </div>
                            @else
                                <p class="mt-2 text-[11px] text-slate-400 italic">Aucune session active sur ce terminal</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100">
                <a href="{{ route('admin.cash-registers.index') }}" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition text-center block">
                    Accéder aux sessions de caisse
                </a>
            </div>
        </div>

        <!-- 2. DERNIÈRES VENTES RÉALISÉES -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Dernières Ventes</h3>
                            <p class="text-xs text-slate-400">Tickets de caisse récents</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.sales.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                        Voir tout
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentSales as $sale)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition flex items-center justify-between gap-3">
                            <div>
                                <span class="font-mono font-bold text-slate-900 text-xs block">{{ $sale->sale_number }}</span>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $sale->created_at->format('H:i') }} • Par {{ $sale->user->first_name ?? $sale->user->name }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-black text-slate-900 font-mono block">{{ number_format($sale->total_amount, 0, ',', ' ') }} F</span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                    {{ $sale->payment_method }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-8">Aucune vente récente.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100">
                <a href="{{ route('admin.sales.index') }}" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition text-center block">
                    Historique complet des ventes
                </a>
            </div>
        </div>

        <!-- 3. JOURNAL D'ACTIVITÉ & AUDIT -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Journal d'Audit</h3>
                            <p class="text-xs text-slate-400">Traçabilité des opérations</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($recentActivities as $act)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition text-xs">
                            <div class="flex items-center justify-between text-[10px] text-slate-400 mb-1">
                                <span class="font-bold text-slate-700">{{ $act->user_name ?? 'Système' }}</span>
                                <span>{{ $act->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-700 font-medium leading-snug">{{ $act->description }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-8">Aucune activité enregistrée.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100">
                <span class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-emerald-600 text-xs"></i>
                    <span>Toutes les actions sont tracées et sécurisées</span>
                </span>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($salesLast7Days);

    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(0, 86, 166, 0.25)');
    gradient.addColorStop(1, 'rgba(0, 86, 166, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(d => d.date),
            datasets: [{
                label: 'Chiffre d\'Affaires (FCFA)',
                data: salesData.map(d => d.total),
                borderColor: '#0056a6',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#0056a6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#ffffff',
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('fr-FR') + ' F';
                        },
                        color: '#94a3b8',
                        font: { size: 10 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10, weight: 'bold' }
                    },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endsection
