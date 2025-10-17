<?php

use App\Livewire\Forms\ProfileForm;
use Livewire\Attributes\Layout;
use App\Http\Requests\StoreUserProfileRequest; 
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.auth')]
    class extends Component {

    use WithFileUploads;
    
    // Déclarez une seule propriété pour le formulaire.
    public ProfileForm $form;

    public function saveProfile()
    {
        // Validez et récupérez les données en un seul appel !
        $data = $this->form->validate();

        // Si un avatar est téléchargé, on le stocke
        if ($this->form->avatar) {
            $data['avatar'] = $this->form->avatar->store('avatars', 'public');
        }

        // Crée le profil de l'utilisateur
        auth()->user()->profile()->create($data);

        // Met à jour le statut de l'utilisateur
        auth()->user()->update(['profile_completed' => true]);

        // Redirige vers le tableau de bord
        return $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Create an account')" 
        :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="saveProfile" class="flex flex-col gap-6">

        <!-- Full_name -->
        <flux:input 
            wire:model="form.full_name" 
            :label="__('Nom et Pénoms')" 
            name="full_name"
            type="text" required autofocus
            placeholder="Nom Complet" />

        <!-- Date of Birth -->
        <flux:input 
            wire:model="form.date_of_birth" 
            :label="__('Date de naissance')" 
            name="date_of_birth"
            type="date"
            placeholder="Date de naissance" />

        <!-- Phone -->
        <flux:input 
            wire:model="form.phone" 
            :label="__('Téléphone')" 
            name="phone"
            type="tel"
            placeholder="Numéro de téléphone" />

        <!-- Address -->
        <flux:input 
            wire:model="form.address" 
            :label="__('Adresse')" 
            name="address"
            type="text"
            placeholder="Adresse complète" />

        <!-- City -->
        <flux:input 
            wire:model="form.city" 
            :label="__('Ville')" 
            name="city"
            type="text"
            placeholder="Ville" />

        <!-- Country -->
        <flux:input 
            wire:model="form.country" 
            :label="__('Pays')" 
            name="country"
            type="text"
            placeholder="Pays" />

        <!-- Bio -->
        <flux:textarea 
            wire:model="form.bio" 
            :label="__('Biographie')" 
            name="bio"
            rows="4"
            placeholder="Parlez-nous de vous..." />

        <!-- Avatar -->
        <div>
            <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('Photo de profil (optionnel)') }}
            </label>
            <input 
                type="file" 
                wire:model="form.avatar" 
                name="avatar"
                id="avatar"
                accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            @error('avatar')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Enregistrer les modifications</span>
            <span wire:loading>Enregistrement en cours...</span>
        </flux:button>

    </form>

</div>