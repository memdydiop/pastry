<?php
// ... (code PHP de la classe Volt inchangé)

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On; // Ajouté pour l'écoute
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

new #[Title('Gestion des utilisateurs')]
    class extends Component {

    use WithPagination;

    #[Url]
    public string $search = '';
    #[Url]
    public string $roleFilter = '';
    #[Url]
    public int $perPage = 10;
    public array $availableRoles = [];

    // Écoute l'événement du composant modal pour rafraîchir la liste
    #[On('roles-updated')]
    public function refreshList()
    {
        $this->resetPage(); // On réinitialise la page pour un rafraîchissement complet
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'roleFilter', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        // Vérifie la permission d'accès à la page
        Gate::authorize('view users'); // ⬅️ Utilisation de Gate::authorize

        $this->availableRoles = Role::pluck('name', 'name')->toArray();
    }

    public function with(): array
    {

        // Le with() est appelé même si l'utilisateur n'a pas la permission, 
        // donc on peut retourner une collection vide si l'accès a échoué au mount.
        if (!Gate::allows('view users')) {
            return ['users' => collect()];
        }

        $users = User::with(['roles', 'profile'])
            ->select([
                'users.*',
                'email_verified_at',
                'two_factor_confirmed_at',
                'two_factor_secret',
                'created_at',
                'profile_completed'
            ])
            ->when($this->search, function (Builder $query, $search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function (Builder $query) use ($search) {
                        $query->where('full_name', 'like', "%{$search}%");
                    });
            })
            ->when($this->roleFilter, fn(Builder $query, $role) => $query->role($role))
            ->orderBy('created_at', 'asc')
            ->paginate($this->perPage);

        return [
            'users' => $users,
        ];
    }

    /**
     * Méthode de suppression d'utilisateur (avec vérifications de sécurité).
     */
    public function deleteUser(int $userId): void
    {
        // ⬅️ Utilisation de Gate::authorize pour lever automatiquement une exception 403
        Gate::authorize('delete users');

        $user = User::find($userId);

        if ($user) {
            if ($user->id === auth()->id()) {
                session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
                return;
            }

            // Supprimer l'utilisateur
            $user->delete();
            $this->resetPage(); // Reset pour rafraîchir la liste après suppression
            session()->flash('success', 'Utilisateur supprimé avec succès.');
        }
    }
};
?>

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
        <x-card class="">

            {{-- Bloc de Contrôles: Par Page, Recherche, Filtre Rôle --}}
            <div class="flex flex-col sm:flex-row mb-4 space-y-4 sm:space-y-0 sm:space-x-4">

                <div class="flex items-end flex-grow space-x-4">
                    {{-- Éléments par Page --}}
                    <div class="w-auto">
                        <flux:input.group label="Par page">
                            <flux:select wire:model.live="perPage" id="per-page">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </flux:select>
                        </flux:input.group>
                    </div>

                    {{-- Recherche --}}
                    <div class="flex-grow">
                        <flux:input.group label="Recherche">
                            <flux:input wire:model.live.debounce.300ms="search" type="search" icon="magnifying-glass"
                                placeholder="Nom ou Email" />
                        </flux:input.group>
                    </div>

                    {{-- Filtre Rôle --}}
                    <div class="w-auto">
                        <flux:input.group label="Rôle">
                            <flux:select wire:model.live="roleFilter">
                                <option value="">Tous les rôles</option>
                                @foreach ($availableRoles as $roleName)
                                    <flux:select.option value="{{ $roleName }}">
                                        {{ ucfirst($roleName) }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:input.group>
                    </div>
                </div>

                <div class="actions sm:hidden"></div>

            </div>

            {{-- Tableau des Utilisateurs --}}
            <div class="overflow-x-auto shadow-sm ring-1 ring-gray-900/5 rounded">
                <table class="min-w-full divide-y divide-gray-300 ">
                    <thead class="bg-gray-50 ">
                        <tr>
                            <th scope="col" class="py-2.5 p-3 text-left text-sm font-semibold text-gray-900">
                                Utilisateur</th>
                            {{-- <th scope="col"
                                class="p-3 py-2.5 text-left text-sm font-semibold text-gray-900 max-sm:hidden">
                                Email</th> --}}
                            <th scope="col" class="p-3 py-2.5 text-left text-sm font-semibold text-gray-900 ">
                                Rôles</th>
                            <th scope="col"
                                class="p-3 py-2.5 text-center text-sm font-semibold text-gray-900 max-sm:hidden"
                                title="Statut de vérification de l'adresse email">
                                Email Vérifié</th>
                            <th scope="col"
                                class="p-3 py-2.5 text-center text-sm font-semibold text-gray-900 max-sm:hidden"
                                title="Statut de l'authentification à deux facteurs">
                                2FA</th>
                            <th scope="col"
                                class="p-3 py-2.5 text-left text-sm font-semibold text-gray-900 max-sm:hidden"
                                title="Date d'inscription de l'utilisateur">
                                Inscrit le</th>
                            <th scope="col"
                                class="p-3 py-2.5 text-center text-sm font-semibold text-gray-900 max-sm:hidden"
                                title="Statut de complétion du profil">
                                Profil Complété</th>
                            <th scope="col" class="relative py-2.5 pl-3 pr-4 sm:pr-6">
                                <span class=" sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white ">
                        @forelse ($users as $user)
                            <tr wire:key="{{ $user->id }}">
                                <td class="whitespace-nowrap py-2 p-3 text-sm">
                                    <div class="flex items-center gap-2 sm:gap-4">
                                        <flux:avatar size="lg" class="max-sm:size-8" src="{{ $user->avatar }}"
                                            alt="{{ $user->initials() }}" />
                                        <div class="flex flex-col">
                                            <flux:heading class="">
                                                {{ $user->name }}
                                                @if ($user->is(auth()->user()))
                                                    <flux:badge size="sm" color="blue"
                                                        class=" rounded-full! p-0! size-2 bg-success  ">

                                                    </flux:badge>
                                                @endif
                                            </flux:heading>
                                            <flux:text class="text-gray-500">{{ $user->email }}</flux:text>
                                        </div>
                                    </div>
                                </td>
                                {{-- <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500 max-sm:hidden">
                                    {{ $user->email }}
                                </td> --}}
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500 ">
                                    @forelse ($user->roles as $role)
                                        <flux:badge color="primary" class="mr-1 max-sm:block max-sm:mb-1">
                                            {{ ucfirst($role->name) }}
                                        </flux:badge>
                                    @empty
                                        <flux:badge color="secondary">Aucun</flux:badge>
                                    @endforelse
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-center max-sm:hidden">
                                    @if ($user->hasVerifiedEmail())
                                        <flux:icon.check-circle class="w-5 h-5 text-success mx-auto" title="Email vérifié" />
                                    @else
                                        <flux:icon.x-circle class="w-5 h-5 text-danger mx-auto" title="Email non vérifié" />
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-center max-sm:hidden">
                                    @if ($user->two_factor_confirmed_at)
                                        <flux:icon.shield-check class="w-5 h-5 text-success mx-auto" title="2FA activé" />
                                    @elseif ($user->two_factor_secret)
                                        <flux:icon.exclamation-triangle class="w-5 h-5 text-warning mx-auto"
                                            title="2FA en attente de confirmation" />
                                    @else
                                        <flux:icon.lock-open class="w-5 h-5 text-gray-400 mx-auto" title="2FA désactivé" />
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500 max-sm:hidden">
                                    {{ $user->created_at->isoFormat('D MMM YYYY') }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-2 text-sm text-center max-sm:hidden">
                                    @if ($user->profile_completed)
                                        <flux:icon.check-circle class="w-5 h-5 text-success mx-auto" title="Profil complet" />
                                    @else
                                        <flux:icon.x-circle class="w-5 h-5 text-danger mx-auto" title="Profil incomplet" />
                                    @endif
                                </td>

                                {{-- COLONNE ACTIONS CORRIGÉE --}}
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm text-right sm:pr-6">
                                    <flux:dropdown position="bottom" align="end">

                                        <flux:button icon="ellipsis-vertical" size="sm" variant="ghost" title="Actions"
                                            inset />


                                        <flux:menu class="min-w-32!">
                                            <div class="space-y-1">
                                                {{-- Ouvre la modale EditRoles --}}
                                                @can('edit users')
                                                    <flux:button class="w-full" icon="pencil-square" variant="info"
                                                        wire:click="$dispatch('edit-user-roles', { userId: {{ $user->id }} })">
                                                        Rôles
                                                    </flux:button>
                                                @endcan
                                            </div>

                                            {{-- Option Supprimer --}}
                                            @if ($user->id !== auth()->id() && Gate::allows('delete users'))
                                                <flux:menu.separator />
                                                <flux:button class="w-full" icon="trash"
                                                    wire:click="deleteUser({{ $user->id }})" variant="danger"
                                                    confirm="Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.">
                                                    Supprimer
                                                </flux:button>

                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-sm text-gray-500 ">
                                    Aucun utilisateur trouvé correspondant aux critères de recherche.
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