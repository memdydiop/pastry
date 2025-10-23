<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

new class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public int $perPage = 10;

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        Gate::authorize('view users');
    }

    #[On('user-roles-updated')]
    public function refreshUserList()
    {
        $this->resetPage();
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

    public function with(): array
    {
        if (!Gate::allows('view users')) {
            return ['users' => collect()];
        }


        $query = User::query()
            // On joint la table des profils pour accéder à 'full_name'
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            // On sélectionne les colonnes de 'users' pour éviter les conflits
            ->select('users.*');

        // Recherche par nom complet dans 'user_profiles' ou par email dans 'users'
        $query->when($this->search, function (Builder $query, $search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('user_profiles.full_name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        });

        // Filtre par rôle (ne change pas)
        $query->when($this->roleFilter, function (Builder $query, $roleName) {
            $query->whereHas('roles', function (Builder $q) use ($roleName) {
                $q->where('name', $roleName);
            });
        });

        // Logique de tri corrigée
        if ($this->sortField === 'name') {
            // On trie sur la bonne colonne de la table jointe
            $query->orderBy('user_profiles.full_name', $this->sortDirection);
        } else {
            // Tri sur les colonnes de la table 'users'
            $query->orderBy('users.' . $this->sortField, $this->sortDirection);
        }

        return [
            'users' => $query->paginate($this->perPage),
            'roles' => Role::pluck('name')->sort(),
        ];
    }
}; ?>

<x-layouts.content 
    :heading="__('Administration')" 
    :subheading="__('Gestion des Utilisateurs')"
    :pageHeading="__('Utilisateurs')" 
    :pageSubheading="__('Mettez à jour les informations de votre profil et votre avatar.')">

    <x-slot name="actions" class="flex gap-x-2">
        @can('create users')
            <livewire:admin.users.invite-user />
        @endcan
    </x-slot>

    {{-- Affichage des messages flash pour la suppression --}}
    @if (session()->has('success') || session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-init="setTimeout(() => open = false, 5000)"
            class="p-4 mb-4 text-sm {{ session()->has('success') ? 'text-green-800 bg-green-50' : 'text-red-800 bg-red-50' }} rounded-lg dark:bg-gray-800 dark:{{ session()->has('success') ? 'text-green-400' : 'text-red-400' }}"
            role="alert">
            <span class="font-medium">{{ session('success') ?? session('error') }}</span>
        </div>
    @endif


    <div class="space-y-6">
        <x-card>
            {{-- Barre de contrôles --}}
            <div class="flex flex-col sm:flex-row mb-4 space-y-4 sm:space-y-0 sm:space-x-4">
                <div class="flex items-end flex-grow space-x-4">
                    <div class="w-auto">
                        <flux:input.group label="Par page">
                            <flux:select wire:model.live="perPage" id="per-page">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </flux:select>
                        </flux:input.group>
                    </div>
    
                    <div class="flex-grow">
                        <flux:input.group label="Recherche">
                            <flux:input wire:model.live.debounce.300ms="search" type="search" icon="magnifying-glass"
                                placeholder="Nom ou email..." />
                        </flux:input.group>
                    </div>
    
                    <div class="w-auto">
                        <flux:input.group label="Rôle">
                            <flux:select wire:model.live="roleFilter">
                                <option value="">Tous les rôles</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </flux:select>
                        </flux:input.group>
                    </div>
                </div>
            </div>
    
            {{-- Table --}}
            <div class="overflow-x-auto shadow-sm ring-1 ring-gray-900/5 rounded">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">
                                {{-- Ce bouton trie maintenant sur 'full_name' --}}
                                <button wire:click="sortBy('name')" class="flex items-center gap-1.5 group">
                                    <span>Utilisateur</span>
                                    @if ($sortField === 'name')
                                        <flux:icon.chevron-up
                                            class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                    @else
                                        <flux:icon.chevrons-up-down
                                            class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="px-3 py-2.5 text-left text-sm font-semibold text-gray-500">
                                <button wire:click="sortBy('email')" class="flex items-center gap-1.5 group">
                                    <span>Contact</span>
                                    @if ($sortField === 'email')
                                        <flux:icon.chevron-up
                                            class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                    @else
                                        <flux:icon.chevrons-up-down
                                            class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="px-3 py-2.5 text-left text-sm font-semibold text-gray-500">Rôle</th>
                            <th scope="col" class="px-3 py-2.5 text-left text-sm font-semibold text-gray-500">Statut</th>
                            <th scope="col" class="px-3 py-2.5 text-left text-sm font-semibold text-gray-500 max-sm:hidden">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1.5 group">
                                    <span>Inscrit le</span>
                                    @if ($sortField === 'created_at')
                                        <flux:icon.chevron-up
                                            class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                    @else
                                        <flux:icon.chevrons-up-down
                                            class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="relative py-2.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td class="whitespace-nowrap py-2 p-3 text-sm">
                                    <div class="flex items-center gap-2 sm:gap-4">
                                        <img class="h-10 w-10 rounded-full" src="{{ $user->avatar }}"
                                            alt="{{ $user->name }}'s avatar">
                                        <div class="flex flex-col">
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->profile?->job_title }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                    <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->profile?->phone }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                    @if ($user->roles->isNotEmpty())
                                        @foreach ($user->roles as $role)
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400">Aucun rôle</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm">
                                    @if ($user->email_verified_at)
                                        <flux:badge color="success" icon="check-circle">Vérifié</flux:badge>
                                    @else
                                        <flux:badge color="warning" icon="clock">En attente</flux:badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500 max-sm:hidden">
                                    {{ $user->created_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm text-right sm:pr-6">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button icon="ellipsis-vertical" size="sm" variant="ghost" title="Actions" inset />
                                        <flux:menu class="min-w-32!">
                                            <div class="flex flex-col">
                                                @can('edit users')
                                                    <flux:button class="w-full" icon="user-circle" variant="info">
                                                        Voir le profil
                                                    </flux:button>
                                                    <flux:button class="w-full mt-1" icon="key" variant="info"
                                                        wire:click="$dispatch('openModal', { component: 'admin.users.edit-roles', arguments: { userId: {{ $user->id }} }})">
                                                        Gérer les rôles
                                                    </flux:button>
                                                @endcan
                                            </div>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-sm text-gray-500">
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Liens de Pagination --}}
            <div class="pt-4">
                {{ $users->links() }}
            </div>
        </x-card>
    </div>

    {{-- Inclusion du composant de modification des rôles --}}
    @if (auth()->user()->can('edit users'))
        <livewire:admin.users.edit-roles />
    @endif
</x-layouts.content>