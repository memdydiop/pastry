<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Models\Invitation;
use App\Mail\UserInvitationMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL as UrlBuilder;

new #[Title('Gestion des invitations')]
    class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public int $perPage = 10;

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        Gate::authorize('create users');
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

    public function resendInvitation(int $invitationId): void
    {
        Gate::authorize('create users');

        $invitation = Invitation::findOrFail($invitationId);

        if ($invitation->registered_at) {
            session()->flash('error', 'Cet utilisateur est déjà inscrit.');
            return;
        }

        $signedUrl = UrlBuilder::temporarySignedRoute(
            'register.invitation',
            now()->addDays(7),
            ['token' => $invitation->token]
        );

        Mail::to($invitation->email)->send(new UserInvitationMail($signedUrl));

        $invitation->touch();

        session()->flash('success', "L'invitation pour {$invitation->email} a été renvoyée.");
    }

    public function with(): array
    {
        $invitations = Invitation::query()
            ->when($this->search, function (Builder $query, $search) {
                $query->where('email', 'like', "%{$search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return [
            'invitations' => $invitations,
        ];
    }
};
?>

<x-layouts.content :heading="__('Administration')" :subheading="__('Gestion des Utilisateurs.')"
    :pageHeading="__('Invitations')" :pageSubheading="__('Suivez et gérez les invitations envoyées aux nouveaux utilisateurs.')">

    {{-- Messages Flash --}}
    @if (session()->has('success') || session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-init="setTimeout(() => open = false, 5000)"
            class="p-4 mb-4 text-sm rounded-lg {{ session()->has('success') ? 'text-green-800 bg-green-50' : 'text-red-800 bg-red-50' }}"
            role="alert">
            <span class="font-medium">{{ session('success') ?? session('error') }}</span>
        </div>
    @endif

    <x-card>
        {{-- Barre de recherche et filtres --}}
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
                            placeholder="Rechercher par email..." />
                    </flux:input.group>
                </div>
            </div>
        </div>

        {{-- Tableau des invitations --}}
        <div class="overflow-x-auto shadow-sm ring-1 ring-gray-900/5 rounded">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">
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
                        <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">Statut</th>
                        <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1.5 group">
                                <span>Date d'envoi</span>
                                @if ($sortField === 'created_at')
                                    <flux:icon.chevron-up
                                        class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                @else
                                    <flux:icon.chevrons-up-down
                                        class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="py-2.5 px-3 text-left text-sm font-semibold text-gray-500">
                            <button wire:click="sortBy('registered_at')" class="flex items-center gap-1.5 group">
                                <span>Date d'inscription</span>
                                @if ($sortField === 'registered_at')
                                    <flux:icon.chevron-up
                                        class="w-3 h-3 transition-transform {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" />
                                @else
                                    <flux:icon.chevrons-up-down
                                        class="w-3 h-3 text-gray-400 transition-opacity group-hover:opacity-100 opacity-0" />
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="relative py-2.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($invitations as $invitation)
                        <tr wire:key="invitation-{{ $invitation->id }}">
                            <td class="whitespace-nowrap py-2 p-3 text-sm font-medium text-gray-900">
                                {{ $invitation->email }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm">
                                @if ($invitation->registered_at)
                                    <flux:badge color="success" icon="check-circle">Inscrit</flux:badge>
                                @else
                                    <flux:badge color="warning" icon="clock">En attente</flux:badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                {{ $invitation->created_at->translatedFormat('d F Y à H:i') }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                {{ $invitation->registered_at ? $invitation->registered_at->translatedFormat('d F Y à H:i') : '—' }}
                            </td>
                            <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm text-right sm:pr-6">
                                @if (!$invitation->registered_at)
                                    <flux:button wire:click="resendInvitation({{ $invitation->id }})" variant="secondary"
                                        size="sm" confirm="Êtes-vous sûr de vouloir renvoyer l'invitation à cet utilisateur ?">
                                        Renvoyer
                                    </flux:button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-sm text-gray-500">
                                Aucune invitation trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $invitations->links() }}
        </div>
    </x-card>

</x-layouts.content>