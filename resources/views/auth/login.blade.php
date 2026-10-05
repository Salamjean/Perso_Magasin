<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — GestionMAG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN & SweetAlert2 & Assets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0056a6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 16px;
        }
        .login-card {
            width: 100%;
            max-width: 325px; /* Format ultra-compact et élégant */
            background-color: #ffffff;
            border-radius: 24px;
            padding: 24px 22px;
            box-shadow: 0 20px 45px -10px rgba(2, 29, 66, 0.45);
            margin: auto;
        }
        .input-bg {
            background-color: #ebf3fc;
        }
        .btn-primary {
            background-color: #004a99;
        }
        .btn-primary:hover {
            background-color: #003c80;
        }

        /* DESIGN SWEETALERT2 SIMPLE, PROPRE & BORDURES CARRÉES */
        .swal2-container {
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            background: rgba(15, 23, 42, 0.45) !important;
            padding: 16px !important;
        }

        .swal2-popup.modern-swal-popup,
        .swal2-popup {
            border-radius: 0px !important;
            padding: 24px 20px 20px !important;
            background: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 0 0 1px #cbd5e1 !important;
            border: 1px solid #cbd5e1 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            max-width: 380px !important;
            width: 100% !important;
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

        /* INFO & QUESTION */
        .swal2-icon.swal2-info,
        .swal2-icon.swal2-question {
            background-color: #eff6ff !important;
            border-color: #bfdbfe !important;
        }
        .swal2-icon.swal2-info::after,
        .swal2-icon.swal2-question::after {
            content: "\f129" !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            font-size: 1.35rem !important;
            color: #0056a6 !important;
            display: block !important;
            line-height: 1 !important;
        }

        /* TYPOGRAPHIE & BOUTONS */
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

        .swal2-timer-progress-bar {
            background: #0056a6 !important;
            height: 3px !important;
            border-radius: 0px !important;
        }
    </style>
</head>
<body class="antialiased">

    <!-- CARTE DE CONNEXION ULTRA-COMPACTE ET CENTRÉE -->
    <div class="login-card">

        <!-- LOGO DU SUPERMARCHÉ -->
        <div class="flex justify-center mb-3 text-center">
            @php
                $customLogo = \App\Models\Setting::get('store_logo');
                $logoSrc = ($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
                    ? asset('storage/' . $customLogo)
                    : asset('images/logo.png');
            @endphp
            <img src="{{ $logoSrc }}" alt="Logo" class="h-14 max-w-full object-contain mx-auto">
        </div>

        <!-- TITRES -->
        <div class="text-center mb-4">
            <h1 class="text-lg font-black text-[#0f3460] tracking-tight">Connexion</h1>
            <p class="text-[11px] text-slate-400 mt-0.5 font-medium leading-tight">Accédez à votre espace selon votre rôle</p>
        </div>

        <!-- FORMULAIRE COMPACT -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-3">
            @csrf

            <!-- IDENTIFIANT -->
            <div>
                <label for="login" class="block text-[11px] font-bold text-slate-700 mb-1">
                    Identifiant
                </label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-user text-xs"></i>
                    </div>
                    <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus
                        placeholder="Email (Admin) ou Téléphone"
                        class="input-bg w-full pl-8 pr-3 py-2 border border-blue-100 rounded-lg text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0056a6] focus:bg-white transition-all">
                </div>
            </div>

            <!-- MOT DE PASSE -->
            <div>
                <label for="password" class="block text-[11px] font-bold text-slate-700 mb-1">
                    Mot de passe
                </label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </div>
                    <input type="password" name="password" id="password" required placeholder="••••••••"
                        class="input-bg w-full pl-8 pr-8 py-2 border border-blue-100 rounded-lg text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0056a6] focus:bg-white transition-all">
                    
                    <!-- OEIL AFFICHER / MASQUER -->
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" title="Afficher/Masquer">
                        <i id="password-toggle-icon" class="fa-solid fa-eye text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- SE SOUVENIR DE MOI -->
            <div class="flex items-center pt-0.5">
                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded border-slate-300 text-[#0056a6] focus:ring-[#0056a6]">
                    <span class="text-[11px] text-slate-500 font-medium">Se souvenir de moi</span>
                </label>
            </div>

            <!-- BOUTON DE CONNEXION -->
            <div class="pt-1.5">
                <button type="submit"
                    class="btn-primary w-full py-2.5 text-white font-bold text-xs rounded-lg transition duration-150 shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>Se connecter</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </form>

    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        @if($errors->any())
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Erreur de connexion',
                text: "{{ $errors->first() }}",
                showCancelButton: false,
                confirmButtonText: 'Réessayer',
                customClass: {
                    popup: 'modern-swal-popup',
                    title: 'modern-swal-title',
                    htmlContainer: 'modern-swal-text',
                    confirmButton: 'modern-swal-confirm',
                    actions: 'modern-swal-actions'
                }
            });
        @endif

        @if(session('success'))
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Succès',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                showCancelButton: false,
                timer: 2500,
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
                title: 'Accès refusé',
                text: "{{ session('error') }}",
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
    </script>
</body>
</html>
