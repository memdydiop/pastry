<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    /**
     * Route de redirection pour complétion du profil.
     */
    protected const COMPLETION_ROUTE = 'profile.create';

    /**
     * Routes exclues de la vérification.
     */
    protected const EXCLUDED_ROUTES = [
        'profile.create',
        'profile.store',
        'profile.update',
        'logout',
        'verification.*',
        'livewire.*',
        'password.*',
    ];

    /**
     * Traite une requête entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Ce middleware doit être appliqué après 'auth'
        // donc $user ne devrait jamais être null
        if (!$user) {
            return $next($request);
        }

        // Si le profil est complété, continuer normalement
        if ($user->profile_completed) {
            return $next($request);
        }

        // Si la route actuelle est exclue, continuer
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Rediriger vers la page de complétion du profil
        return redirect()
            ->route(self::COMPLETION_ROUTE)
            ->with('warning', 'Veuillez compléter votre profil pour continuer.');
    }

    /**
     * Vérifie si la route actuelle est exclue.
     */
    protected function isExcludedRoute(Request $request): bool
    {
        return $request->routeIs(self::EXCLUDED_ROUTES);
    }
}