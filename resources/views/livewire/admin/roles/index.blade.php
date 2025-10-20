<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use App\Services\RoleService;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Gate;

new #[Title('Gestion des rôles')]
    class extends Component {

    #[On('role-updated')]
    #[On('role-created')]
    #[On('role-deleted')]
    public function refreshList(): void
    {
        // Forcer le rafraîchissement
    }

    public function mount(): void
    {
        Gate::authorize('view roles');
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
        $roleService = app(RoleService::class);

        return [
            'roles' => $roleService->getAllRoles(),
        ];
    }
};

?>

<x-layouts.content :heading="__('Rôles & Permissions')" :subheading="__('Gérez les rôles et leurs permissions')"
    :pageHeading="__('Administration')" :pageSubheading="__('Configuration des accès utilisateurs')">

    <x-slot name="actions">
        @can('create roles')
            <flux:button icon="plus" variant="primary" wire:click="$dispatch('open-create-role-modal')"> 
                Rôle
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                                Rôle
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900">
                                Utilisateurs
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900">
                                Permissions
                            </th>
                            <th scope="col" class="px-3 py-3 text-center text-sm font-semibold text-gray-900">
                                Statut
                            </th>
                            <th scope="col" class="relative py-3 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($roles as $role)
                            <tr wire:key="role-{{ $role->id }}" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full {{ $role->name === 'Ghost' ? 'bg-purple-100 text-purple-600' : ($role->name === 'admin' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600') }}">
                                            <flux:icon.shield-check class="w-5 h-5" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-medium text-gray-900">
                                                {{ $role->name }}
                                                @if(in_array($role->name, ['Ghost', 'admin']))
                                                    <flux:badge size="sm" color="blue" class="ml-2">Système</flux:badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.users class="w-4 h-4" />
                                        <span class="font-medium">{{ $role->users_count }}</span>
                                    </div>
                                </td>

                                <td class="px-3 py-2 text-sm text-gray-500">
                                    <div class="flex flex-wrap gap-1">
                                        @if($role->permissions->count() > 0)
                                            <flux:badge color="secondary">
                                                {{ $role->permissions->count() }} permission(s)
                                            </flux:badge>
                                        @else
                                            <span class="text-gray-400">Aucune</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-center">
                                    @if(in_array($role->name, ['Ghost', 'admin']))
                                        <flux:badge color="warning">Protégé</flux:badge>
                                    @else
                                        <flux:badge color="success">Modifiable</flux:badge>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm sm:pr-6">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button icon="ellipsis-vertical" size="sm" variant="ghost" inset />

                                        <flux:menu class="min-w-32!">
                                            <div class="space-y-1 flex flex-col">
                                                @can('view roles')
                                                    <flux:button 
                                                        class="w-full"
                                                        icon="eye" variant="info"
                                                        wire:click="$dispatch('view-role-permissions', { roleId: {{ $role->id }} })">
                                                        Permissions
                                                    </flux:button>
                                                @endcan
                                                @can('edit roles')
                                                    <flux:button 
                                                        icon="pencil-square" variant="warning"
                                                        wire:click="$dispatch('edit-role', { roleId: {{ $role->id }} })">
                                                        Modifier
                                                    </flux:button>
                                                @endcan
                                            </div>

                                            @can('delete roles')
                                                @if(!in_array($role->name, ['Ghost', 'admin']))
                                                    <flux:menu.separator />
                                                    <flux:button 

                                                        class="w-full"
                                                        icon="trash" 
                                                        wire:click="deleteRole({{ $role->id }})" variant="danger"
                                                        confirm="Êtes-vous sûr de vouloir supprimer ce rôle ?">
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
                                    Aucun rôle trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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