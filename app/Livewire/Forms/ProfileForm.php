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
     * ✅ AMÉLIORATION : Validation renforcée avec regex et dimensions
     */
    public function rules(): array
    {
        $profileId = auth()->user()->profile?->id;
        
        return [
            'full_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}\s\-\']+$/u', // Lettres, espaces, tirets, apostrophes
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:1900-01-01',
            ],
            'phone' => [
                'required', 
                'string', 
                'min:8',
                'max:20',
                'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/',
                Rule::unique(UserProfile::class, 'phone')->ignore($profileId)
            ],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'country' => ['required', 'string', 'min:2', 'max:100'],
            'bio' => ['required', 'string', 'min:10', 'max:1000'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp', // ✅ Support WEBP
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
            ],
        ];
    }

    /**
     * ✅ AMÉLIORATION : Messages plus explicites
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'full_name.min' => 'Le nom doit contenir au moins :min caractères.',
            'full_name.regex' => 'Le nom contient des caractères invalides.',
            
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'date_of_birth.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'date_of_birth.after' => 'La date de naissance semble incorrecte.',
            
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'phone.regex' => 'Le format du numéro de téléphone est invalide.',
            'phone.min' => 'Le numéro doit contenir au moins :min chiffres.',
            
            'address.required' => 'L\'adresse est obligatoire.',
            'address.min' => 'L\'adresse doit contenir au moins :min caractères.',
            
            'city.required' => 'La ville est obligatoire.',
            'country.required' => 'Le pays est obligatoire.',
            
            'bio.required' => 'La biographie est obligatoire.',
            'bio.min' => 'La biographie doit contenir au moins :min caractères.',
            'bio.max' => 'La biographie ne peut pas dépasser :max caractères.',
            
            'avatar.image' => 'Le fichier doit être une image.',
            'avatar.mimes' => 'L\'avatar doit être au format JPEG, JPG, PNG ou WEBP.',
            'avatar.max' => 'L\'avatar ne peut pas dépasser 2 Mo.',
            'avatar.dimensions' => 'L\'image doit avoir au moins 100x100 pixels.',
        ];
    }

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

    /**
     * ✅ NOUVEAU : Prépare les données avant enregistrement
     */
    public function prepareForSave(): array
    {
        $data = $this->only([
            'full_name',
            'date_of_birth',
            'phone',
            'address',
            'city',
            'country',
            'bio',
        ]);

        // Nettoyage
        $data['phone'] = preg_replace('/[^\+0-9]/', '', $data['phone']);
        $data['full_name'] = trim($data['full_name']);
        $data['address'] = trim($data['address']);
        $data['city'] = trim($data['city']);
        $data['country'] = trim($data['country']);
        
        return $data;
    }
}