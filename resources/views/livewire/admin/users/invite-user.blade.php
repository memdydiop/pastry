<?php

use Livewire\Volt\Component;
use App\Models\Invitation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\UserInvitationMail;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    public bool $modalOpen = false;

    public function sendInvitation(): void
    {
        $this->validate([
            'email' => 'unique:invitations,email,NULL,id,registered_at,NULL'
        ], [
            'email.unique' => 'Une invitation a déjà été envoyée à cette adresse email.'
        ]);

        $invitation = Invitation::create([
            'email' => $this->email,
            'token' => (string) Str::uuid(),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'register.invitation',
            now()->addDays(7),
            ['token' => $invitation->token]
        );

        Mail::to($this->email)->send(new UserInvitationMail($signedUrl));

        session()->flash('success', 'Invitation envoyée avec succès.');
        $this->closeModal();
    }

    public function openModal()
    {
        $this->reset('email');
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
    }
}; ?>

<div>
    <flux:button icon="user-plus" variant="primary" wire:click="openModal">
        Inviter un utilisateur
    </flux:button>

    @if($modalOpen)
        <flux:modal wire:model="modalOpen" title="Inviter un nouvel utilisateur">
            <form wire:submit="sendInvitation" class="space-y-6">
                <flux:description>
                    L'utilisateur recevra un email contenant un lien pour créer son compte. Ce lien expirera dans 7 jours.
                </flux:description>

                <flux:input wire:model="email" label="Adresse email" type="email" placeholder="nom@exemple.com" required />
                @error('email') <flux:error>{{ $message }}</flux:error> @enderror

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <flux:button type="button" variant="secondary" wire:click="closeModal">
                        Annuler
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Envoyer l'invitation
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>