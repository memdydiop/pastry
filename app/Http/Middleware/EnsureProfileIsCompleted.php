<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        $user = Auth::user();

        // Si l'utilisateur n'est pas authentifié, laisser passer
        if (! $user) {
            return $next($request);
        }

        $profileIsComplete = $user->profile_completed;

        // Routes à exclure de la redirection
        $excludedRoutes = [
            'profile.create',
            'profile.store',
            'profile.update',
            'logout',
            'verification.*',
            'livewire.*',
            'password.*',
        ];

        $isExcludedRoute = $request->routeIs($excludedRoutes);

        // Si le profil n'est pas complet et que la route n'est pas exclue
        if (! $profileIsComplete && ! $isExcludedRoute) {
            return redirect()
                ->route('profile.create')
                ->with('warning', 'Veuillez compléter votre profil pour continuer.');
        }

        return $next($request);
    }
}
