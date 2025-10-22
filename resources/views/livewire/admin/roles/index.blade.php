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
                            <th class="py-2.5 px-3 text-left text-sm font-semibold text-gray-900">
                                Rôle
                            </th>
                            <th class="py-2.5 px-3 text-left text-sm font-semibold text-gray-900">
                                Utilisateurs
                            </th>
                            <th class="py-2.5 px-3 text-left text-sm font-semibold text-gray-900">
                                Permissions
                            </th>
                            <th class="py-2.5 px-3 text-center text-sm font-semibold text-gray-900">
                                Statut
                            </th>
                            <th class="py-2.5 px-3 text-right text-sm font-semibold text-gray-900">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($roles as $role)
                            @php
                                $isProtected = in_array($role->name, $protectedRoles);
                                $bgColor = $role->name === 'Ghost' ? 'bg-purple-100 text-purple-600' :
                                    ($role->name === 'admin' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600');
                            @endphp

                            <tr wire:key="role-{{ $role->id }}" class="hover:bg-gray-50">
                                {{-- Colonne Rôle --}}
                                <td class="whitespace-nowrap py-2 px-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full {{ $bgColor }}">
                                            <flux:icon.shield-check class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $role->name }}</span>
                                            @if($isProtected)
                                                <span
                                                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Système
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Colonne Utilisateurs --}}
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.users class="w-4 h-4" />
                                        <span class="font-medium">{{ $role->users_count }}</span>
                                    </div>
                                </td>

                                {{-- Colonne Permissions --}}
                                <td class="px-3 py-2 text-sm text-gray-500">
                                    @if($role->permissions_count > 0)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $role->permissions_count }} permission(s)
                                        </span>
                                    @else
                                        <span class="text-gray-400">Aucune</span>
                                    @endif
                                </td>

                                {{-- Colonne Statut --}}
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

                                {{-- Colonne Actions --}}
                                <td class="whitespace-nowrap py-2 px-3 text-right text-sm">

                                    <flux:dropdown position="bottom" align="end">

                                        <flux:button icon="ellipsis-vertical" size="sm" variant="ghost" title="Actions"
                                            inset />

                                        <flux:menu class="min-w-32!">
                                            <div class="flex flex-col items-center space-y-1">
                                                {{-- Ouvre la modale ViewRolePermissions --}}
                                                @can('edit users')
                                                    <flux:button class="w-full" icon="eye" variant="info"
                                                        wire:click="$dispatch('view-role-permissions', { roleId: {{ $role->id }} })">
                                                        Permissions
                                                    </flux:button>
                                                @endcan{{-- Ouvre la modale EditRole --}}
                                                @can('edit roles')
                                                    <flux:button class="w-full" icon="pencil-square" variant="info"
                                                        wire:click="$dispatch('edit-role', { roleId: {{ $role->id }} })">
                                                        Modifier
                                                    </flux:button>
                                                @endcan
                                            </div>

                                            {{-- Option Supprimer --}}
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
                                <td colspan="5" class="text-center py-8 text-sm text-gray-500">
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