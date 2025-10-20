<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Vérifie si l'utilisateur a la permission requise.
     *
     * @param string|array $permission Une ou plusieurs permissions (OR logic)
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            abort(401, 'Non authentifié');
        }

        $user = auth()->user();

        // Super admin bypass
        if ($user->hasRole('Ghost')) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a au moins une des permissions
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'Action non autorisée.');
        }

        return $next($request);
    }
}