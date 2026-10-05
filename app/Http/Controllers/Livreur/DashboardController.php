<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Compteurs de livraisons
        $assignedCount = Delivery::where('livreur_id', $user->id)->where('status', 'assigned')->count();
        $inTransitCount = Delivery::where('livreur_id', $user->id)->where('status', 'in_transit')->count();
        $deliveredTodayCount = Delivery::where('livreur_id', $user->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', $today)
            ->count();
        $failedTodayCount = Delivery::where('livreur_id', $user->id)
            ->where('status', 'failed')
            ->whereDate('updated_at', $today)
            ->count();
        $totalDeliveredCount = Delivery::where('livreur_id', $user->id)->where('status', 'delivered')->count();

        // Montants financiers
        $cashToCollect = (float) Delivery::where('livreur_id', $user->id)
            ->whereIn('status', ['assigned', 'in_transit'])
            ->sum('total_amount');

        $cashCollectedToday = (float) Delivery::where('livreur_id', $user->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', $today)
            ->sum('total_amount');

        // Taux de réussite global
        $totalFinished = Delivery::where('livreur_id', $user->id)->whereIn('status', ['delivered', 'failed'])->count();
        $successRate = $totalFinished > 0 ? round(($totalDeliveredCount / $totalFinished) * 100) : 100;

        // Livraisons actives urgentes (in_transit en priorité, puis assigned)
        $activeDeliveries = Delivery::where('livreur_id', $user->id)
            ->whereIn('status', ['assigned', 'in_transit'])
            ->with(['customer', 'sale.items.product'])
            ->orderByRaw("CASE WHEN status = 'in_transit' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        // Dernières livraisons terminées
        $recentCompleted = Delivery::where('livreur_id', $user->id)
            ->where('status', 'delivered')
            ->with(['customer', 'sale'])
            ->latest('delivered_at')
            ->limit(6)
            ->get();

        return view('livreur.dashboard.index', compact(
            'assignedCount',
            'inTransitCount',
            'deliveredTodayCount',
            'failedTodayCount',
            'totalDeliveredCount',
            'cashToCollect',
            'cashCollectedToday',
            'successRate',
            'activeDeliveries',
            'recentCompleted'
        ));
    }
}
