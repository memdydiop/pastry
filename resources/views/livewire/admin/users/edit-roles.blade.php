<?php

use Livewire\Volt\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public ?User $user = null;
    public array $allRoles = [];
    #[Validate('nullable|array')] // Ajout de la validation
    public array $userRoles = [];
    public bool $modalOpen = false;

    // Écoute l'événement 'edit-user-roles' du composant parent
    #[On('edit-user-roles')]
    public function openModal(int $userId): void
    {


        // 1. Vérification de permission (doit être fait ici pour ouvrir la modale)
        if (!Gate::allows('edit users')) {
            return;
        }
        // Vérification de permission
        //if (!auth()->user()->can('edit users')) {
        //    return;
        //}

        $this->user = User::with('roles')->find($userId);

        if (!$this->user) {
            $this->closeModal();
            return;
        }

        $this->allRoles = Role::pluck('name')->toArray();
        $this->userRoles = $this->user->roles->pluck('name')->map(fn($role) => (string) $role)->toArray();

        $this->modalOpen = true;
    }

    public function updateRoles(): void
    {
        $this->validate();

        if (!Gate::allows('edit users')) {
            $this->closeModal();
            return;
        }

        // Vérification finale de permission
        //if (!auth()->user()->can('edit users')) {
        //    $this->closeModal();
        //    return;
        //}

        // Empêcher l'administrateur de retirer son propre rôle 'admin'
        //if ($this->user->id === auth()->id() && !in_array('admin', $this->userRoles)) {
        //    $this->userRoles[] = 'admin';
        //    session()->flash('error', 'Vous ne pouvez pas retirer votre propre rôle "admin".');
        //    // Continuer l'exécution pour synchroniser les autres rôles si nécessaire
        //}

        // Sécurité critique : si l'utilisateur essaie de se retirer son propre rôle 'admin', on le réajoute.
        if ($this->user->id === auth()->id() && in_array('Ghost', $this->user->roles->pluck('name')->toArray())) {
            if (!in_array('Ghost', $this->userRoles)) {
                $this->userRoles[] = 'Ghost'; // Force le maintien du rôle Ghost
                session()->flash('error', 'Vous ne pouvez pas retirer votre propre rôle "Ghost" pour des raisons de sécurité.');
            }
        }

        // Synchronise les rôles.
        $this->user->syncRoles($this->userRoles);

        // Émet un événement pour rafraîchir le composant parent (la liste)
        $this->dispatch('roles-updated');

        session()->flash('success', "Les rôles de {$this->user->name} ont été mis à jour.");
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
        $this->reset(['user', 'userRoles']);
    }
};
?>

<flux:modal wire:model="modalOpen" :title="__('Modifier les rôles pour :name', ['name' => $user?->name])">
    <form wire:submit="updateRoles" class="space-y-6">

        <flux:description>
            Sélectionnez les rôles que cet utilisateur doit posséder.
        </flux:description>

        @if ($user)
            <flux:fieldset>

                <flux:checkbox.group wire:model="userRoles" label="Rôles disponibles">
                    @foreach ($allRoles as $role)
                        @if ($user->id === auth()->id() && $role === 'Ghost')
                            <flux:checkbox label="{{ ucfirst($role) }} (Rôle administrateur personnel)" value="{{ $role }}" disabled checked />
                        @else
                            <flux:checkbox label="{{ ucfirst($role) }}" value="{{ $role }}" />
                        @endif
                    @endforeach
                </flux:checkbox.group>

            </flux:fieldset>
        @else
            <flux:text color="danger">Chargement de l'utilisateur en cours...</flux:text>
        @endif

        <div>
            <flux:button type="button" color="secondary" wire:click="closeModal">Annuler</flux:button>
            <flux:button type="submit" color="primary" :disabled="!$user || !auth()->user()->can('edit users')">
                Enregistrer les rôles
            </flux:button>
        </div>
    </form>
</flux:modal>