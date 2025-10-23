<?php

namespace App\Repositories;

use App\Models\Client;
use App\Enums\TypeClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ClientRepository
{
    public function __construct(
        private Client $model
    ) {}

    public function find(int $id): ?Client
    {
        return $this->model
            ->with(['adresses'])
            ->find($id);
    }

    public function findByEmail(string $email): ?Client
    {
        return $this->model->where('email', $email)->first();
    }

    public function all(): Collection
    {
        return $this->model->with('adresses')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()->with('adresses');

        // Recherche
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtre par type
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'particulier') {
                $query->particuliers();
            } elseif ($filters['type'] === 'entreprise') {
                $query->entreprises();
            }
        }

        // Filtre VIP
        if (!empty($filters['vip'])) {
            $query->vip();
        }

        // Tri
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    public function create(array $data): Client
    {
        return $this->model->create($data);
    }

    public function update(Client $client, array $data): bool
    {
        return $client->update($data);
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
    }

    public function getStatistiques(): array
    {
        return [
            'total' => $this->model->count(),
            'particuliers' => $this->model->particuliers()->count(),
            'entreprises' => $this->model->entreprises()->count(),
            'vip' => $this->model->vip()->count(),
            'score_moyen' => round($this->model->avg('score_client'), 2),
            'points_total' => $this->model->sum('points_fidelite'),
        ];
    }

    public function getTopClients(int $limit = 10): Collection
    {
        return $this->model
            ->orderByDesc('score_client')
            ->limit($limit)
            ->get();
    }
}