<?php

use App\Livewire\Forms\Auth\RegisterForm;
use App\Models\Invitation;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public RegisterForm $form;
    public ?Invitation $invitation = null;

    /**
     * Initialise le composant et pré-remplit le formulaire.
     */
    public function mount(string $token = ''): void
    {
        $this->invitation = Invitation::where('token', $token)->whereNull('registered_at')->firstOrFail();
        $this->form->email = $this->invitation->email;
    }

    /**
     * Gère la demande d'enregistrement.
     */
    public function register(): void
    {
        // Valide et crée l'utilisateur via l'objet Form, en passant l'invitation
        $this->form->store($this->invitation);

        // Marque l'invitation comme utilisée
        $this->invitation->update(['registered_at' => now()]);

        // Redirige vers la page de vérification d'email
        //$this->redirect(
        //    url: route('verification.notice', absolute: false),
        //    navigate: true
        //);
    }
}; ?>

<x-layouts.auth.card>
    <x-slot name="header">
        <x-auth-header :title="__('Créez votre compte')" :description="__('Remplissez les informations ci-dessous pour finaliser votre inscription.')" />
    </x-slot>

    <form wire:submit="register" class="space-y-6">

        {{-- Email Address --}}
        <flux:input wire:model="form.email" label="Adresse Email" type="email" name="email" required readonly
            disabled autocomplete="username" />

        {{-- Password --}}
        <flux:input wire:model="form.password" label="Mot de passe" type="password" name="password" required
            autocomplete="new-password" />

        {{-- Confirm Password --}}
        <flux:input wire:model="form.password_confirmation" label="Confirmer le mot de passe" type="password"
            name="password_confirmation" required autocomplete="new-password" />

        <flux:button type="submit" variant="primary" class="w-full" spinner>
            {{ __('S\'inscrire') }}
        </flux:button>
    </form>
</x-layouts.auth.card>