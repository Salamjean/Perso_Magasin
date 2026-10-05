<?php

use App\Http\Controllers\Magasinier\AlertController;
use App\Http\Controllers\Magasinier\DashboardController;
use App\Http\Controllers\Magasinier\InventoryController;
use App\Http\Controllers\Magasinier\ProductController;
use App\Http\Controllers\Magasinier\ReceptionController;
use App\Http\Controllers\Magasinier\StockController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:magasinier,admin'])->prefix('magasinier')->name('magasinier.')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Gestion du stock (Entrées, Sorties, Mouvements)
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('stock/entry', [StockController::class, 'entry'])->name('stock.entry');
    Route::post('stock/entry', [StockController::class, 'processEntry'])->name('stock.entry.process');
    Route::get('stock/exit', [StockController::class, 'exit'])->name('stock.exit');
    Route::post('stock/exit', [StockController::class, 'processExit'])->name('stock.exit.process');

    // 3. Inventaires
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');

    // 4. Réception des commandes fournisseurs
    Route::get('receptions', [ReceptionController::class, 'index'])->name('receptions.index');
    Route::get('receptions/{purchase}', [ReceptionController::class, 'process'])->name('receptions.process');
    Route::post('receptions/{purchase}/validate', [ReceptionController::class, 'validateReception'])->name('receptions.validate');

    // 5. Catalogue & Fiches produits
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

    // 6. Alertes (ruptures, stock faible, expiration)
    Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');
});
