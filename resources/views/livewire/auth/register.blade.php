<?php

use App\Livewire\Forms\Auth\RegisterForm;
use App\Models\Invitation;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public RegisterForm $form;
    public ?Invitation $invitation = null;

    /**
     * Mount the component and pre-fill the form.
     */
    public function mount(string $token = ''): void
    {
        // firstOrFail() va automatiquement générer une erreur 404 si le token
        // est invalide ou si l'invitation a déjà été utilisée. C'est plus propre.
        $this->invitation = Invitation::where('token', $token)->whereNull('registered_at')->firstOrFail();

        // On assigne l'email de l'invitation au champ du formulaire.
        // L'erreur précédente se produisait ici, mais avec une gestion propre via firstOrFail,
        // ce code n'est atteint que si $this->invitation est valide.
        $this->form->email = $this->invitation->email;
    }

    /**
     * Handle the registration request.
     */
    public function register(): void
    {
        // Pour une sécurité maximale, on revérifie que l'email du formulaire
        // correspond bien à une invitation encore valide au moment de la soumission.
        // Cela évite des manipulations entre le chargement de la page et l'envoi du formulaire.
        $invitation = Invitation::where('email', $this->form->email)->whereNull('registered_at')->first();

        if (!$invitation) {
            // Redirige avec une erreur si l'invitation n'est plus valide.
            session()->flash('error', 'Ce lien d\'invitation a expiré ou a déjà été utilisé.');
            $this->redirect(route('login'), navigate: true);
            return;
        }

        // Valide et crée l'utilisateur via l'objet Form
        $user = $this->form->store();

        // Marque l'invitation comme utilisée
        $invitation->update(['registered_at' => now()]);

        // Connecte l'utilisateur
        auth()->login($user);

        // Redirige vers le tableau de bord
        $this->redirect(
            url: route('dashboard', absolute: false),
            navigate: true
        );
    }
}; ?>

<x-layouts.auth.card>
    <x-slot name="header">
        <x-auth-header :title="__('Créez votre compte')" :description="__('Remplissez les informations ci-dessous pour finaliser votre inscription.')" />
    </x-slot>

    <form wire:submit="register" class="space-y-6">

        {{-- Email Address --}}
        <flux:input wire:model="form.email" label="Adresse Email" type="email" name="email" required readonly
            autocomplete="username" />

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