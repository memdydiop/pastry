<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On;

new class extends Component {

    public ?Role $role = null;
    public bool $modalOpen = false;
    public array $groupedPermissions = [];

    #[On('view-role-permissions')]
    public function openModal(int $roleId): void
    {
        $this->role = Role::with('permissions')->findOrFail($roleId);

        // Grouper les permissions par catégorie avec conversion en tableau
        $this->groupedPermissions = $this->role->permissions
            ->groupBy(function ($permission) {
                $parts = explode(' ', $permission->name);
                return end($parts);
            })
            ->map(function ($group) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->toArray();
            })
            ->toArray();

        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->reset(['role', 'groupedPermissions']);
    }
};

?>

<flux:modal wire:model="modalOpen" :title="'Permissions du rôle: ' . ($role?->name ?? '')" class="max-w-2xl">
    @if($role)
        <div class="space-y-6">

            {{-- Informations générales --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:text sm color="muted">Nom du rôle</flux:text>
                        <flux:heading size="sm" class="mt-1">{{ $role->name }}</flux:heading>
                    </div>
                    <div>
                        <flux:text sm color="muted">Nombre d'utilisateurs</flux:text>
                        <flux:heading size="sm" class="mt-1 flex items-center gap-2">
                            <flux:icon.users class="w-5 h-5" />
                            {{ $role->users()->count() }}
                        </flux:heading>
                    </div>
                </div>
            </div>

            {{-- Liste des permissions --}}
            @if($role->permissions->isEmpty())
                <div class="text-center py-8">
                    <flux:icon.shield-exclamation class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                    <flux:text color="muted">Ce rôle ne possède aucune permission.</flux:text>
                </div>
            @else
                <div class="space-y-4">
                    <flux:heading size="lg">
                        Permissions attribuées ({{ $role->permissions->count() }})
                    </flux:heading>

                    <div class="space-y-4 max-h-96 overflow-y-auto border rounded-lg p-4">
                        @foreach ($groupedPermissions as $category => $permissions)
                            <div class="border-b pb-4 last:border-b-0">
                                <div class="flex items-center gap-2 mb-3">
                                    <flux:icon.folder class="w-5 h-5 text-blue-500" />
                                    <flux:heading size="sm">{{ $category }}</flux:heading>
                                    <flux:badge size="sm" color="secondary">
                                        {{ count($permissions) }}
                                    </flux:badge>
                                </div>

                                <div class="grid grid-cols-2 gap-2 ml-7">
                                    @foreach ($permissions as $permission)
                                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                                            <flux:icon.check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                                            <flux:text sm class="truncate">
                                                {{ str_replace('_', ' ', $permission['name']) }}
                                            </flux:text>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex justify-end gap-2 pt-4 border-t">
                <flux:button type="button" variant="secondary" wire:click="closeModal">
                    Fermer
                </flux:button>

                @can('edit roles')
                    <flux:button icon="pencil-square" variant="primary"
                        wire:click="$dispatch('edit-role', { roleId: {{ $role->id }} }); closeModal()">
                        
                        Modifier
                    </flux:button>
                @endcan
            </div>
        </div>
    @endif
</flux:modal>