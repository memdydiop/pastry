<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Adresse;
use App\Repositories\ClientRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function __construct(
        private ClientRepository $repository
    ) {}

    public function creerClient(array $data): Client
    {
        return DB::transaction(function () use ($data) {
            // Extraire les adresses si présentes
            $adresses = $data['adresses'] ?? [];
            unset($data['adresses']);

            // Vérifier l'unicité de l'email
            if ($this->repository->findByEmail($data['email'])) {
                throw ValidationException::withMessages([
                    'email' => 'Cette adresse email est déjà utilisée.',
                ]);
            }

            // Créer le client
            $client = $this->repository->create($data);

            // Créer les adresses
            if (!empty($adresses)) {
                foreach ($adresses as $index => $adresseData) {
                    $adresseData['is_default'] = $index === 0;
                    $client->adresses()->create($adresseData);
                }
            }

            // Calculer le score initial
            $client->calculerScore();

            return $client->fresh('adresses');
        });
    }

    public function mettreAJourClient(Client $client, array $data): Client
    {
        return DB::transaction(function () use ($client, $data) {
            // Extraire les adresses si présentes
            $adresses = $data['adresses'] ?? null;
            unset($data['adresses']);

            // Vérifier l'unicité de l'email (sauf pour le client actuel)
            if (isset($data['email']) && $data['email'] !== $client->email) {
                if ($this->repository->findByEmail($data['email'])) {
                    throw ValidationException::withMessages([
                        'email' => 'Cette adresse email est déjà utilisée.',
                    ]);
                }
            }

            // Mettre à jour le client
            $this->repository->update($client, $data);

            // Mettre à jour les adresses si fournies
            if ($adresses !== null) {
                // Supprimer les anciennes adresses
                $client->adresses()->delete();

                // Créer les nouvelles
                foreach ($adresses as $index => $adresseData) {
                    $adresseData['is_default'] = $index === 0;
                    $client->adresses()->create($adresseData);
                }
            }

            // Recalculer le score
            $client->calculerScore();

            return $client->fresh('adresses');
        });
    }

    public function supprimerClient(Client $client): bool
    {
        // Vérifier s'il a des commandes (à implémenter plus tard)
        // if ($client->commandes()->exists()) {
        //     throw new \Exception('Impossible de supprimer un client avec des commandes existantes.');
        // }

        return $this->repository->delete($client);
    }

    public function ajouterAdresse(Client $client, array $data): Adresse
    {
        $adresse = $client->adresses()->create($data);

        // Si c'est la seule adresse ou si demandé, la mettre par défaut
        if ($client->adresses()->count() === 1 || ($data['is_default'] ?? false)) {
            $adresse->definirParDefaut();
        }

        return $adresse;
    }

    public function supprimerAdresse(Adresse $adresse): bool
    {
        $client = $adresse->client;
        
        // Ne pas permettre de supprimer la dernière adresse
        if ($client->adresses()->count() === 1) {
            throw new \Exception('Impossible de supprimer la dernière adresse du client.');
        }

        $etaitDefaut = $adresse->is_default;
        $resultat = $adresse->delete();

        // Si c'était l'adresse par défaut, définir une autre comme défaut
        if ($etaitDefaut && $resultat) {
            $client->adresses()->first()?->definirParDefaut();
        }

        return $resultat;
    }

    public function ajouterPointsFidelite(Client $client, int $points): void
    {
        $client->ajouterPoints($points);
        $client->calculerScore();
    }
}