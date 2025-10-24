<?php

namespace App\Livewire\Forms;

use App\Enums\TypeClient;
use App\Models\Client;
use Livewire\Form;
use Illuminate\Validation\Rule;

class ClientForm extends Form
{
    // Propriétés du modèle Client
    public ?string $nom = null;
    public ?string $prenom = null;
    public ?string $email = null;
    public ?string $telephone = null;
    // Initialisation avec la valeur par défaut de l'Enum
    public ?string $type_client = TypeClient::PARTICULIER->value;

    // Propriétés du modèle Adresse (utilisation d'un préfixe pour éviter les conflits)
    public ?string $adresse_ligne1 = null;
    public ?string $adresse_ligne2 = null;
    public ?string $ville = null;
    public ?string $code_postal = null;
    public ?string $pays = null;

    protected ?Client $model = null;

    /**
     * Règle de validation du formulaire.
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            // L'email doit être unique sauf pour le client actuel (lors de la modification)
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($this->getClientId())],
            'telephone' => ['nullable', 'string', 'max:20'],
            // Valide que la valeur de l'Enum est valide
            'type_client' => ['required', 'string', Rule::in(TypeClient::cases())],

            // Règles pour l'adresse (on suppose que l'adresse est obligatoire)
            'adresse_ligne1' => ['required', 'string', 'max:255'],
            'adresse_ligne2' => ['nullable', 'string', 'max:255'],
            'ville' => ['required', 'string', 'max:100'],
            'code_postal' => ['required', 'string', 'max:20'],
            'pays' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Assigne les propriétés du modèle au formulaire pour l'édition.
     */
    public function setClient(Client $client): void
    {
        $this->model = $client;

        $this->nom = $client->nom;
        $this->prenom = $client->prenom;
        $this->email = $client->email;
        $this->telephone = $client->telephone;
        $this->type_client = $client->type_client;

        if ($client->adresse) {
            $this->adresse_ligne1 = $client->adresse->ligne1;
            $this->adresse_ligne2 = $client->adresse->ligne2;
            $this->ville = $client->adresse->ville;
            $this->code_postal = $client->adresse->code_postal;
            $this->pays = $client->adresse->pays;
        }
    }
    
    /**
     * Retourne l'ID du client pour les règles d'unicité.
     */
    protected function getClientId(): ?int
    {
        return $this->model?->id;
    }
    
    /**
     * Réinitialise le formulaire.
     */
    public function resetForm(): void
    {
        $this->reset();
        $this->type_client = TypeClient::PARTICULIER->value;
        $this->model = null;
    }
}