<?php

use App\Http\Controllers\Livreur\DashboardController;
use App\Http\Controllers\Livreur\DeliveryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:livreur,admin'])->prefix('livreur')->name('livreur.')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Gestion des livraisons
    Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::patch('deliveries/{delivery}/start', [DeliveryController::class, 'start'])->name('deliveries.start');
    Route::post('deliveries/{delivery}/validate', [DeliveryController::class, 'validateDelivery'])->name('deliveries.validate');
    Route::post('deliveries/{delivery}/failure', [DeliveryController::class, 'reportFailure'])->name('deliveries.failure');
    Route::post('deliveries/{delivery}/fail', [DeliveryController::class, 'reportFailure'])->name('deliveries.fail');
});
