<?php

use Livewire\Volt\Component;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends Component {

    public ?Role $role = null;
    public bool $modalOpen = false;
    public array $groupedPermissions = [];
    public bool $isProtected = false;

    #[Validate('required|string|min:2|max:50')]
    public string $name = '';

    #[Validate('array')]
    public array $selectedPermissions = [];

    #[On('edit-role')]
    public function openModal(int $roleId): void
    {
        $this->role = Role::with('permissions')->findOrFail($roleId);

        $roleService = app(RoleService::class);
        $this->isProtected = $roleService->isProtected($this->role);

        $this->name = $this->role->name;
        $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();
        $this->groupedPermissions = $roleService->getGroupedPermissions();

        $this->modalOpen = true;
    }

    public function updateRole(): void
    {
        // Validation avec règle dynamique pour l'unicité
        $this->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($this->role->id),
            ],
            'selectedPermissions' => 'array',
        ]);

        try {
            $roleService = app(RoleService::class);
            $roleService->updateRole(
                $this->role,
                $this->name,
                $this->selectedPermissions
            );

            $this->dispatch('role-updated');
            session()->flash('success', "Le rôle \"{$this->name}\" a été mis à jour.");

            $this->closeModal();

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('name', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la mise à jour du rôle.');
        }
    }

    public function toggleCategory(string $category): void
    {
        $categoryPermissions = collect($this->groupedPermissions[$category] ?? [])
            ->pluck('name')
            ->toArray();

        $allSelected = empty(array_diff($categoryPermissions, $this->selectedPermissions));

        if ($allSelected) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $categoryPermissions);
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $categoryPermissions));
        }
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->reset(['role', 'name', 'selectedPermissions', 'isProtected']);
        $this->resetValidation();
    }
};

?>

<flux:modal wire:model="modalOpen" :title="'Modifier le rôle: ' . ($role?->name ?? '')" class="max-w-3xl">
    @if($role)
        <form wire:submit="updateRole" class="space-y-6">

            {{-- Alerte pour rôles protégés --}}
            @if($isProtected)
                <div class="rounded-md bg-yellow-50 p-4">
                    <div class="flex">
                        <flux:icon.exclamation-triangle class="h-5 w-5 text-yellow-400" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Rôle système protégé</h3>
                            <p class="mt-1 text-sm text-yellow-700">
                                Ce rôle est protégé et ne peut pas être renommé. Vous pouvez uniquement modifier ses
                                permissions.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Nom du rôle --}}
            <flux:input wire:model="name" label="Nom du rôle" :disabled="$isProtected" required />

            {{-- Permissions --}}
            <div class="space-y-4">
                <flux:heading size="lg">Permissions</flux:heading>
                <flux:subheading>Modifiez les permissions de ce rôle</flux:subheading>

                <div class="space-y-4 max-h-96 overflow-y-auto border rounded-lg p-4">
                    @foreach ($groupedPermissions as $category => $permissions)
                        <div class="border-b pb-4 last:border-b-0">
                            <div class="flex items-center justify-between mb-3">
                                <flux:heading size="sm" class="flex items-center gap-2">
                                    <flux:icon.folder class="w-5 h-5 text-gray-500" />
                                    {{ $category }}
                                </flux:heading>

                                <flux:button type="button" size="sm" variant="ghost"
                                    wire:click="toggleCategory('{{ $category }}')">
                                    @php
                                        $categoryPerms = collect($permissions)->pluck('name')->toArray();
                                        $allSelected = empty(array_diff($categoryPerms, $selectedPermissions));
                                    @endphp
                                    {{ $allSelected ? 'Tout désélectionner' : 'Tout sélectionner' }}
                                </flux:button>
                            </div>

                            <div class="grid grid-cols-2 gap-2 ml-7">
                                @foreach ($permissions as $permission)
                                    <flux:checkbox wire:model="selectedPermissions" value="{{ $permission->name }}"
                                        label="{{ ucfirst(str_replace('_', ' ', $permission->name)) }}" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <flux:text sm color="muted">
                    {{ count($selectedPermissions) }} permission(s) sélectionnée(s)
                </flux:text>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2 pt-4 border-t">
                <flux:button type="button" variant="secondary" wire:click="closeModal">
                    Annuler
                </flux:button>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="updateRole">Mettre à jour</span>
                    <span wire:loading wire:target="updateRole">Mise à jour...</span>
                </flux:button>
            </div>
        </form>
    @endif
</flux:modal>