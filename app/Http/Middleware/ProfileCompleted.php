<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // On vérifie si l'utilisateur est connecté ET s'il n'a PAS de profil
        // ET qu'il n'est pas déjà sur la page de création de profil (pour éviter une boucle infinie).
        if (Auth::check() && 
            !Auth::user()->profile_completed && 
            !$request->routeIs('profile.create') &&
            !$request->routeIs('logout') &&
            !$request->routeIs('verification.*') &&
            !$request->routeIs('livewire*')) {
            // Redirige l'utilisateur vers la page de création de profil
            return redirect()
                ->route('profile.create')
                ->with('warning', 'Veuillez compléter votre profil pour continuer.');
        }
        return $next($request);
    }
}
