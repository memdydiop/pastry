<?php

namespace App\Services;

use App\Repositories\ClientRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientService
{
    protected ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * Récupère les clients paginés avec recherche et tri.
     */
    public function getPaginatedClients(string $search, string $sortField, string $sortDirection, int $perPage): LengthAwarePaginator
    {
        // Délégation de l'opération de base de données au Repository
        return $this->clientRepository->getPaginatedClients(
            $search, 
            $sortField, 
            $sortDirection, 
            $perPage);
    }

    // Autres méthodes de logique métier (calcul du score, envoi de notification, etc.) iraient ici.
}