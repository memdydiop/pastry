<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.auth')]
    class extends Component {
    use WithFileUploads;
    public string $full_name = '';
    public string $date_of_birth = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';
    public string $bio = '';
    public $avatar;

    public function saveProfile()
    {
        //dd('La méthode saveProfile est bien appelée !'); // <-- AJOUTEZ CECI
        $validated = $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:' . now()->subYears(120)->format('Y-m-d')],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);
        //dd($validated); // <-- AJOUTEZ CECI


        //if ($this->date_of_birth) {
        //    $profile->date_of_birth = \Carbon\Carbon::createFromFormat('d-m-Y', $this->date_of_birth);
        //}// Handle avatar upload
        // Handle avatar upload
        if ($this->avatar) {
            $validated['avatar'] = $this->avatar->store('avatars', 'public');

        }
        
        // Utilise updateOrCreate pour plus de robustesse
        Auth::user()->userProfile()->updateOrCreate(
            ['user_id' => Auth::id()], // Condition de recherche
            $validated // Données à insérer ou à mettre à jour
        );


        //$profile->save();
        Auth::user()->update(['profile_completed' => true]);
        
        // Redirige l'utilisateur vers le tableau de bord ou la page d'accueil
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
            wire:model="full_name" 
            :label="__('Nom et Pénoms')" 
            name="full_name"
            type="text" required autofocus
            placeholder="Nom Complet" />

        <!-- Date of Birth -->
        <flux:input 
            wire:model="date_of_birth" 
            :label="__('Date de naissance')" 
            name="date_of_birth"
            type="date"
            placeholder="Date de naissance" />

        <!-- Phone -->
        <flux:input 
            wire:model="phone" 
            :label="__('Téléphone')" 
            name="phone"
            type="tel"
            placeholder="Numéro de téléphone" />

        <!-- Address -->
        <flux:input 
            wire:model="address" 
            :label="__('Adresse')" 
            name="address"
            type="text"
            placeholder="Adresse complète" />

        <!-- City -->
        <flux:input 
            wire:model="city" 
            :label="__('Ville')" 
            name="city"
            type="text"
            placeholder="Ville" />

        <!-- Country -->
        <flux:input 
            wire:model="country" 
            :label="__('Pays')" 
            name="country"
            type="text"
            placeholder="Pays" />

        <!-- Bio -->
        <flux:textarea 
            wire:model="bio" 
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
                wire:model="avatar" 
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