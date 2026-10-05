<!DOCTYPE html>
<html lang="fr" class="h-full bg-[#0056a6]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestMagasin - Démarrage</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            user-select: none;
            overflow: hidden;
            background-color: #0056a6;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center bg-[#0056a6] p-4 text-slate-800">

    <!-- CONTENEUR DU SPLASH SCREEN (CARTE BLANCHE SUR FOND BLEU UNI #0056a6) -->
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 sm:p-10 flex flex-col items-center text-center border border-blue-100 relative">
        
        <!-- LOGO DU MAGASIN -->
        <div class="w-28 h-28 sm:w-32 sm:h-32 mb-5 rounded-2xl bg-blue-50 border border-blue-100 p-3 flex items-center justify-center shadow-xs">
            <img src="{{ asset('images/logo.png') }}" alt="Logo GestMagasin" class="w-full h-full object-contain">
        </div>

        <!-- TITRE & SOUS-TITRE -->
        <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-1">
            GESTION <span class="text-[#0056a6]">MAGASIN</span>
        </h1>
        <p class="text-xs font-semibold text-slate-500 mb-8 max-w-[300px]">
            Système de Caisse TPV & Gestion Commerciale
        </p>

        <!-- BARRE DE CHARGEMENT & STATUT EN DIRECT -->
        <div class="w-full space-y-2.5">
            <div class="flex items-center justify-between text-xs font-bold text-slate-700 px-0.5">
                <span id="splash-status-text" class="flex items-center gap-2 truncate text-slate-600">
                    <i class="fa-solid fa-circle-notch fa-spin text-[#0056a6]"></i>
                    <span>Démarrage de l'application...</span>
                </span>
                <span id="splash-percentage" class="font-mono text-[#0056a6] font-extrabold">0%</span>
            </div>

            <!-- CONTENEUR BARRE DE PROGRESSION -->
            <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200">
                <div id="splash-progress-bar" 
                     class="h-full bg-[#0056a6] rounded-full transition-all duration-300 ease-out"
                     style="width: 0%"></div>
            </div>
        </div>

        <!-- PIED DE CARTE (STATUT & VERSION) -->
        <div class="mt-8 pt-5 border-t border-slate-100 w-full flex items-center justify-between text-[11px] text-slate-400">
            <div class="flex items-center gap-1.5 font-medium text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Mode Hybride Actif</span>
            </div>
            
            <span class="font-mono font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">v1.0 Desktop</span>
        </div>

    </div>

    <!-- SCRIPT DE SYNCHRONISATION AUTOMATIQUE & PROGRESSION EN DIRECT -->
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const progressBar = document.getElementById('splash-progress-bar');
            const percentageEl = document.getElementById('splash-percentage');
            const statusText = document.getElementById('splash-status-text');

            function setProgress(percent, message) {
                if (progressBar) progressBar.style.width = percent + '%';
                if (percentageEl) percentageEl.textContent = percent + '%';
                if (statusText) {
                    statusText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-[#0056a6]"></i> <span>' + message + '</span>';
                }
            }

            // Étape 1 : Initialisation locale
            setProgress(25, "Initialisation de la base locale...");
            await new Promise(r => setTimeout(r, 400));

            // Étape 2 : Synchronisation avec MySQL distant
            setProgress(55, "Connexion et synchronisation MySQL distant...");

            try {
                // Utilisation du chemin relatif ou URL absolue courante
                const startupUrl = window.location.origin + "/sync/startup";
                const response = await fetch(startupUrl, {
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.is_online) {
                        setProgress(85, "Catalogue & utilisateurs synchronisés !");
                    } else {
                        setProgress(85, "Mode Hors-ligne (Données locales prêtes)");
                    }
                } else {
                    setProgress(85, "Mode local prêt.");
                }
            } catch (err) {
                console.warn('[Splash] Note de synchronisation :', err);
                setProgress(85, "Démarrage en mode autonome...");
            }

            await new Promise(r => setTimeout(r, 400));

            // Étape 3 : Fin et ouverture de l'application
            setProgress(100, "Ouverture de l'application...");
            await new Promise(r => setTimeout(r, 350));

            window.location.href = window.location.origin + "/login";
        });
    </script>
</body>
</html>
