<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Facades\URL;

class ValidateInvitationToken
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Vérifiez d'abord si la signature de l'URL est valide
        if (! $request->hasValidSignature()) {
            abort(401, 'Lien d\'invitation invalide ou expiré.');
        }

        // 2. Récupérez le token et vérifiez son statut dans la base de données
        $token = $request->route('token');
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation || $invitation->registered_at) {
            abort(403, 'Cette invitation a déjà été utilisée ou n\'existe pas.');
        }

        return $next($request);
    }
}