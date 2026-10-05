<!-- NAVBAR MODERNE DÉCOLLÉE FLOTTANTE (RESPONSIVE BURGER & DROPDOWN) -->
<header id="main-navbar" style="background-color: #0056a6;" class="mx-3 sm:mx-6 mt-3 sm:mt-4 h-14 text-white px-3 sm:px-6 rounded-2xl flex items-center justify-between shrink-0 shadow-lg shadow-blue-950/15 z-40 relative transition-all duration-300">
    
    <!-- GAUCHE : BOUTON BURGER + TITRE DE LA PAGE -->
    <div class="flex items-center gap-2.5 sm:gap-3">
        <!-- BOUTON BURGER (OUVRE TIROIR SUR MOBILE / RÉDUIT SUR DESKTOP) -->
        <button type="button" onclick="toggleSidebar()" 
            class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 flex items-center justify-center text-white text-sm transition-all focus:outline-none cursor-pointer border border-white/10"
            title="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- TITRE DE LA PAGE -->
        <h2 class="text-xs sm:text-sm md:text-base font-bold text-white tracking-tight leading-tight truncate max-w-[170px] sm:max-w-xs md:max-w-none">
            @yield('page_title', 'Tableau de bord')
        </h2>
    </div>

    <!-- DROITE : ACTIONS & DROPDOWN PROFIL -->
    <div class="flex items-center gap-2 sm:gap-3">
        
        <!-- ACTIONS ET HORLOGE CAISSIER UNIQUEMENT -->
        @if(auth()->user()->role === 'caissier')
            <!-- HORLOGE DYNAMIQUE EN TEMPS RÉEL (TEMPS QUI TOURNE) -->
            <div class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-[11px] sm:text-xs font-mono font-bold border border-white/15 shadow-inner transition-all select-none" title="Heure en direct">
                <i class="fa-regular fa-clock text-amber-300 animate-pulse text-xs"></i>
                <span id="navbar-live-time">--:--:--</span>
            </div>

            <!-- RACCOURCI TPV CAISSIER -->
            <a href="{{ route('caissier.pos.index') }}" 
                class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 text-[11px] sm:text-xs font-black shadow-sm transition flex items-center gap-1.5 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-cash-register text-xs"></i>
                <span class="hidden sm:inline">Point de Vente</span>
            </a>
        @endif

        @php
            $syncInitStatus = app(\App\Services\SyncService::class)->getSyncStatus();
            $unsyncedCountInit = (int) ($syncInitStatus['unsynced_count'] ?? 0);
            $isOnlineInit = (bool) ($syncInitStatus['is_online'] ?? false);
        @endphp

        <!-- WIDGET GLOBAL DE SYNCHRONISATION & MODE HORS-LIGNE -->
        <div class="flex items-center gap-1.5" id="global-sync-widget-container">
            <!-- BADGE INDICATEUR DE STATUT -->
            <div id="navbar-sync-badge" 
                 onclick="{{ auth()->user()->role === 'admin' ? "window.location.href='" . route('admin.sync.index') . "'" : "triggerGlobalSync(event)" }}"
                 class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl {{ $isOnlineInit ? ($unsyncedCountInit > 0 ? 'bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 border-amber-400/30' : 'bg-white/10 hover:bg-white/20 text-white border-white/15') : 'bg-rose-500/25 hover:bg-rose-500/35 text-rose-200 border-rose-400/30' }} text-[11px] sm:text-xs font-bold border shadow-inner transition-all select-none cursor-pointer"
                 title="{{ $isOnlineInit ? ($unsyncedCountInit > 0 ? $unsyncedCountInit . ' élément(s) en attente de synchronisation' : 'Base synchronisée avec mysql_remote') : 'Mode Hors-Ligne' }}">
                <span id="navbar-sync-dot" class="w-2 h-2 rounded-full {{ $isOnlineInit ? ($unsyncedCountInit > 0 ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400 animate-pulse') : 'bg-rose-500' }}"></span>
                <span id="navbar-sync-text" class="hidden sm:inline">{{ $isOnlineInit ? ($unsyncedCountInit > 0 ? 'En attente' : 'Synchronisé') : 'Hors-ligne' }}</span>
            </div>

            <!-- BOUTON DE SYNCHRONISATION IMMÉDIATE (SEUL AVEC BADGE COMPTEUR DE NOTIFICATION) -->
            <button type="button" id="btn-navbar-sync" onclick="triggerGlobalSync(event)" 
                    class="relative w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 text-white text-xs font-bold border border-white/15 flex items-center justify-center transition-all cursor-pointer {{ $unsyncedCountInit > 0 ? 'ring-2 ring-rose-400/50' : '' }}"
                    title="Synchroniser maintenant (PUSH & PULL)">
                <i class="fa-solid fa-arrows-rotate" id="navbar-sync-icon"></i>
                <span id="navbar-sync-btn-badge" class="{{ $unsyncedCountInit > 0 ? 'flex' : 'hidden' }} absolute -top-1.5 -right-1.5 h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 text-white text-[9px] font-black shadow-md border-2 border-[#0056a6] animate-pulse leading-none">{{ $unsyncedCountInit }}</span>
            </button>
        </div>

        <!-- DROPDOWN UTILISATEUR -->
        <div class="relative" id="user-dropdown-container">
            <button type="button" onclick="toggleUserDropdown(event)" 
                class="flex items-center gap-1.5 sm:gap-2 py-1 px-1.5 sm:px-2 rounded-xl hover:bg-white/10 transition border border-transparent focus:outline-none cursor-pointer">
                
                <!-- AVATAR -->
                <div class="w-8 h-8 rounded-full bg-white text-[#0056a6] font-bold text-xs flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                    @if(auth()->user()->photo)
                        <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->firstname ?? '', 0, 1)) }}
                    @endif
                </div>

                <!-- NOM (SUR ÉCRANS MOYENS ET GRANDS) -->
                <span class="text-xs font-semibold text-white hidden md:inline-block max-w-[130px] truncate">
                    {{ auth()->user()->full_name }}
                </span>

                <i class="fa-solid fa-chevron-down text-[9px] text-blue-200 transition-transform duration-200" id="dropdown-chevron"></i>
            </button>

            <!-- MENU DÉROULANT DU DROPDOWN -->
            <div id="user-dropdown-menu" 
                class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 py-1.5 text-slate-700 z-50 animate-fadeIn">
                
                <!-- NOM ET IDENTIFIANT -->
                <div class="px-4 py-2.5 border-b border-slate-100">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-[11px] text-slate-400 truncate mt-0.5">
                        {{ auth()->user()->role === 'admin' ? auth()->user()->email : (auth()->user()->phone ?? auth()->user()->email) }}
                    </p>
                </div>

                <!-- LIEN MON PROFIL -->
                <div class="py-1">
                    <a href="{{ route('profile') }}" 
                        class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-[#0056a6] transition">
                        <i class="fa-solid fa-user text-slate-400 text-xs w-4 text-center"></i>
                        <span>Mon Profil</span>
                    </a>
                </div>

                <div class="border-t border-slate-100 my-0.5"></div>

                <!-- DÉCONNEXION -->
                <div class="px-1.5 py-0.5">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition text-left cursor-pointer">
                            <i class="fa-solid fa-power-off text-rose-500 text-xs w-4 text-center"></i>
                            <span>Se déconnecter</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>

<!-- JAVASCRIPT DU BURGER & DROPDOWN (RESPONSIVE COMPLET MOBILE + DESKTOP) -->
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar) return;

        const isMobile = window.innerWidth < 1024;

        if (isMobile) {
            // Comportement Mobile (Tiroir coulissant gauche avec animation fluide)
            const isOpened = sidebar.classList.contains('translate-x-0');
            if (isOpened) {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                if (backdrop) {
                    backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                    backdrop.classList.add('opacity-0', 'pointer-events-none');
                }
                document.body.classList.remove('overflow-hidden');
            } else {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (backdrop) {
                    backdrop.classList.remove('opacity-0', 'pointer-events-none');
                    backdrop.classList.add('opacity-100', 'pointer-events-auto');
                }
                document.body.classList.add('overflow-hidden');
            }
        } else {
            // Comportement Desktop (Réduction / Agrandissement fluide)
            const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
        }
    }

    // Fermer le menu mobile lors d'un redimensionnement écran
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('main-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (window.innerWidth >= 1024) {
            if (sidebar) sidebar.classList.remove('translate-x-0', '-translate-x-full');
            if (backdrop) {
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
            }
            document.body.classList.remove('overflow-hidden');
        } else {
            if (sidebar && !sidebar.classList.contains('translate-x-0')) {
                sidebar.classList.add('-translate-x-full');
            }
        }
    });

    // Restaurer l'état sur desktop au chargement
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth >= 1024 && localStorage.getItem('sidebar_collapsed') === 'true') {
            const sidebar = document.getElementById('main-sidebar');
            if (sidebar) {
                sidebar.classList.add('sidebar-collapsed');
            }
        }
    });

    function toggleUserDropdown(event) {
        event.stopPropagation();
        const menu = document.getElementById('user-dropdown-menu');
        const chevron = document.getElementById('dropdown-chevron');
        const isHidden = menu.classList.contains('hidden');

        if (isHidden) {
            menu.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    document.addEventListener('click', function(event) {
        const container = document.getElementById('user-dropdown-container');
        const menu = document.getElementById('user-dropdown-menu');
        const chevron = document.getElementById('dropdown-chevron');

        if (container && !container.contains(event.target) && menu && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const menu = document.getElementById('user-dropdown-menu');
            const chevron = document.getElementById('dropdown-chevron');
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
            
            // Fermer aussi la sidebar mobile si ouverte
            if (window.innerWidth < 1024) {
                const sidebar = document.getElementById('main-sidebar');
                const backdrop = document.getElementById('sidebar-backdrop');
                if (sidebar && sidebar.classList.contains('translate-x-0')) {
                    toggleSidebar();
                }
            }
        }
    });

    // HORLOGE DYNAMIQUE EN DIRECT (SECONDE PAR SECONDE)
    function updateLiveNavbarClock() {
        const clockEl = document.getElementById('navbar-live-time');
        if (!clockEl) return;
        
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        clockEl.textContent = `${hours}:${minutes}:${seconds}`;
    }

    // GESTION DU MODE HORS-LIGNE & SYNCHRONISATION AUTOMATIQUE
    let isGlobalSyncing = false;
    let syncCheckTimer = null;
    let wasOffline = {{ $isOnlineInit ? 'false' : 'true' }};

    async function fetchLiveSyncStatus() {
        if (isGlobalSyncing) return;
        try {
            const statusUrl = window.location.origin + '/sync/live-status';
            const res = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Status check failed');
            const data = await res.json();

            // Gestion de la transition Hors-ligne -> En ligne
            if (data.is_online) {
                if (wasOffline) {
                    wasOffline = false;
                    if (data.unsynced_count > 0) {
                        if (typeof Swal !== 'undefined') {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'info',
                                title: 'Connexion rétablie !',
                                text: `Synchronisation automatique en cours (${data.unsynced_count} élément(s))...`
                            });
                        }
                        updateNavbarSyncUI(data);
                        // Déclenchement automatique de la synchronisation
                        await triggerGlobalSync(null, true);
                        return;
                    } else {
                        if (typeof Swal !== 'undefined') {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: 'Connexion rétablie !',
                                text: 'Vous êtes connecté à MySQL distant.'
                            });
                        }
                    }
                }
            } else {
                wasOffline = true;
            }

            updateNavbarSyncUI(data);
        } catch (e) {
            wasOffline = true;
            updateNavbarSyncUI({ is_online: false, unsynced_count: null });
        }
    }

    function updateNavbarSyncUI(data) {
        const dot = document.getElementById('navbar-sync-dot');
        const text = document.getElementById('navbar-sync-text');
        const btnBadge = document.getElementById('navbar-sync-btn-badge');
        const badge = document.getElementById('navbar-sync-badge');
        const btn = document.getElementById('btn-navbar-sync');

        if (!dot || !text || !badge) return;

        const unsynced = data.unsynced_count !== null && data.unsynced_count !== undefined ? data.unsynced_count : 0;

        // Mise à jour du badge de notification fixé sur le bouton Synchroniser UNIQUEMENT
        if (btnBadge) {
            if (unsynced > 0) {
                btnBadge.innerText = unsynced;
                btnBadge.classList.remove('hidden');
                btnBadge.classList.add('flex');
                if (btn) btn.classList.add('ring-2', 'ring-rose-400/60');
            } else {
                btnBadge.innerText = '0';
                btnBadge.classList.add('hidden');
                btnBadge.classList.remove('flex');
                if (btn) btn.classList.remove('ring-2', 'ring-rose-400/60');
            }
        }

        if (data.is_online) {
            if (unsynced > 0) {
                dot.className = 'w-2 h-2 rounded-full bg-amber-400 animate-pulse';
                text.innerText = 'En attente';
                badge.className = 'flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 text-[11px] sm:text-xs font-bold border border-amber-400/30 shadow-inner transition-all select-none cursor-pointer';
                badge.setAttribute('title', unsynced + ' élément(s) local(aux) en attente de synchronisation vers MySQL');
            } else {
                dot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
                text.innerText = 'Synchronisé';
                badge.className = 'flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-[11px] sm:text-xs font-bold border border-white/15 shadow-inner transition-all select-none cursor-pointer';
                badge.setAttribute('title', 'Base locale 100% synchronisée avec mysql_remote');
            }
        } else {
            dot.className = 'w-2 h-2 rounded-full bg-rose-500';
            text.innerText = 'Hors-ligne';
            badge.setAttribute('title', unsynced > 0 
                ? 'Mode Hors-ligne : ' + unsynced + ' élément(s) enregistré(s) en local dans SQLite'
                : 'Mode Hors-ligne (Serveur mysql_remote non joignable)');
            badge.className = 'flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-rose-500/25 hover:bg-rose-500/35 text-rose-200 text-[11px] sm:text-xs font-bold border border-rose-400/30 shadow-inner transition-all select-none cursor-pointer';
        }
    }

    async function triggerGlobalSync(event, isAutomatic = false) {
        if (event) event.stopPropagation();
        if (isGlobalSyncing) return;

        isGlobalSyncing = true;
        const icon = document.getElementById('navbar-sync-icon');
        const btn = document.getElementById('btn-navbar-sync');
        if (icon) icon.classList.add('fa-spin');
        if (btn) btn.disabled = true;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const syncUrl = window.location.origin + '/sync/trigger-auto';
            const res = await fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                }
            });

            const result = await res.json();

            if (result.success) {
                updateNavbarSyncUI({ is_online: true, unsynced_count: 0 });
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: isAutomatic ? 'Synchronisation automatique réussie !' : 'Synchronisation réussie !',
                        text: 'Toutes les données locales ont été synchronisées avec MySQL distant.'
                    });
                }
            } else {
                if (typeof Swal !== 'undefined' && !isAutomatic) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Synchronisation incomplète',
                        text: result.message || 'Certaines données n\'ont pas pu être transférées.',
                        showConfirmButton: false,
                        timer: 4000
                    });
                }
            }
        } catch (err) {
            console.warn('[Sync] Erreur lors de la synchronisation :', err);
            if (typeof Swal !== 'undefined' && !isAutomatic) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Serveur Distant Non Joignable',
                    text: 'Vos données restent enregistrées en toute sécurité dans SQLite.',
                    showConfirmButton: false,
                    timer: 4000
                });
            }
        } finally {
            isGlobalSyncing = false;
            if (icon) icon.classList.remove('fa-spin');
            if (btn) btn.disabled = false;
        }
    }

    // Écouteurs de reprise réseau navigateur
    window.addEventListener('online', function() {
        fetchLiveSyncStatus();
    });

    window.addEventListener('offline', function() {
        fetchLiveSyncStatus();
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateLiveNavbarClock();
        setInterval(updateLiveNavbarClock, 1000);

        fetchLiveSyncStatus();
        syncCheckTimer = setInterval(fetchLiveSyncStatus, 5000);
    });
</script>
