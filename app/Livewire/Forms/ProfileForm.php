<?php

namespace App\Livewire\Forms;

use App\Models\UserProfile;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProfileForm extends Form
{
    public ?string $full_name = null;
    public ?string $date_of_birth = null;
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $city = null;
    public ?string $country = null;
    public ?string $bio = null;
    public $avatar = null;

    /**
     * Règles de validation dynamiques
     */
    public function rules(): array
    {
        $profileId = auth()->user()->profile?->id;
        
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'phone' => [
                'required', 
                'string', 
                'max:20',
                'regex:/^[+]?[0-9\s\-\(\)]+$/',
                Rule::unique(UserProfile::class, 'phone')->ignore($profileId)
            ],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'bio' => ['required', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'full_name.max' => 'Le nom complet ne peut pas dépasser 255 caractères.',
            'date_of_birth.date' => 'La date de naissance doit être une date valide.',
            'date_of_birth.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'phone.regex' => 'Le format du numéro de téléphone est invalide.',
            'phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'address.required' => 'L\'adresse est obligatoire.',
            'address.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',
            'city.required' => 'La ville est obligatoire.',
            'city.max' => 'La ville ne peut pas dépasser 100 caractères.',
            'country.required' => 'Le pays est obligatoire.',
            'country.max' => 'Le pays ne peut pas dépasser 100 caractères.',
            'bio.max' => 'La biographie ne peut pas dépasser 1000 caractères.',
            'avatar.image' => 'Le fichier doit être une image.',
            'avatar.mimes' => 'L\'avatar doit être au format JPEG, JPG, PNG ou GIF.',
            'avatar.max' => 'L\'avatar ne peut pas dépasser 2 Mo.',
        ];
    }

    /**
     * Attributs personnalisés pour les messages
     */
    public function validationAttributes(): array
    {
        return [
            'full_name' => 'nom complet',
            'date_of_birth' => 'date de naissance',
            'phone' => 'téléphone',
            'address' => 'adresse',
            'city' => 'ville',
            'country' => 'pays',
            'bio' => 'biographie',
            'avatar' => 'avatar',
        ];
    }
}