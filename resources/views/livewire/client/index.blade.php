<?php

use App\Models\Client;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public int $perPage = 10;

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        Gate::authorize('view clients');
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

    public function deleteClient(Client $client): void
    {
        Gate::authorize('delete clients');
        $client->delete();
        session()->flash('success', 'Client supprimé avec succès.');
    }

    public function with(): array
    {
        $clients = Client::query()
            ->when($this->search, function (Builder $query) {
                $query->where('nom_complet', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('telephone', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

            

        return [
            'clients' => $clients,
        ];
    }
}; ?>

<x-layouts.content heading="Gestion des Clients"
    subheading="Affichez, créez et gérez les fiches de vos clients.">
    <x-slot name="actions">
        @can('create clients')
            <flux:button wire:click="$dispatch('openModal', { component: 'client.create-client' })" icon="plus"
                variant="primary">
                Nouveau Client
            </flux:button>
        @endcan
    </x-slot>

    <x-card>
        {{-- Barre de contrôles --}}
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
                            placeholder="Nom, email ou téléphone..." />
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
                            <button wire:click="sortBy('nom_complet')" class="flex items-center gap-1.5 group">
                                <span>Nom du client</span>
                                @if ($sortField === 'nom_complet')
                                    <flux:icon.chevron-up
                                        class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                @else
                                    <flux:icon.chevrons-up-down
                                        class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-3 py-2.5 text-left text-sm font-semibold text-gray-500">
                            <button wire:click="sortBy('type_client')" class="flex items-center gap-1.5 group">
                                <span>Type</span>
                                @if ($sortField === 'type_client')
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
                                <span>Email</span>
                                @if ($sortField === 'email')
                                    <flux:icon.chevron-up
                                        class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                @else
                                    <flux:icon.chevrons-up-down
                                        class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-3 py-2.5 text-left text-sm font-semibold text-gray-500">
                            <button wire:click="sortBy('telephone')" class="flex items-center gap-1.5 group">
                                <span>Téléphone</span>
                                @if ($sortField === 'telephone')
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
                    @forelse ($clients as $client)
                        <tr wire:key="client-{{ $client->id }}">
                            <td class="whitespace-nowrap py-2 p-3 text-sm font-medium text-gray-900">
                                {{ $client->nom_complet }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500 {{ $client->type->value === 'entreprise' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $client->type_client->label() }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                {{ $client->email }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                {{ $client->telephone }}
                            </td>
                            <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm text-right sm:pr-6">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button icon="ellipsis-vertical" size="sm" variant="ghost"
                                        title="Actions" inset />
                                    <flux:menu class="min-w-32!">
                                        <div class="flex flex-col">
                                            @can('view clients')
                                                <flux:button class="w-full" icon="eye" variant="info"
                                                    wire:click="$dispatch('openModal', { component: 'client.show-client', arguments: { client: {{ $client->id }} }})">
                                                    Voir
                                                </flux:button>
                                            @endcan
                                            @can('edit clients')
                                                <flux:button class="w-full mt-1" icon="pencil-square" variant="info"
                                                    wire:click="$dispatch('openModal', { component: 'client.edit-client', arguments: { client: {{ $client->id }} }})">
                                                    Modifier
                                                </flux:button>
                                            @endcan
                                        </div>
                                        @can('delete clients')
                                             <flux:menu.separator />
                                            <flux:button class="w-full" icon="trash" variant="danger"
                                                wire:click="deleteClient({{ $client->id }})"
                                                confirm="Êtes-vous sûr de vouloir supprimer ce client ?">
                                                Supprimer
                                            </flux:button>
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-sm text-gray-500">
                                Aucun client trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $clients->links() }}
        </div>
    </x-card>
</x-layouts.content>