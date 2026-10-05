<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // 1. Synthèse Ventes
        $sales = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->with(['user', 'customer'])
            ->get();

        $totalSalesAmount = (float) $sales->sum('total_amount');
        $totalSalesCount = $sales->count();
        $averageBasket = $totalSalesCount > 0 ? $totalSalesAmount / $totalSalesCount : 0;
        $totalDiscounts = (float) $sales->sum('discount');

        // Ventilation par mode de règlement
        $paymentBreakdown = [
            'cash' => (float) $sales->where('payment_method', 'cash')->sum('total_amount'),
            'mobile_money' => (float) $sales->where('payment_method', 'mobile_money')->sum('total_amount'),
            'card' => (float) $sales->where('payment_method', 'card')->sum('total_amount'),
            'transfer' => (float) $sales->where('payment_method', 'transfer')->sum('total_amount'),
        ];

        // 2. Synthèse Achats / Approvisionnements
        $purchases = Purchase::whereBetween('order_date', [$startDate, $endDate])->with('supplier')->get();
        $totalPurchasesAmount = (float) $purchases->sum('total_amount');
        $purchasesCount = $purchases->count();

        // 3. Dépenses d'exploitation
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->get();
        $totalExpensesAmount = (float) $expenses->sum('amount');

        // 4. Marge et Rentabilité
        $saleItems = SaleItem::whereHas('sale', function ($q) use ($startDateTime, $endDateTime) {
            $q->where('status', 'completed')
                ->whereBetween('created_at', [$startDateTime, $endDateTime]);
        })->with('product.category')->get();

        $totalCogs = 0;
        foreach ($saleItems as $item) {
            $buyPrice = $item->product ? (float) $item->product->buy_price : 0;
            $totalCogs += ($buyPrice * $item->quantity);
        }
        $grossMargin = $totalSalesAmount - $totalCogs;
        $marginRate = $totalSalesAmount > 0 ? ($grossMargin / $totalSalesAmount) * 100 : 0;
        $netProfit = $grossMargin - $totalExpensesAmount;

        // 5. Top Produits les plus vendus
        $topProducts = $saleItems->groupBy('product_id')->map(function ($items) {
            $first = $items->first();
            $qty = $items->sum('quantity');
            $revenue = $items->sum('total_price');

            return [
                'product_id' => $first->product_id,
                'name' => $first->product_name ?? ($first->product->name ?? 'Article supprimé'),
                'category' => $first->product->category->name ?? 'Sans rayon',
                'category_color' => $first->product->category->color ?? '#0056a6',
                'total_qty' => $qty,
                'total_revenue' => $revenue,
            ];
        })->sortByDesc('total_qty')->take(5)->values();

        // 6. Ventes par Rayon / Catégorie
        $salesByCategory = $saleItems->groupBy(function ($item) {
            return $item->product->category->name ?? 'Sans catégorie';
        })->map(function ($items, $categoryName) use ($totalSalesAmount) {
            $revenue = $items->sum('total_price');
            $qty = $items->sum('quantity');
            $first = $items->first();
            $color = $first->product->category->color ?? '#0056a6';
            $percentage = $totalSalesAmount > 0 ? ($revenue / $totalSalesAmount) * 100 : 0;

            return [
                'category_name' => $categoryName,
                'color' => $color,
                'total_qty' => $qty,
                'total_revenue' => $revenue,
                'percentage' => round($percentage, 1),
            ];
        })->sortByDesc('total_revenue')->values();

        // 7. Performance des Caissiers
        $cashierPerformance = $sales->groupBy('user_id')->map(function ($cashierSales) {
            $cashier = $cashierSales->first()->user;

            return [
                'name' => $cashier->full_name ?? 'Caissier',
                'sales_count' => $cashierSales->count(),
                'total_amount' => $cashierSales->sum('total_amount'),
            ];
        })->sortByDesc('total_amount')->values();

        // 8. Statistiques et Valorisation des Stocks
        $totalStockValue = (float) Product::sum(DB::raw('stock_quantity * buy_price'));
        $totalStockSellValue = (float) Product::sum(DB::raw('stock_quantity * sell_price'));
        $totalStockItems = (int) Product::count();
        $outOfStock = Product::where('stock_quantity', '<=', 0)->count();
        $lowStock = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'alert_threshold')
            ->count();

        // 9. Créances et Dettes Clients
        $totalCustomerDebt = (float) Customer::sum('debt_balance');
        $customersInDebtCount = Customer::where('debt_balance', '>', 0)->count();

        // 10. Livraisons
        $deliveries = Delivery::whereBetween('created_at', [$startDateTime, $endDateTime])->get();
        $deliveredCount = $deliveries->where('status', 'delivered')->count();
        $inTransitCount = $deliveries->whereIn('status', ['assigned', 'in_transit'])->count();
        $cancelledDeliveriesCount = $deliveries->whereIn('status', ['failed', 'cancelled'])->count();

        return view('admin.reports.index', compact(
            'startDate',
            'endDate',
            'totalSalesAmount',
            'totalSalesCount',
            'averageBasket',
            'totalDiscounts',
            'paymentBreakdown',
            'totalPurchasesAmount',
            'purchasesCount',
            'totalExpensesAmount',
            'totalCogs',
            'grossMargin',
            'marginRate',
            'netProfit',
            'topProducts',
            'salesByCategory',
            'cashierPerformance',
            'totalStockValue',
            'totalStockSellValue',
            'totalStockItems',
            'outOfStock',
            'lowStock',
            'totalCustomerDebt',
            'customersInDebtCount',
            'deliveries',
            'deliveredCount',
            'inTransitCount',
            'cancelledDeliveriesCount'
        ));
    }
}
