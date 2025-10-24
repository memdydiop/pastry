<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Adresse;
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
            // Ajoute le chargement anticipé de l'adresse
            ->with('adresse')
            // Utilise le scope Search du modèle (maintenant plus complet)
            ->search($search)
            // Applique le tri sur la colonne physique corrigée
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage);
    }/**
     * Crée un nouveau client et son adresse.
     */
    public function createClient(array $clientData, array $addressData): Client
    {
        $client = Client::create($clientData);

        // Crée et associe l'adresse
        $adresse = new Adresse($addressData);
        $client->adresse()->save($adresse);

        return $client->load('adresse');
    }

    /**
     * Met à jour un client et son adresse.
     */
    public function updateClient(Client $client, array $clientData, array $addressData): Client
    {
        $client->update($clientData);

        // Crée ou met à jour l'adresse
        if ($client->adresse) {
            $client->adresse->update($addressData);
        } else {
            $adresse = new Adresse($addressData);
            $client->adresse()->save($adresse);
        }

        return $client->load('adresse');
    }

    /**
     * Supprime un client.
     */
    public function deleteClient(Client $client): bool
    {
        // Supprime l'adresse si elle existe (avant le client pour éviter les problèmes de clé étrangère).
        if ($client->adresse) {
            $client->adresse->delete();
        }
        return $client->delete();
    }
}