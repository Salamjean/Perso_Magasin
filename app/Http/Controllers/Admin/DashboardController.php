<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashRegister;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Chiffres d'affaires
        $caToday = Sale::where('status', 'completed')->whereDate('created_at', $today)->sum('total_amount');
        $caWeek = Sale::where('status', 'completed')->where('created_at', '>=', $startOfWeek)->sum('total_amount');
        $caMonth = Sale::where('status', 'completed')->where('created_at', '>=', $startOfMonth)->sum('total_amount');

        // Ventes
        $salesCountToday = Sale::where('status', 'completed')->whereDate('created_at', $today)->count();
        $totalSalesCount = Sale::where('status', 'completed')->count();

        // Produits et stocks
        $totalProducts = Product::count();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();
        $lowStockCount = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'alert_threshold')
            ->count();

        // Commandes & Livraisons
        $purchasesCount = Purchase::count();
        $deliveriesCount = Delivery::where('status', '!=', 'cancelled')->count();
        $pendingDeliveries = Delivery::whereIn('status', ['pending', 'preparing', 'ready', 'assigned', 'in_transit'])->count();

        // Dépenses & Bénéfice estimé
        $totalExpensesMonth = Expense::where('created_at', '>=', $startOfMonth)->sum('amount');

        // Coût des marchandises vendues ce mois pour calculer la marge brute
        $salesItemsMonth = SaleItem::whereHas('sale', function ($query) use ($startOfMonth) {
            $query->where('status', 'completed')->where('created_at', '>=', $startOfMonth);
        })->with('product')->get();

        $cogsMonth = 0;
        foreach ($salesItemsMonth as $item) {
            $buyPrice = $item->product ? $item->product->buy_price : 0;
            $cogsMonth += ($buyPrice * $item->quantity);
        }
        $estimatedProfitMonth = max(0, $caMonth - $cogsMonth - $totalExpensesMonth);

        // État des caisses
        $cashRegisters = CashRegister::with(['currentSession.user'])->get();

        // Graphique 1: Ventes des 7 derniers jours
        $salesLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayLabel = $date->translatedFormat('D d M');
            $dayTotal = Sale::where('status', 'completed')->whereDate('created_at', $date)->sum('total_amount');
            $salesLast7Days[] = [
                'date' => $dayLabel,
                'total' => (float) $dayTotal,
            ];
        }

        // Graphique 2: Top 3 produits les plus vendus
        $topProducts = SaleItem::selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        // Dernières ventes
        $recentSales = Sale::with(['user', 'customer'])->latest()->limit(5)->get();

        // Dernières activités (4 derniers logs)
        $recentActivities = ActivityLog::latest()->limit(4)->get();

        return view('admin.dashboard.index', compact(
            'caToday',
            'caWeek',
            'caMonth',
            'salesCountToday',
            'totalSalesCount',
            'totalProducts',
            'outOfStockCount',
            'lowStockCount',
            'purchasesCount',
            'deliveriesCount',
            'pendingDeliveries',
            'totalExpensesMonth',
            'estimatedProfitMonth',
            'cashRegisters',
            'salesLast7Days',
            'topProducts',
            'recentSales',
            'recentActivities'
        ));
    }
}
