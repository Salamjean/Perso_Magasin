<!-- BACKDROP MOBILE POUR FERMER LA SIDEBAR EN CLIQUANT SUR L'EXTÉRIEUR -->
<div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 lg:hidden opacity-0 pointer-events-none transition-opacity duration-300 ease-out"></div>

<!-- SIDEBAR RESPONSIVE (DÉTACHÉE SUR DESKTOP, TIROIR MODERNE ANIMÉ SUR MOBILE) -->
<aside id="main-sidebar" 
    class="fixed lg:static inset-y-0 left-0 z-50 my-0 lg:my-4 ml-0 lg:ml-4 h-full lg:h-[calc(100vh-2rem)] w-64 bg-white text-slate-700 flex flex-col justify-between rounded-r-2xl lg:rounded-2xl shadow-2xl lg:shadow-xl shadow-blue-900/20 border-r lg:border-2 border-[#0056a6] shrink-0 transform -translate-x-full lg:translate-x-0 overflow-hidden">
    
    <div class="flex-1 flex flex-col min-h-0">
        
        <!-- LOGO OFFICIEL GESTIMAG & BOUTON FERMETURE MOBILE -->
        <div class="h-20 flex items-center justify-between lg:justify-center px-4 border-b border-blue-100/80 bg-white rounded-none lg:rounded-t-xl overflow-hidden shrink-0">
            <a href="/" class="flex items-center justify-center py-2 max-w-full overflow-hidden">
                @php
                    $customLogo = \App\Models\Setting::get('store_logo');
                    $logoSrc = ($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
                        ? asset('storage/' . $customLogo)
                        : asset('images/logo.png');
                @endphp
                <img src="{{ $logoSrc }}" alt="Logo" class="sidebar-logo-img h-11 max-h-11 max-w-[155px] w-auto object-contain mx-auto transition-transform duration-200 hover:scale-105">
            </a>

            <!-- BOUTON FERMETURE RAPIDE SUR MOBILE SEULEMENT -->
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-rose-600 focus:outline-none cursor-pointer" title="Fermer le menu">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- NAVIGATION MENU (ONGLETS AGRANDIS & CONFORTABLES) -->
        <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">

            @if(auth()->user()->role === 'admin')
                <!-- ADMIN LINKS -->
                <div class="sidebar-text px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pilotage</div>
                <a href="{{ route('admin.dashboard') }}" title="Tableau de bord" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Tableau de bord</span>
                </a>
                <a href="{{ route('admin.users.index') }}" title="Utilisateurs" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-users-gear w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Utilisateurs</span>
                </a>

                <div class="sidebar-text px-3 pt-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Catalogue & Stock</div>
                <a href="{{ route('admin.products.index') }}" title="Produits" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.products.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-box-archive w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Produits</span>
                </a>
                <a href="{{ route('admin.stock.index') }}" title="Mouvements de Stock" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.stock.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-boxes-stacked w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Mouvements Stock</span>
                </a>
                <a href="{{ route('admin.inventory.index') }}" title="Inventaires" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.inventory.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-clipboard-check w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Inventaires</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" title="Catégories" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-tags w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Catégories</span>
                </a>
                <a href="{{ route('admin.suppliers.index') }}" title="Fournisseurs" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.suppliers.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-truck-moving w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Fournisseurs</span>
                </a>
                <a href="{{ route('admin.purchases.index') }}" title="Achats & Commandes" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.purchases.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-dolly w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Achats / Commandes</span>
                </a>

                <div class="sidebar-text px-3 pt-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ventes & Finances</div>
                <a href="{{ route('admin.sales.index') }}" title="Ventes" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.sales.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-receipt w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Toutes les ventes</span>
                </a>
                <a href="{{ route('admin.cash-registers.index') }}" title="Gestion Caisses" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.cash-registers.*') || request()->routeIs('admin.cash-sessions.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-cash-register w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Gestion Caisses</span>
                </a>
                <a href="{{ route('admin.customers.index') }}" title="Clients & Dettes" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.customers.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-user-group w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Clients & Dettes</span>
                </a>
                <a href="{{ route('admin.deliveries.index') }}" title="Livraisons" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.deliveries.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-motorcycle w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Livraisons</span>
                </a>
                <a href="{{ route('admin.expenses.index') }}" title="Dépenses" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.expenses.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-money-bill-transfer w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Dépenses</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" title="Rapports" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.reports.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-chart-line w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Rapports</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" title="Paramètres" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-sliders w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Paramètres</span>
                </a>
                <a href="{{ route('admin.sync.index') }}" title="Synchronisation Distante" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.sync.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-arrows-rotate w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Synchronisation</span>
                </a>

            @elseif(auth()->user()->role === 'magasinier')
                <!-- MAGASINIER LINKS -->
                <div class="sidebar-text px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Espace Magasin</div>
                <a href="{{ route('magasinier.dashboard') }}" title="Tableau de bord" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.dashboard') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-gauge-high w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Tableau de bord</span>
                </a>
                <a href="{{ route('magasinier.stock.index') }}" title="Mouvements de stock" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.stock.index') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-boxes-stacked w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Mouvements de stock</span>
                </a>
                <a href="{{ route('magasinier.stock.entry') }}" title="Entrée de stock" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.stock.entry') ? 'bg-emerald-600 text-white shadow-md' : 'text-emerald-700 hover:bg-emerald-50' }}">
                    <i class="fa-solid fa-arrow-down-long w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Entrée de stock</span>
                </a>
                <a href="{{ route('magasinier.stock.exit') }}" title="Sortie Casse/Périmé" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.stock.exit') ? 'bg-rose-600 text-white shadow-md' : 'text-rose-700 hover:bg-rose-50' }}">
                    <i class="fa-solid fa-arrow-up-long w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Sortie (Casse/Périmé)</span>
                </a>
                <a href="{{ route('magasinier.inventory.index') }}" title="Inventaires" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.inventory.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-clipboard-check w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Inventaires</span>
                </a>
                <a href="{{ route('magasinier.receptions.index') }}" title="Réceptions commandes" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.receptions.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-truck-ramp-box w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Réceptions commandes</span>
                </a>
                <a href="{{ route('magasinier.products.index') }}" title="Catalogue Produits" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.products.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-barcode w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Catalogue Produits</span>
                </a>
                <a href="{{ route('magasinier.alerts.index') }}" title="Alertes & Ruptures" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('magasinier.alerts.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-triangle-exclamation w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Alertes & Ruptures</span>
                </a>

            @elseif(auth()->user()->role === 'caissier')
                <!-- CAISSIER LINKS -->
                <div class="sidebar-text px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Espace Caisse</div>
                <a href="{{ route('caissier.dashboard') }}" title="Tableau de bord" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('caissier.dashboard') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-chart-simple w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Tableau de bord</span>
                </a>
                <a href="{{ route('caissier.pos.index') }}" title="Point de Vente (TPV)" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('caissier.pos.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-[#0056a6] bg-blue-50 hover:bg-blue-100' }}">
                    <i class="fa-solid fa-cash-register w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Point de Vente (TPV)</span>
                </a>
                <a href="{{ route('caissier.session.status') }}" title="Gestion de Caisse" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('caissier.session.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-vault w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Gestion de Caisse</span>
                </a>
                <a href="{{ route('caissier.sales.index') }}" title="Mes Ventes" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('caissier.sales.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-receipt w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Mes Ventes</span>
                </a>
                <a href="{{ route('caissier.returns.index') }}" title="Retours / Annulations" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('caissier.returns.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-rotate-left w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Retours / Annulations</span>
                </a>
                <a href="{{ route('caissier.deliveries.index') }}" title="Livraisons" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('caissier.deliveries.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-motorcycle w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Livraisons</span>
                </a>

            @elseif(auth()->user()->role === 'livreur')
                <!-- LIVREUR LINKS -->
                <div class="sidebar-text px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Espace Livraison</div>
                <a href="{{ route('livreur.dashboard') }}" title="Tableau de bord" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('livreur.dashboard') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-gauge w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Tableau de bord</span>
                </a>
                <a href="{{ route('livreur.deliveries.index') }}" title="Mes Livraisons" 
                    class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('livreur.deliveries.*') ? 'bg-[#0056a6] text-white shadow-md shadow-blue-900/20' : 'text-slate-600 hover:text-[#0056a6] hover:bg-blue-50/80' }}">
                    <i class="fa-solid fa-route w-5 text-base text-center shrink-0"></i>
                    <span class="sidebar-text truncate">Mes Livraisons</span>
                </a>
            @endif

        </nav>

        <!-- HORLOGE DYNAMIQUE EN TEMPS RÉEL (PLEINE LARGEUR 100% SIDEBAR FOND BLEU TOUS PROFILS) -->
        <div id="sidebar-clock-container" class="w-full shrink-0 border-t-2 border-[#004482] overflow-hidden" style="background-color: #0056a6;">
            <div class="w-full text-white px-4 py-3.5 text-center relative overflow-hidden">
                <!-- Décoration lumineuse -->
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

                <!-- Pastille d'état -->
                <div class="flex items-center justify-center mb-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider text-blue-100 border border-white/20 shadow-inner">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Temps Réel
                    </span>
                </div>

                <!-- HEURE EN GRAND FORMAT PLEINE LARGEUR (PLEINE TAILLE) -->
                <div class="text-3xl font-black font-mono text-white tracking-wide drop-shadow-md my-0.5" id="sidebar-live-time">
                    {{ now()->format('H:i:s') }}
                </div>

                <!-- DATE DU JOUR EN FRANÇAIS -->
                <div class="text-xs text-blue-100 font-bold capitalize truncate">
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </div>
            </div>
        </div>

    </div>
</aside>

<script>
    (function() {
        function updateSidebarLiveTime() {
            const el = document.getElementById('sidebar-live-time');
            if (!el) return;
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            el.textContent = h + ':' + m + ':' + s;
        }

        updateSidebarLiveTime();
        setInterval(updateSidebarLiveTime, 1000);
    })();
</script>
