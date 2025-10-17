<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Rule;
use Livewire\Form;

class ProfileForm extends Form
{
    #[Rule('required|string|max:255', as: 'nom complet')]
    public string $full_name = '';

    #[Rule('required|date|before:today', as: 'date de naissance')]
    public string $date_of_birth = '';

    #[Rule('required|string|max:20', as: 'téléphone')]
    public string $phone = '';

    #[Rule('required|string|max:255', as: 'adresse')]
    public string $address = '';

    #[Rule('required|string|max:100', as: 'ville')]
    public string $city = '';

    #[Rule('required|string|max:100', as: 'pays')]
    public string $country = '';

    #[Rule('nullable|string|max:1000')]
    public string $bio = '';

    #[Rule('nullable|image|max:2048', as: 'avatar')] // 2MB Max
    public $avatar;     
}
