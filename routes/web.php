<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SyncGlobalController;
use App\Services\SyncService;
use Illuminate\Support\Facades\Route;

// Écran de Démarrage Desktop (Splash Screen) & Synchronisation initiale
Route::get('/splash', function () {
    try {
        app(SyncService::class)->syncAll();
    } catch (Throwable $e) {
        // Mode hors-ligne si distant non joignable
    }

    return view('splash');
})->name('splash');
Route::get('/sync/startup', [SyncGlobalController::class, 'startupSync'])->name('sync.startup');

// Redirection principale
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'magasinier' => redirect()->route('magasinier.dashboard'),
            'caissier' => redirect()->route('caissier.dashboard'),
            'livreur' => redirect()->route('livreur.dashboard'),
            default => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

// Authentification & Profil
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Synchronisation globale (Mode Hors-ligne & Détection automatique)
    Route::get('/sync/live-status', [SyncGlobalController::class, 'status'])->name('sync.live-status');
    Route::post('/sync/trigger-auto', [SyncGlobalController::class, 'triggerAuto'])->name('sync.trigger-auto');
});

// Inclusion des fichiers de routes spécifiques par utilisateur
require __DIR__.'/admin.php';
require __DIR__.'/magasinier.php';
require __DIR__.'/caissier.php';
require __DIR__.'/livreur.php';
