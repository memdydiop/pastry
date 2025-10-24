<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository
{
    /**
     * Récupère les clients paginés avec recherche et tri.
     */
    public function getPaginatedClients(string $search, string $sortField, string $sortDirection, int $perPage): LengthAwarePaginator
    {
        // La colonne 'nom_complet' est un accesseur, elle est remplacée par 'nom' ou 'raison_sociale' pour le tri SQL.
        // Puisque scopeSearch gère la recherche sur 'nom' et 'raison_sociale', on utilise 'nom' comme fallback physique.
        $sortColumn = ($sortField === 'nom_complet' || $sortField === 'type') ? 'nom' : $sortField;
        
        return Client::query()
            // Utilise le scope Search du modèle (maintenant plus complet)
            ->search($search)
            // Applique le tri sur la colonne physique corrigée
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage);
    }
}