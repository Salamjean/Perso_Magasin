<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SyncService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyncController extends Controller
{
    public function __construct(protected SyncService $syncService) {}

    /**
     * Page principale du Hub de Synchronisation & Mode Hors-Ligne
     */
    public function index(): View
    {
        $status = $this->syncService->getSyncStatus();

        return view('admin.sync.index', compact('status'));
    }

    /**
     * Endpoint API/AJAX pour tester la connexion distante
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->syncService->testRemoteConnection();

        return response()->json($result);
    }

    /**
     * Déclencher un PULL (Distant -> Local)
     */
    public function pull(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->syncService->pull();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return back()->with('success', $result['message']);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erreur PULL : '.$e->getMessage());
        }
    }

    /**
     * Déclencher un PUSH (Local -> Distant)
     */
    public function push(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->syncService->push();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return back()->with('success', $result['message']);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erreur PUSH : '.$e->getMessage());
        }
    }

    /**
     * Déclencher une synchronisation complète (PUSH + PULL)
     */
    public function syncAll(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->syncService->syncAll();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return back()->with('success', $result['message']);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Erreur de synchronisation : '.$e->getMessage());
        }
    }
}
