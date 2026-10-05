<?php

namespace App\Http\Controllers\Magasinier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(): View
    {
        // 1. Ruptures totales (stock <= 0)
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
            ->with(['category', 'supplier'])
            ->get();

        // 2. Stocks faibles (0 < stock <= seuil)
        $lowStockProducts = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'alert_threshold')
            ->with(['category', 'supplier'])
            ->get();

        // 3. Expirations proches (dans les 30 jours)
        $expiringSoonProducts = Product::whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<=', Carbon::now()->addDays(30))
            ->whereDate('expiration_date', '>=', Carbon::now())
            ->with(['category', 'supplier'])
            ->get();

        // 4. Produits déjà expirés
        $expiredProducts = Product::whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', Carbon::now())
            ->with(['category', 'supplier'])
            ->get();

        return view('magasinier.alerts.index', compact(
            'outOfStockProducts',
            'lowStockProducts',
            'expiringSoonProducts',
            'expiredProducts'
        ));
    }
}
