<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Votre compte a été désactivé. Veuillez contacter un administrateur.');
        }

        if (! in_array($user->role, $roles)) {
            // Rediriger vers son propre tableau de bord
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'magasinier' => redirect()->route('magasinier.dashboard'),
                'caissier' => redirect()->route('caissier.dashboard'),
                'livreur' => redirect()->route('livreur.dashboard'),
                default => abort(403, 'Accès non autorisé à cette section.'),
            };
        }

        return $next($request);
    }
}
