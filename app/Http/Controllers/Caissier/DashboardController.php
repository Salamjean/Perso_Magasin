<?php

namespace App\Http\Controllers\Caissier;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Session de caisse active
        $activeSession = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->with(['cashRegister', 'movements'])
            ->latest()
            ->first();

        // Calculs spécifiques à la session active si ouverte
        $sessionTotalSales = 0;
        $sessionCashSales = 0;
        $sessionCreditSales = 0;
        $sessionCreditCount = 0;
        $sessionSalesCount = 0;
        $theoreticalDrawerAmount = 0;

        if ($activeSession) {
            $sessionSalesQuery = Sale::where('cash_session_id', $activeSession->id)
                ->where('status', 'completed');

            $sessionSalesCount = (clone $sessionSalesQuery)->count();
            $sessionTotalSales = (clone $sessionSalesQuery)->sum('total_amount');
            $sessionCashSales = (clone $sessionSalesQuery)->where('payment_method', 'cash')->sum('total_amount');
            $sessionCreditSales = (clone $sessionSalesQuery)->where('payment_method', 'credit')->sum('total_amount');
            $sessionCreditCount = (clone $sessionSalesQuery)->where('payment_method', 'credit')->count();

            // Tiroir théorique en espèces (Fond initial + Espèces encaissées + Entrées - Sorties)
            $theoreticalDrawerAmount = (float) ($activeSession->closing_amount_theory ?? $activeSession->opening_amount);
        }

        // Ventes du jour du caissier
        $todaySales = Sale::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('created_at', $today);

        $salesCountToday = (clone $todaySales)->count();
        $totalCollectedToday = (clone $todaySales)->sum('total_amount');
        $averageBasket = $salesCountToday > 0 ? (int) round($totalCollectedToday / $salesCountToday) : 0;

        // Répartition par mode de paiement aujourd'hui
        $cashTotal = Sale::where('user_id', $user->id)->where('status', 'completed')->whereDate('created_at', $today)->where('payment_method', 'cash')->sum('total_amount');
        $mobileMoneyTotal = Sale::where('user_id', $user->id)->where('status', 'completed')->whereDate('created_at', $today)->where('payment_method', 'mobile_money')->sum('total_amount');
        $cardTotal = Sale::where('user_id', $user->id)->where('status', 'completed')->whereDate('created_at', $today)->where('payment_method', 'card')->sum('total_amount');
        $creditTotal = Sale::where('user_id', $user->id)->where('status', 'completed')->whereDate('created_at', $today)->where('payment_method', 'credit')->sum('total_amount');
        $creditCountToday = Sale::where('user_id', $user->id)->where('status', 'completed')->whereDate('created_at', $today)->where('payment_method', 'credit')->count();
        $otherTotal = Sale::where('user_id', $user->id)->where('status', 'completed')->whereDate('created_at', $today)->whereNotIn('payment_method', ['cash', 'mobile_money', 'card', 'credit'])->sum('total_amount');

        // Pourcentages de répartition
        $cashPercentage = $totalCollectedToday > 0 ? round(($cashTotal / $totalCollectedToday) * 100) : 0;
        $mobileMoneyPercentage = $totalCollectedToday > 0 ? round(($mobileMoneyTotal / $totalCollectedToday) * 100) : 0;
        $cardPercentage = $totalCollectedToday > 0 ? round(($cardTotal / $totalCollectedToday) * 100) : 0;
        $creditPercentage = $totalCollectedToday > 0 ? round(($creditTotal / $totalCollectedToday) * 100) : 0;
        $otherPercentage = $totalCollectedToday > 0 ? max(0, 100 - $cashPercentage - $mobileMoneyPercentage - $cardPercentage - $creditPercentage) : 0;

        // Top articles vendus aujourd'hui par le caissier
        $topProductsToday = SaleItem::whereHas('sale', function ($q) use ($user, $today) {
            $q->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereDate('created_at', $today);
        })
            ->select('product_name', 'product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('product_name', 'product_id')
            ->orderByDesc('total_qty')
            ->limit(4)
            ->get();

        // Dernières ventes
        $recentSales = Sale::where('user_id', $user->id)
            ->with(['customer', 'items'])
            ->latest()
            ->limit(10)
            ->get();

        return view('caissier.dashboard.index', compact(
            'activeSession',
            'sessionSalesCount',
            'sessionTotalSales',
            'sessionCashSales',
            'sessionCreditSales',
            'sessionCreditCount',
            'theoreticalDrawerAmount',
            'salesCountToday',
            'totalCollectedToday',
            'averageBasket',
            'cashTotal',
            'mobileMoneyTotal',
            'cardTotal',
            'creditTotal',
            'creditCountToday',
            'otherTotal',
            'cashPercentage',
            'mobileMoneyPercentage',
            'cardPercentage',
            'creditPercentage',
            'otherPercentage',
            'topProductsToday',
            'recentSales'
        ));
    }
}
