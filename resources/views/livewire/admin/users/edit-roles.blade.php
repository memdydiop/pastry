<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public ?User $user = null;
    public array $allRoles = [];
    #[Validate('nullable|array')]
    public array $userRoles = [];
    public bool $modalOpen = false;

    #[On('edit-user-roles')]
    public function openModal(int $userId): void
    {
        if (!Gate::allows('edit users')) {
            return;
        }

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

        if ($this->user->id === auth()->id() && in_array('Ghost', $this->user->roles->pluck('name')->toArray())) {
            if (!in_array('Ghost', $this->userRoles)) {
                $this->userRoles[] = 'Ghost';
                session()->flash('error', 'Vous ne pouvez pas retirer votre propre rôle "Ghost" pour des raisons de sécurité.');
            }
        }

        $this->user->syncRoles($this->userRoles);
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

<flux:modal wire:model="modalOpen" :title="__('Modifier les rôles pour :name', ['name' => $user?->name])" class="max-w-3xl">
    @if ($user)
        <form wire:submit="updateRoles" class="space-y-6">

            <div class="space-y-4">
                <flux:heading size="lg">Rôles</flux:heading>
                <flux:subheading>Sélectionnez les rôles que cet utilisateur doit posséder.</flux:subheading>

                <div class="space-y-4 border rounded-lg p-4">
                    <flux:checkbox.group wire:model="userRoles">
                        @foreach ($allRoles as $role)
                            @if ($user->id === auth()->id() && $role === 'Ghost')
                                <flux:checkbox label="{{ $role }} (Rôle système personnel)" value="{{ $role }}" disabled checked />
                            @else
                                <flux:checkbox label="{{ $role }}" value="{{ $role }}" />
                            @endif
                        @endforeach
                    </flux:checkbox.group>
                </div>

                <flux:text sm color="muted">
                    {{ count($userRoles) }} rôle(s) sélectionné(s)
                </flux:text>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2 pt-4 border-t">
                <flux:button type="button" variant="secondary" wire:click="closeModal">
                    Annuler
                </flux:button>
                <flux:button type="submit" variant="primary" :disabled="!$user || !auth()->user()->can('edit users')">
                    <span wire:loading.remove wire:target="updateRoles">Enregistrer</span>
                    <span wire:loading wire:target="updateRoles">Mise à jour...</span>
                </flux:button>
            </div>
        </form>
    @else
        <div class="text-center py-8">
            <flux:text color="danger">Chargement de l'utilisateur en cours...</flux:text>
        </div>
    @endif
</flux:modal>