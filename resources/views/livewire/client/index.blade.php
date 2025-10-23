<?php

use App\Models\Client;
use App\Enums\TypeClient;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $typeFilter = 'tous';

    #[Url]
    public bool $vipOnly = false;
    #[Url]
    public int $perPage = 10;

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingVipOnly(): void
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

    public function confirmerSuppression(int $clientId): void
    {
        $this->dispatch('confirmer-suppression', clientId: $clientId);
    }

    public function supprimerClient(int $clientId): void
    {
        $client = Client::find($clientId);

        if ($client) {
            $client->delete();
            session()->flash('success', 'Client supprimé avec succès.');
        }
    }

    public function with(): array
    {
        $query = Client::query()->with('adresses');

        // Recherche
        if ($this->search) {
            $query->search($this->search);
        }

        // Filtre par type
        if ($this->typeFilter === 'particulier') {
            $query->particuliers();
        } elseif ($this->typeFilter === 'entreprise') {
            $query->entreprises();
        }

        // Filtre VIP
        if ($this->vipOnly) {
            $query->vip();
        }

        // Tri
        $query->orderBy($this->sortField, $this->sortDirection);

        return [
            'clients' => $query->paginate(15),
            'statistiques' => [
                'total' => Client::count(),
                'particuliers' => Client::particuliers()->count(),
                'entreprises' => Client::entreprises()->count(),
                'vip' => Client::vip()->count(),
            ],
        ];
    }
}; ?>

<x-layouts.content 
    :heading="__('Administration')" 
    :subheading="__('Gestion des Utilisateurs')"
    :pageHeading="__('Clients')" 
    :pageSubheading="__('Gérez vos clients particuliers et entreprises.')">
    
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

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total clients</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $statistiques['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Particuliers</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $statistiques['particuliers'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Entreprises</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $statistiques['entreprises'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Clients VIP</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $statistiques['vip'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
            
            <!-- Recherche --><div class="flex-grow">
                <flux:input.group label="Recherche">
                    <flux:input wire:model.live.debounce.300ms="search" type="search" icon="magnifying-glass"
                        placeholder="Nom, email, téléphone..." />
                </flux:input.group>
            </div>
           

            <!-- Filtre type --><div class="w-auto">
                <flux:input.group label="Rôle">
                    <flux:select wire:model.live="typeFilter">
                        <option value="">Tous les types</option>
                        <option value="particulier">Particuliers</option>
                        <option value="entreprise">Entreprises</option>
                    </flux:select>
                </flux:input.group>
            </div>
            

            <!-- Filtre VIP -->
            {{-- <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" wire:model.live="vipOnly"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Clients VIP uniquement</span>
                </label>
            </div> --}}
        </div>
    </div>

    <!-- Tableau des clients -->
    <div class="overflow-x-auto shadow-sm ring-1 ring-gray-900/5 rounded">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                        wire:click="sortBy('nom')">
                        <div class="flex items-center space-x-1">
                            <span>Client</span>
                            @if($sortField === 'nom')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    @if($sortDirection === 'asc')
                                        <path fill-rule="evenodd"
                                            d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                            clip-rule="evenodd" />
                                    @else
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    @endif
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Inscription
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clients as $client)
                    <tr class="hover:bg-gray-50 transition" wire:key="client-{{ $client->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold text-sm">{{ $client->initiales }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $client->nom_complet }}
                                        @if($client->estVip())
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                ⭐ VIP
                                            </span>
                                        @endif
                                    </div>
                                    @if($client->adresse_default)
                                        <div class="text-sm text-gray-500">{{ $client->adresse_default->ville }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $client->email }}</div>
                            <div class="text-sm text-gray-500">{{ $client->telephone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $client->type->value === 'entreprise' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $client->type->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="h-2 w-20 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-400 to-indigo-600"
                                            style="width: {{ $client->score_client }}%"></div>
                                    </div>
                                </div>
                                <div class="ml-2 text-sm text-gray-600">{{ number_format($client->score_client, 0) }}</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ $client->points_fidelite }} pts</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $client->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('clients.show', $client) }}"
                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                Voir
                            </a>
                            <a href="{{ route('clients.edit', $client) }}" class="text-gray-600 hover:text-gray-900 mr-3">
                                Modifier
                            </a>
                            <button wire:click="confirmerSuppression({{ $client->id }})"
                                class="text-red-600 hover:text-red-900">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun client trouvé</h3>
                            <p class="mt-1 text-sm text-gray-500">Commencez par créer un nouveau client.</p>
                            <div class="mt-6">
                                <a href="{{ route('clients.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Nouveau client
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $clients->links() }}
    </div></x-card>
    </div>
</x-layouts.content>

@script
<script>
    $wire.on('confirmer-suppression', (event) => {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
            $wire.supprimerClient(event.clientId);
        }
    });
</script>
@endscript