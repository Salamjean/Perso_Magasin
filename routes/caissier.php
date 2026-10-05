<?php

use App\Http\Controllers\Caissier\DashboardController;
use App\Http\Controllers\Caissier\DeliveryController;
use App\Http\Controllers\Caissier\PosController;
use App\Http\Controllers\Caissier\ReturnController;
use App\Http\Controllers\Caissier\SaleController;
use App\Http\Controllers\Caissier\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:caissier,admin'])->prefix('caissier')->name('caissier.')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Point de Vente / TPV (POS)
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/search', [PosController::class, 'searchProduct'])->name('pos.search');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');

    // 3. Gestion des sessions de caisse
    Route::get('session', [SessionController::class, 'show'])->name('session.status');
    Route::get('session/open', [SessionController::class, 'openForm'])->name('session.open');
    Route::post('session/open', [SessionController::class, 'open'])->name('session.open.process');
    Route::get('session/close', [SessionController::class, 'closeForm'])->name('session.close');
    Route::post('session/close', [SessionController::class, 'close'])->name('session.close.process');

    // 4. Ventes du caissier
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

    // 5. Retours & Annulations directes
    Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('returns/cancel', [ReturnController::class, 'cancelSale'])->name('returns.cancel');
    Route::post('returns/request', [ReturnController::class, 'cancelSale'])->name('returns.request');

    // 6. Programmation & Suivi des Livraisons
    Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
    Route::post('deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::get('deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('deliveries/{delivery}/cancel', [DeliveryController::class, 'cancel'])->name('deliveries.cancel');
});
