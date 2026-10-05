<?php

use App\Http\Controllers\Admin\CashRegisterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1b. Hub de Synchronisation & Mode Hors-Ligne (mysql_remote)
    Route::get('sync', [SyncController::class, 'index'])->name('sync.index');
    Route::get('sync/test-connection', [SyncController::class, 'testConnection'])->name('sync.test-connection');
    Route::post('sync/pull', [SyncController::class, 'pull'])->name('sync.pull');
    Route::post('sync/push', [SyncController::class, 'push'])->name('sync.push');
    Route::post('sync/all', [SyncController::class, 'syncAll'])->name('sync.all');

    // 2. Utilisateurs (Suppression désactivée, blocage uniquement)
    Route::resource('users', UserController::class)->except(['destroy']);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // 3. Produits
    Route::get('products/{product}/download-barcode', [ProductController::class, 'downloadBarcode'])->name('products.download-barcode');
    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');

    // 4. Catégories
    Route::resource('categories', CategoryController::class);

    // 5. Fournisseurs (Suppression désactivée, masquage/réactivation)
    Route::resource('suppliers', SupplierController::class)->except(['destroy']);
    Route::patch('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

    // 6. Achats / Approvisionnements & Réceptions
    Route::get('purchases/{purchase}/reception', [PurchaseController::class, 'reception'])->name('purchases.reception');
    Route::post('purchases/{purchase}/reception', [PurchaseController::class, 'processReception'])->name('purchases.reception.process');
    Route::resource('purchases', PurchaseController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('purchases/{purchase}/status', [PurchaseController::class, 'updateStatus'])->name('purchases.status');

    // 6b. Mouvements de Stock (Entrées, Sorties, Historique)
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('stock/entry', [StockController::class, 'entry'])->name('stock.entry');
    Route::post('stock/entry', [StockController::class, 'processEntry'])->name('stock.entry.process');
    Route::get('stock/exit', [StockController::class, 'exit'])->name('stock.exit');
    Route::post('stock/exit', [StockController::class, 'processExit'])->name('stock.exit.process');

    // 6c. Inventaires physiques & Ajustements
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');

    // 7. Ventes
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

    // 8. Caisses
    Route::resource('cash-registers', CashRegisterController::class)->only(['index', 'store', 'show']);
    Route::get('cash-sessions/{session}', [CashRegisterController::class, 'showSession'])->name('cash-sessions.show');
    Route::post('cash-sessions/{session}/movement', [CashRegisterController::class, 'addMovement'])->name('cash-sessions.movement');

    // 9. Clients
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/debt', [CustomerController::class, 'adjustDebt'])->name('customers.debt');

    // 10. Livraisons
    Route::resource('deliveries', DeliveryController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('deliveries/{delivery}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');
    Route::patch('deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('deliveries.status');

    // 11. Dépenses
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy']);

    // 12. Rapports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // 13. Paramètres
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
