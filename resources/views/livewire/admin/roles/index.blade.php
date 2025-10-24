<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use App\Services\RoleService;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;

new #[Title('Gestion des rôles')]
    class extends Component {

    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public int $perPage = 10;

    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    #[On('role-updated')]
    #[On('role-created')]
    #[On('role-deleted')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        Gate::authorize('view roles');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteRole(int $roleId): void
    {
        Gate::authorize('delete roles');

        try {
            $role = Role::findOrFail($roleId);
            $roleService = app(RoleService::class);
            $roleService->deleteRole($role);

            $this->dispatch('role-deleted');
            session()->flash('success', "Le rôle \"{$role->name}\" a été supprimé.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la suppression du rôle.');
        }
    }

    public function with(): array
    {
        if (!Gate::allows('view roles')) {
            return ['roles' => collect()];
        }

        $roles = Role::withCount(['users', 'permissions'])
            ->when($this->search, function (Builder $query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'roles' => $roles,
            'protectedRoles' => ['Ghost', 'Admin'],
        ];
    }
};

?>

<x-layouts.content 
    :heading="__('Administration')" 
    :subheading="__('Gestion des Utilisateurs')" 
    :pageHeading="__('Rôles & Permissions')" 
    :pageSubheading="__('Gérez les rôles et leurs permissions')">

    <x-slot name="actions">
        @can('create roles')
            <flux:button icon="plus" variant="primary" wire:click="$dispatch('open-create-role-modal')">
                Nouveau Rôle
            </flux:button>
        @endcan
    </x-slot>

    {{-- Messages Flash --}}
    @if (session()->has('success') || session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-init="setTimeout(() => open = false, 5000)"
            class="p-4 mb-4 text-sm rounded-lg {{ session()->has('success') ? 'text-green-800 bg-green-50' : 'text-red-800 bg-red-50' }}"
            role="alert">
            <span class="font-medium">{{ session('success') ?? session('error') }}</span>
        </div>
    @endif

    <div class="space-y-6">
        <x-card>
            {{-- Barre de contrôles --}}
            <div class="flex flex-col sm:flex-row mb-4 space-y-4 sm:space-y-0 sm:space-x-4">
                <div class="w-auto">
                    <flux:input.group label="Par page">
                        <flux:select wire:model.live="perPage">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </flux:select>
                    </flux:input.group>
                </div>

                <div class="flex-grow">
                    <flux:input.group label="Recherche">
                        <flux:input wire:model.live.debounce.300ms="search" type="search" icon="magnifying-glass"
                            placeholder="Nom du rôle" />
                    </flux:input.group>
                </div>
            </div>

            {{-- Table optimisée --}}
            <div class="overflow-x-auto shadow-sm ring-1 ring-gray-900/5 rounded">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">
                                <button wire:click="sortBy('name')" class="flex items-center gap-1.5 group">
                                    <span>Rôle</span>
                                    @if ($sortField === 'name')
                                        <flux:icon.chevron-up
                                            class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                    @else
                                        <flux:icon.chevrons-up-down
                                            class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">
                                <button wire:click="sortBy('users_count')" class="flex items-center gap-1.5 group">
                                    <span>Utilisateurs</span>
                                    @if ($sortField === 'users_count')
                                        <flux:icon.chevron-up
                                            class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                    @else
                                        <flux:icon.chevrons-up-down
                                            class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">
                                <button wire:click="sortBy('permissions_count')"
                                    class="flex items-center gap-1.5 group">
                                    <span>Permissions</span>
                                    @if ($sortField === 'permissions_count')
                                        <flux:icon.chevron-up
                                            class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                    @else
                                        <flux:icon.chevrons-up-down
                                            class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="py-2.5 px-3 text-center text-sm font-semibold text-gray-500">Statut
                            </th>
                            <th scope="col" class="relative py-2.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($roles as $role)
                            @php
                                $isProtected = in_array($role->name, $protectedRoles);
                            @endphp

                            <tr wire:key="role-{{ $role->id }}">
                                <td class="whitespace-nowrap py-2 p-3 text-sm">
                                    <div class="flex items-center">
                                        <div class="font-medium text-gray-900">{{ $role->name }}</div>
                                        @if($isProtected)
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Système
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                    {{ $role->users_count }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                    @if($role->permissions_count > 0)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $role->permissions_count }} permission(s)
                                        </span>
                                    @else
                                        <span class="text-gray-400">Aucune</span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-center">
                                    @if($isProtected)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Protégé
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Modifiable
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm text-right sm:pr-6">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button icon="ellipsis-vertical" size="sm" variant="ghost" title="Actions"
                                            inset />
                                        <flux:menu class="min-w-32!">
                                            <div class="space-y-1 flex flex-col">
                                                @can('edit users')
                                                    <flux:button class="w-full" icon="eye" variant="info"
                                                        wire:click="$dispatch('view-role-permissions', { roleId: {{ $role->id }} })">
                                                        Permissions
                                                    </flux:button>
                                                @endcan
                                                @can('edit roles')
                                                    <flux:button class="w-full" icon="pencil-square" variant="info"
                                                        wire:click="$dispatch('edit-role', { roleId: {{ $role->id }} })">
                                                        Modifier
                                                    </flux:button>
                                                @endcan
                                            </div>
                                            @can('delete roles')
                                                @if(!$isProtected)
                                                    <flux:menu.separator />
                                                    <flux:button class="w-full" icon="trash"
                                                        wire:click="deleteRole({{ $role->id }})" variant="danger"
                                                        confirm="Êtes-vous sûr de vouloir supprimer ce rôle ? Cette action est irréversible.">
                                                        Supprimer
                                                    </flux:button>
                                                @endif
                                            @endcan
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-sm text-gray-500 ">
                                    @if($search)
                                        Aucun rôle ne correspond à "{{ $search }}"
                                    @else
                                        Aucun rôle trouvé.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pt-4">
                {{ $roles->links() }}
            </div>
        </x-card>
    </div>

    {{-- Modals --}}
    @can('create roles')
        <livewire:admin.roles.create-role />
    @endcan

    @can('edit roles')
        <livewire:admin.roles.edit-role />
    @endcan

    @can('view roles')
        <livewire:admin.roles.view-permissions />
    @endcan

</x-layouts.content>