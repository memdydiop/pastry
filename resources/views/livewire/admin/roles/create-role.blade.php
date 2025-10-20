<?php

use Livewire\Volt\Component;
use App\Services\RoleService;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends Component {

    public bool $modalOpen = false;
    public array $groupedPermissions = [];

    #[Validate('required|string|min:2|max:50|unique:roles,name')]
    public string $name = '';

    #[Validate('array')]
    public array $selectedPermissions = [];

    #[On('open-create-role-modal')]
    public function openModal(): void
    {
        $this->resetForm();

        $roleService = app(RoleService::class);
        $this->groupedPermissions = $roleService->getGroupedPermissions();

        $this->modalOpen = true;
    }

    public function createRole(): void
    {
        $this->validate();

        try {
            $roleService = app(RoleService::class);
            $roleService->createRole($this->name, $this->selectedPermissions);

            $this->dispatch('role-created');
            session()->flash('success', "Le rôle \"{$this->name}\" a été créé avec succès !");

            $this->closeModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création du rôle.');
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
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'selectedPermissions']);
        $this->resetValidation();
    }
};

?>

<flux:modal wire:model="modalOpen" title="Créer un nouveau rôle" class="max-w-3xl">
    <form wire:submit="createRole" class="space-y-6">

        {{-- Nom du rôle --}}
        <flux:input wire:model="name" label="Nom du rôle" placeholder="Ex: Manager, Support, Comptable..." required />

        {{-- Permissions par catégorie --}}
        <div class="space-y-4">
            <flux:heading size="lg">Permissions</flux:heading>
            <flux:subheading>Sélectionnez les permissions à attribuer à ce rôle</flux:subheading>

            <div class="space-y-4 max-h-96 overflow-y-auto border rounded-lg p-4">
                @foreach ($groupedPermissions as $category => $permissions)
                    <div class="border-b pb-4 last:border-b-0">
                        {{-- En-tête de catégorie avec toggle --}}
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

                        {{-- Liste des permissions --}}
                        <div class="grid grid-cols-2 gap-2 ml-7">
                            @foreach ($permissions as $permission)
                                <flux:checkbox wire:model="selectedPermissions" value="{{ $permission->name }}"
                                    label="{{ ucfirst(str_replace('_', ' ', $permission->name)) }}" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if(empty($selectedPermissions))
                <flux:text sm color="warning" class="flex items-center gap-2">
                    <flux:icon.exclamation-triangle class="w-4 h-4" />
                    Aucune permission sélectionnée. Le rôle sera créé sans permissions.
                </flux:text>
            @else
                <flux:text sm color="muted">
                    {{ count($selectedPermissions) }} permission(s) sélectionnée(s)
                </flux:text>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-2 pt-4 border-t">
            <flux:button type="button" variant="secondary" wire:click="closeModal">
                Annuler
            </flux:button>
            <flux:button type="submit" variant="primary">
                <span wire:loading.remove wire:target="createRole">Créer le rôle</span>
                <span wire:loading wire:target="createRole">Création...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>