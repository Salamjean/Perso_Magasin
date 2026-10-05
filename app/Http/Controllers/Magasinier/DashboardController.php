<?php

namespace App\Http\Controllers\Magasinier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalStockUnits = Product::where('is_active', true)->sum('stock_quantity');

        // Ruptures et alertes
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();
        $lowStockProducts = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'alert_threshold')
            ->count();

        // Calcul du taux de santé du stock
        $healthyProductsCount = max(0, $totalProducts - ($outOfStockProducts + $lowStockProducts));
        $stockHealthRate = $totalProducts > 0 ? round(($healthyProductsCount / $totalProducts) * 100) : 100;

        // Commandes fournisseurs en attente de réception
        $pendingPurchasesCount = Purchase::whereIn('status', ['ordered', 'partial'])->count();
        $pendingPurchases = Purchase::with('supplier')
            ->whereIn('status', ['ordered', 'partial'])
            ->latest()
            ->limit(4)
            ->get();

        // Derniers mouvements de stock (limité aux 5 derniers)
        $recentMovements = StockMovement::with(['product.category', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        // Produits critiques (alertes immédiates)
        $criticalProducts = Product::where(function ($q) {
            $q->where('stock_quantity', '<=', 0)
                ->orWhereColumn('stock_quantity', '<=', 'alert_threshold');
        })->with('category')->orderBy('stock_quantity', 'asc')->limit(5)->get();

        return view('magasinier.dashboard.index', compact(
            'totalProducts',
            'totalStockUnits',
            'outOfStockProducts',
            'lowStockProducts',
            'stockHealthRate',
            'pendingPurchasesCount',
            'pendingPurchases',
            'recentMovements',
            'criticalProducts'
        ));
    }
}
