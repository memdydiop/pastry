<?php

namespace App\Services;

use App\Models\Client;
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
/**
     * Crée un nouveau client et son adresse associée.
     */
    public function createClientWithAddress(array $clientData, array $addressData): Client
    {
        // Logique métier avant création (ex: générer un code client, valider)
        return $this->clientRepository->createClient($clientData, $addressData);
    }

    /**
     * Met à jour un client existant et son adresse.
     */
    public function updateClientWithAddress(Client $client, array $clientData, array $addressData): Client
    {
        // Logique métier avant mise à jour (ex: audit, vérification de statut)
        return $this->clientRepository->updateClient($client, $clientData, $addressData);
    }

    /**
     * Supprime un client.
     */
    public function deleteClient(Client $client): bool
    {
        // Logique métier avant suppression (ex: vérification des commandes associées)
        return $this->clientRepository->deleteClient($client);
    }
}