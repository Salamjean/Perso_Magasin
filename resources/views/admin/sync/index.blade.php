@extends('layouts.app')

@section('title', 'Centre de Synchronisation & Mode Hors-Ligne - Admin')
@section('page_title', 'Synchronisation Distante (mysql_remote)')

@section('content')
<div class="space-y-6 w-full">

    <!-- EN-TÊTE DU HUB DE SYNCHRONISATION -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0056a6] text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Synchronisation Automatique & Mode Hors-Ligne</h2>
                    <span id="connection-badge" class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold {{ $status['is_online'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        <span class="w-2 h-2 rounded-full {{ $status['is_online'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                        <span id="connection-status-text">{{ $status['is_online'] ? 'Serveur Distant Connecté' : 'Mode Hors-Ligne Actif' }}</span>
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    Les données s'enregistrent en local en cas de coupure. Dès la reconnexion, la synchronisation (PUSH & PULL) s'exécute automatiquement.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="testConnection()" id="btn-test-conn" 
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-network-wired text-xs"></i>
                <span>Tester la connexion</span>
            </button>
            <form action="{{ route('admin.sync.all') }}" method="POST" id="form-sync-all" onsubmit="startSync(event, this)">
                @csrf
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#0056a6] hover:bg-[#004485] text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-900/15 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate text-xs sync-spin-icon"></i>
                    <span>Synchroniser Maintenant</span>
                </button>
            </form>
        </div>
    </div>

    <!-- CARTE D'ÉTAT DE LA CONNEXION MYSQL REMOTE & MODE HORS-LIGNE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- ÉTAT SERVEUR DISTANT -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Serveur Distant</h3>
                            <p class="text-[11px] text-slate-400 font-mono">mysql_remote</p>
                        </div>
                    </div>
                    <div id="latency-container" class="text-right">
                        @if($status['is_online'])
                            <strong class="text-xs font-mono font-black text-emerald-600">{{ $status['remote_info']['latency_ms'] }} ms</strong>
                        @else
                            <span class="text-xs text-rose-500 font-bold">Déconnecté</span>
                        @endif
                    </div>
                </div>

                <div id="connection-alert-box" class="mt-3.5 p-3 rounded-xl text-xs flex items-start gap-2 {{ $status['is_online'] ? 'bg-emerald-50/70 border border-emerald-200 text-emerald-800' : 'bg-rose-50/70 border border-rose-200 text-rose-900' }}">
                    <i class="fa-solid {{ $status['is_online'] ? 'fa-circle-check text-emerald-600' : 'fa-triangle-exclamation text-rose-600' }} text-sm shrink-0 mt-0.5"></i>
                    <div class="leading-relaxed font-mono text-[11px]" id="connection-message">
                        {{ $status['remote_info']['message'] }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Hôte : <strong class="font-mono text-slate-700">{{ $status['remote_info']['host'] }}</strong></span>
                <span>Base : <strong class="font-mono text-slate-700">{{ $status['remote_info']['database'] }}</strong></span>
            </div>
        </div>

        <!-- PRINCIPE DU MODE AUTOMATIQUE -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-sm">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Synchronisation Bidirectionnelle Automatique</h3>
                        <p class="text-xs text-slate-400">Aucune action manuelle requise au quotidien</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                            <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
                            <span>1. PUSH Automatique (Local &rarr; Distant)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Toutes vos ventes, sessions de caisse, mouvements de stock et livraisons créées hors ligne sont automatiquement téléversées dès que le serveur est accessible.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                            <i class="fa-solid fa-cloud-arrow-down text-indigo-600"></i>
                            <span>2. PULL Automatique (Distant &rarr; Local)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Les modifications de catalogue (nouveaux articles, prix, catégories, fiches clients) faites sur le serveur distant sont automatiquement téléchargées en local.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2 text-[11px] text-slate-500">
                <i class="fa-solid fa-circle-info text-[#0056a6]"></i>
                <span>Le bouton <strong>"Synchroniser Maintenant"</strong> sert de secours immédiat si vous souhaitez forcer un échange sans attendre le cycle automatique.</span>
            </div>
        </div>

    </div>

    <!-- ÉLÉMENTS LOCAUX EN ATTENTE DE SYNCHRONISATION -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $status['unsynced_count'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center text-base">
                    <i class="fa-solid {{ $status['unsynced_count'] > 0 ? 'fa-clock' : 'fa-check-double' }}"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900">Données locales en attente de synchronisation</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $status['unsynced_count'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $status['unsynced_count'] }} en attente
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">Éléments enregistrés localement qui seront poussés vers le serveur distant</p>
                </div>
            </div>

            @if($status['unsynced_count'] > 0)
                <button type="button" onclick="triggerGlobalSync(event)" 
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    <span>Pousser les {{ $status['unsynced_count'] }} éléments</span>
                </button>
            @endif
        </div>

        @if($status['unsynced_count'] === 0)
            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-100 text-xs text-emerald-800 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0"></i>
                <div>
                    <strong class="font-bold">Base locale 100% à jour !</strong>
                    <p class="text-[11px] text-emerald-700 mt-0.5">Toutes les opérations créées en caisse, magasin et livraisons sont synchronisées avec le serveur distant.</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 pt-1">
                @foreach($status['unsynced_breakdown'] as $key => $item)
                    <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-200/70 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-700">{{ $item['label'] }}</span>
                        <strong class="font-mono text-sm font-black text-amber-700 bg-amber-100 px-2 py-0.5 rounded-lg">{{ $item['count'] }}</strong>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- STATISTIQUES DES DONNÉES LOCALES -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Volume total des données locales disponibles</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Produits</span>
                <span class="text-lg font-black font-mono text-slate-900">{{ number_format($status['local_counts']['products'], 0, ',', ' ') }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Ventes</span>
                <span class="text-lg font-black font-mono text-[#0056a6]">{{ number_format($status['local_counts']['sales'], 0, ',', ' ') }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Courses Livreur</span>
                <span class="text-lg font-black font-mono text-indigo-600">{{ number_format($status['local_counts']['deliveries'], 0, ',', ' ') }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Sessions Caisse</span>
                <span class="text-lg font-black font-mono text-amber-600">{{ number_format($status['local_counts']['sessions'], 0, ',', ' ') }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Mouvements Stock</span>
                <span class="text-lg font-black font-mono text-emerald-600">{{ number_format($status['local_counts']['stock_movements'], 0, ',', ' ') }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Clients</span>
                <span class="text-lg font-black font-mono text-slate-700">{{ number_format($status['local_counts']['customers'], 0, ',', ' ') }}</span>
            </div>
        </div>
    </div>

    <!-- DERNIER RAPPORT DE SYNCHRONISATION -->
    @if(!empty($status['last_sync']))
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Dernière Synchronisation Réussie</h3>
                        <span class="text-xs text-slate-400 font-mono">{{ $status['last_sync']['synced_at'] }} ({{ $status['last_sync']['duration_seconds'] }}s)</span>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Opération Validée
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                @if(!empty($status['last_sync']['push']))
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                        <strong class="text-slate-800 font-bold block text-[11px] uppercase tracking-wider text-emerald-700">Détail PUSH (Envoyé au serveur) :</strong>
                        <div class="grid grid-cols-2 gap-2 text-slate-600">
                            @foreach($status['last_sync']['push'] as $entity => $count)
                                <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                    <span class="capitalize">{{ str_replace('_', ' ', $entity) }} :</span>
                                    <strong class="font-mono text-slate-900">{{ $count }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($status['last_sync']['pull']))
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                        <strong class="text-slate-800 font-bold block text-[11px] uppercase tracking-wider text-indigo-700">Détail PULL (Reçu du serveur) :</strong>
                        <div class="grid grid-cols-2 gap-2 text-slate-600">
                            @foreach($status['last_sync']['pull'] as $entity => $count)
                                <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                    <span class="capitalize">{{ str_replace('_', ' ', $entity) }} :</span>
                                    <strong class="font-mono text-slate-900">{{ $count }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    async function testConnection() {
        const btn = document.getElementById('btn-test-conn');
        const badge = document.getElementById('connection-badge');
        const statusText = document.getElementById('connection-status-text');
        const messageBox = document.getElementById('connection-message');
        const alertBox = document.getElementById('connection-alert-box');
        const latencyContainer = document.getElementById('latency-container');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Test en cours...';

        try {
            const response = await fetch('{{ route('admin.sync.test-connection') }}');
            const data = await response.json();

            if (data.connected) {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                badge.querySelector('span').className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
                statusText.innerText = 'Serveur Distant Connecté';
                alertBox.className = 'mt-3.5 p-3 rounded-xl text-xs flex items-start gap-2 bg-emerald-50/70 border border-emerald-200 text-emerald-800';
                alertBox.querySelector('i').className = 'fa-solid fa-circle-check text-emerald-600 text-sm shrink-0 mt-0.5';
                messageBox.innerText = data.message;
                latencyContainer.innerHTML = `<strong class="text-xs font-mono font-black text-emerald-600">${data.latency_ms} ms</strong>`;

                Swal.fire({
                    icon: 'success',
                    title: 'Serveur Distant Connecté',
                    text: `La connexion à ${data.host} (${data.database}) est opérationnelle avec une latence de ${data.latency_ms}ms.`,
                    confirmButtonColor: '#0056a6'
                });
            } else {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200';
                badge.querySelector('span').className = 'w-2 h-2 rounded-full bg-rose-500';
                statusText.innerText = 'Mode Hors-Ligne Actif';
                alertBox.className = 'mt-3.5 p-3 rounded-xl text-xs flex items-start gap-2 bg-rose-50/70 border border-rose-200 text-rose-800';
                alertBox.querySelector('i').className = 'fa-solid fa-triangle-exclamation text-rose-600 text-sm shrink-0 mt-0.5';
                messageBox.innerText = data.message;
                latencyContainer.innerHTML = `<span class="text-xs text-rose-500 font-bold">Déconnecté</span>`;

                Swal.fire({
                    icon: 'warning',
                    title: 'Mode Hors-Ligne',
                    text: data.message,
                    confirmButtonColor: '#0056a6'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur Réseau',
                text: 'Une erreur est survenue lors du test de connexion.',
                confirmButtonColor: '#0056a6'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-network-wired text-xs"></i> <span>Tester la connexion</span>';
        }
    }

    function startSync(event, form) {
        event.preventDefault();
        
        Swal.fire({
            title: 'Synchronisation en cours...',
            text: 'Transfert des données (PUSH & PULL) avec mysql_remote.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        form.submit();
    }
</script>
@endsection
