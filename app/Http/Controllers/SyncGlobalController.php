<?php

namespace App\Http\Controllers;

use App\Services\SyncService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncGlobalController extends Controller
{
    public function __construct(protected SyncService $syncService) {}

    /**
     * Obtenir l'état en direct de la connexion et le nombre d'éléments non synchronisés
     */
    public function status(): JsonResponse
    {
        $status = $this->syncService->getSyncStatus();

        return response()->json([
            'success' => true,
            'is_online' => $status['is_online'],
            'latency_ms' => $status['remote_info']['latency_ms'] ?? null,
            'message' => $status['remote_info']['message'] ?? '',
            'unsynced_count' => $status['unsynced_count'] ?? 0,
            'unsynced_breakdown' => $status['unsynced_breakdown'] ?? [],
            'last_sync' => $status['last_sync'] ?? null,
        ]);
    }

    /**
     * Déclencher la synchronisation automatique / manuelle rapide (PUSH & PULL)
     */
    public function triggerAuto(Request $request): JsonResponse
    {
        try {
            $report = $this->syncService->syncAll();

            return response()->json([
                'success' => true,
                'message' => $report['message'],
                'unsynced_count' => 0,
                'report' => $report,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de synchronisation : '.$e->getMessage(),
                'unsynced_count' => $this->syncService->getUnsyncedCount(),
            ], 500);
        }
    }

    /**
     * Synchronisation au démarrage (Splash Screen) - Accessible sans session active
     */
    public function startupSync(): JsonResponse
    {
        try {
            $test = $this->syncService->testRemoteConnection();
            if (! $test['connected']) {
                return response()->json([
                    'success' => false,
                    'is_online' => false,
                    'message' => 'Mode Hors-Ligne (Serveur distant non joignable).',
                ]);
            }

            $report = $this->syncService->syncAll();

            return response()->json([
                'success' => true,
                'is_online' => true,
                'message' => 'Synchronisation initiale réussie.',
                'report' => $report,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'is_online' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
