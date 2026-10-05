<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') — GestionMAG</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js & SweetAlert2 & CDN Icons & Tailwind CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* TRANSITION FLUIDE ET MODERNE DE LA SIDEBAR (DESKTOP ET MOBILE) */
        #main-sidebar {
            width: 16rem; /* 256px standard */
            min-width: 16rem;
            max-width: 16rem;
            transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1), 
                        min-width 0.35s cubic-bezier(0.16, 1, 0.3, 1), 
                        max-width 0.35s cubic-bezier(0.16, 1, 0.3, 1), 
                        transform 0.38s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.3s ease;
            will-change: width, transform;
        }

        .sidebar-text {
            transition: opacity 0.25s ease, max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s ease;
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
            max-width: 160px;
            opacity: 1;
            transform: translateX(0);
        }

        /* ÉTAT RÉDUIT (COLLAPSED) */
        #main-sidebar.sidebar-collapsed {
            width: 5rem !important; /* 80px */
            min-width: 5rem !important;
            max-width: 5rem !important;
        }

        #main-sidebar.sidebar-collapsed .sidebar-text {
            max-width: 0 !important;
            opacity: 0 !important;
            transform: translateX(-8px);
            margin: 0 !important;
            padding: 0 !important;
        }

        #main-sidebar.sidebar-collapsed #sidebar-clock-container {
            display: none !important;
        }

        #main-sidebar.sidebar-collapsed .sidebar-logo-img {
            height: 2rem !important;
            max-height: 2rem !important;
            max-width: 2.75rem !important;
            object-fit: contain;
        }

        #main-sidebar.sidebar-collapsed nav a {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            gap: 0 !important;
        }

        #main-sidebar.sidebar-collapsed nav a i {
            margin: 0 auto;
        }

        /* DESIGN SWEETALERT2 SIMPLE & PROPRE POUR LES MODALES DE CONFIRMATION (AU CENTRE) */
        .swal2-container:not(.swal2-toast-shown):not(.swal2-top-end):not(.swal2-top-right):not(.swal2-bottom-end):not(.swal2-bottom-right) {
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            background: rgba(15, 23, 42, 0.45) !important;
            padding: 16px !important;
        }

        .swal2-popup.modern-swal-popup,
        .swal2-popup:not(.swal2-toast) {
            border-radius: 0px !important;
            padding: 24px 20px 20px !important;
            background: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 0 0 1px #cbd5e1 !important;
            border: 1px solid #cbd5e1 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            max-width: 380px !important;
            width: 100% !important;
        }

        /* TOASTS DISCRETS & PETITS : STRICTEMENT AUCUN FOND GRISÉ / AUCUN BACKDROP */
        .swal2-toast-shown .swal2-container,
        .swal2-container.swal2-top-end,
        .swal2-container.swal2-top-right,
        .swal2-container.swal2-bottom-end,
        .swal2-container.swal2-bottom-right {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            pointer-events: none !important;
            padding: 12px !important;
        }

        .swal2-popup.swal2-toast {
            pointer-events: auto !important;
            background: #ffffff !important;
            border-radius: 12px !important;
            padding: 6px 12px !important;
            max-width: 260px !important;
            width: auto !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 86, 166, 0.15) !important;
            border: 1px solid #e2e8f0 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .swal2-popup.swal2-toast .swal2-title {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
        }

        .swal2-popup.swal2-toast .swal2-icon {
            width: 20px !important;
            height: 20px !important;
            min-width: 20px !important;
            min-height: 20px !important;
            margin: 0 !important;
            border-radius: 6px !important;
        }

        .swal2-popup.swal2-toast .swal2-icon::after {
            font-size: 0.7rem !important;
        }

        /* ICONS PROPRES, PARFAITEMENT CENTRÉES & CARRÉES AVEC FONTAWESOME */
        .swal2-icon {
            width: 48px !important;
            height: 48px !important;
            margin: 0 auto 14px !important;
            border-radius: 0px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid transparent !important;
            box-sizing: border-box !important;
            position: relative !important;
        }

        /* Masquer les éléments SVG et lignes internes complexes par défaut */
        .swal2-icon * {
            display: none !important;
        }

        /* SUCCESS */
        .swal2-icon.swal2-success {
            background-color: #ecfdf5 !important;
            border-color: #a7f3d0 !important;
        }
        .swal2-icon.swal2-success::after {
            content: "\f00c" !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-size: 1.35rem !important;
            color: #059669 !important;
            display: block !important;
            line-height: 1 !important;
        }

        /* ERROR */
        .swal2-icon.swal2-error {
            background-color: #fff1f2 !important;
            border-color: #fecdd3 !important;
        }
        .swal2-icon.swal2-error::after {
            content: "\f00d" !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-size: 1.35rem !important;
            color: #e11d48 !important;
            display: block !important;
            line-height: 1 !important;
        }

        /* WARNING */
        .swal2-icon.swal2-warning {
            background-color: #fffbeb !important;
            border-color: #fde68a !important;
        }
        .swal2-icon.swal2-warning::after {
            content: "\f12a" !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-size: 1.35rem !important;
            color: #d97706 !important;
            display: block !important;
            line-height: 1 !important;
        }

        /* INFO */
        .swal2-icon.swal2-info {
            background-color: #eff6ff !important;
            border-color: #bfdbfe !important;
        }
        .swal2-icon.swal2-info::after {
            content: "\f129" !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-size: 1.35rem !important;
            color: #0056a6 !important;
            display: block !important;
            line-height: 1 !important;
        }

        /* QUESTION */
        .swal2-icon.swal2-question {
            background-color: #eff6ff !important;
            border-color: #bfdbfe !important;
        }
        .swal2-icon.swal2-question::after {
            content: "\f128" !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-size: 1.35rem !important;
            color: #0056a6 !important;
            display: block !important;
            line-height: 1 !important;
        }

        /* TYPOGRAPHIE SOBRE ET ÉPURÉE */
        .swal2-title,
        .swal2-title.modern-swal-title {
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            padding: 0 !important;
            margin: 0 0 6px !important;
            line-height: 1.35 !important;
        }

        .swal2-html-container,
        .swal2-html-container.modern-swal-text {
            font-size: 0.8125rem !important;
            color: #475569 !important;
            font-weight: 500 !important;
            margin: 0 0 20px !important;
            line-height: 1.5 !important;
            padding: 0 !important;
        }

        /* GESTION STRICTE DE LA VISIBILITÉ DES ÉLÉMENTS SWEETALERT2 */
        .swal2-hidden,
        .swal2-popup [style*="display: none"],
        .swal2-actions[style*="display: none"],
        .swal2-styled[style*="display: none"] {
            display: none !important;
        }

        /* BOUTONS D'ACTION CARRÉS */
        .swal2-actions:not([style*="display: none"]) {
            margin: 0 !important;
            gap: 8px !important;
            width: 100% !important;
            display: flex !important;
            justify-content: stretch !important;
        }

        .swal2-styled.swal2-confirm:not([style*="display: none"]),
        .swal2-styled.swal2-confirm.modern-swal-confirm:not([style*="display: none"]) {
            flex: 1 !important;
            background-color: #0056a6 !important;
            color: #ffffff !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            padding: 10px 16px !important;
            border-radius: 0px !important;
            box-shadow: none !important;
            transition: all 0.15s ease !important;
            margin: 0 !important;
            border: 1px solid #0056a6 !important;
            outline: none !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .swal2-styled.swal2-confirm:hover,
        .swal2-styled.swal2-confirm.modern-swal-confirm:hover {
            background-color: #004485 !important;
            border-color: #004485 !important;
        }

        .swal2-styled.swal2-confirm.modern-swal-danger {
            background-color: #e11d48 !important;
            border-color: #e11d48 !important;
        }

        .swal2-styled.swal2-confirm.modern-swal-danger:hover {
            background-color: #be123c !important;
            border-color: #be123c !important;
        }

        .swal2-styled.swal2-cancel:not([style*="display: none"]),
        .swal2-styled.swal2-cancel.modern-swal-cancel:not([style*="display: none"]) {
            flex: 1 !important;
            background-color: #ffffff !important;
            color: #475569 !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            padding: 10px 16px !important;
            border-radius: 0px !important;
            transition: all 0.15s ease !important;
            margin: 0 !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            outline: none !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .swal2-styled.swal2-cancel:hover,
        .swal2-styled.swal2-cancel.modern-swal-cancel:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }

        .swal2-timer-progress-bar {
            background: #0056a6 !important;
            height: 3px !important;
            border-radius: 0px !important;
        }
    </style>
</head>
<body class="h-full flex overflow-hidden bg-slate-100 text-slate-800 antialiased">

    <!-- 1. SIDEBAR SÉPARÉE -->
    @include('layouts.sidebar')

    <!-- 2. CONTENU PRINCIPAL (NAVBAR + MAIN PAGE) -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- NAVBAR BLEUE FLOTTANTE -->
        @include('layouts.navbar')

        <!-- ZONE DE CONTENU PRINCIPALE (RESPONSIVE) -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    <!-- SWEETALERT2 NOTIFICATIONS & CONFIRMATIONS GLOBALES -->
    <script>
        // NOTIFICATIONS DE SESSION AUTOMATIQUES (AU MILIEU DE L'ÉCRAN - DESIGN SOIGNÉ)
        @if(session('success'))
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Opération réussie',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                showCancelButton: false,
                timer: 2400,
                timerProgressBar: true,
                customClass: {
                    popup: 'modern-swal-popup',
                    title: 'modern-swal-title',
                    htmlContainer: 'modern-swal-text'
                }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Erreur',
                text: "{{ session('error') }}",
                showCancelButton: false,
                confirmButtonText: 'D\'accord',
                customClass: {
                    popup: 'modern-swal-popup',
                    title: 'modern-swal-title',
                    htmlContainer: 'modern-swal-text',
                    confirmButton: 'modern-swal-confirm',
                    actions: 'modern-swal-actions'
                }
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                position: 'center',
                icon: 'warning',
                title: 'Attention',
                text: "{{ session('warning') }}",
                showCancelButton: false,
                confirmButtonText: 'Compris',
                customClass: {
                    popup: 'modern-swal-popup',
                    title: 'modern-swal-title',
                    htmlContainer: 'modern-swal-text',
                    confirmButton: 'modern-swal-confirm',
                    actions: 'modern-swal-actions'
                }
            });
        @endif

        @if(session('info'))
            Swal.fire({
                position: 'center',
                icon: 'info',
                title: 'Information',
                text: "{{ session('info') }}",
                showConfirmButton: false,
                showCancelButton: false,
                timer: 2800,
                timerProgressBar: true,
                customClass: {
                    popup: 'modern-swal-popup',
                    title: 'modern-swal-title',
                    htmlContainer: 'modern-swal-text'
                }
            });
        @endif

        // FONCTION UTILITAIRE GLOBALE DE CONFIRMATION AVEC SWEETALERT2 (AU MILIEU DE L'ÉCRAN)
        window.confirmAction = function(formOrCallback, options = {}) {
            const title = options.title || 'Êtes-vous certain ?';
            const text = options.text || 'Voulez-vous vraiment effectuer cette action ?';
            const confirmText = options.confirmText || 'Confirmer';
            const icon = options.icon || 'warning';
            
            const isDanger = options.isDanger || 
                             /supprimer|bloquer|annuler|suppression|retirer/i.test(title + ' ' + text + ' ' + confirmText);

            Swal.fire({
                position: 'center',
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Annuler',
                reverseButtons: true,
                customClass: {
                    popup: 'modern-swal-popup',
                    title: 'modern-swal-title',
                    htmlContainer: 'modern-swal-text',
                    confirmButton: isDanger ? 'modern-swal-confirm modern-swal-danger' : 'modern-swal-confirm',
                    cancelButton: 'modern-swal-cancel',
                    actions: 'modern-swal-actions'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof formOrCallback === 'function') {
                        formOrCallback();
                    } else if (formOrCallback && formOrCallback.submit) {
                        formOrCallback.submit();
                    }
                }
            });
        };

        // INTERCEPTION AUTOMATIQUE DES FORMULAIRES DATA-CONFIRM
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[data-confirm]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (form.dataset.confirmed === 'true') return;
                    e.preventDefault();
                    confirmAction(() => {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }, {
                        title: form.getAttribute('data-confirm-title') || 'Confirmation',
                        text: form.getAttribute('data-confirm'),
                        confirmText: form.getAttribute('data-confirm-btn') || 'Confirmer'
                    });
                });
            });
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
