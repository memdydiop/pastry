<?php

use Livewire\Volt\Component;
use App\Models\RoleAudit;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

new #[Title('Historique des rôles')]
class extends Component {

    use WithPagination;

    #[Url]
    public string $actionFilter = '';
    
    #[Url]
    public int $perPage = 20;

    public function with(): array
    {
        $audits = RoleAudit::with(['user', 'role'])
            ->when($this->actionFilter, fn($q) => $q->byAction($this->actionFilter))
            ->latest()
            ->paginate($this->perPage);

        return [
            'audits' => $audits,
            'actions' => ['created', 'updated', 'deleted', 'permissions_changed'],
        ];
    }

    public function getActionBadgeColor(string $action): string
    {
        return match($action) {
            'created' => 'success',
            'updated' => 'blue',
            'deleted' => 'danger',
            'permissions_changed' => 'warning',
            default => 'secondary',
        };
    }

    public function getActionIcon(string $action): string
    {
        return match($action) {
            'created' => 'plus-circle',
            'updated' => 'pencil-square',
            'deleted' => 'trash',
            'permissions_changed' => 'shield-check',
            default => 'information-circle',
        };
    }
};

?>

<x-layouts.content 
    :heading="__('Historique des Modifications')" 
    :subheading="__('Audit des actions sur les rôles et permissions')">

    <x-slot name="actions">
        <flux:button :href="route('admin.roles.index')" wire:navigate variant="secondary">
            <flux:icon.arrow-left class="w-5 h-5" />
            Retour aux rôles
        </flux:button>
    </x-slot>

    <div class="space-y-6">
        <x-card>
            {{-- Filtres --}}
            <div class="flex gap-4 mb-6">
                <flux:select wire:model.live="actionFilter" label="Action">
                    <option value="">Toutes les actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="perPage" label="Par page">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </flux:select>
            </div>

            {{-- Timeline --}}
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @forelse ($audits as $index => $audit)
                        <li wire:key="audit-{{ $audit->id }}">
                            <div class="relative pb-8">
                                @if (!$loop->last)
                                    <span class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                
                                <div class="relative flex items-start space-x-3">
                                    {{-- Icône --}}
                                    <div class="relative">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full 
                                            bg-{{ $this->getActionBadgeColor($audit->action) }}-100 
                                            ring-8 ring-white">
                                            <flux:icon.{{ $this->getActionIcon($audit->action) }} 
                                                class="h-5 w-5 text-{{ $this->getActionBadgeColor($audit->action) }}-600" />
                                        </div>
                                    </div>

                                    {{-- Contenu --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <flux:heading size="sm">
                                                    {{ $audit->user->name }}
                                                    <flux:badge color="{{ $this->getActionBadgeColor($audit->action) }}" class="ml-2">
                                                        {{ $audit->action }}
                                                    </flux:badge>
                                                </flux:heading>
                                                
                                                <flux:text sm color="muted" class="mt-0.5">
                                                    Rôle: <span class="font-medium">{{ $audit->role_name }}</span>
                                                </flux:text>
                                            </div>
                                            
                                            <flux:text xs color="muted">
                                                {{ $audit->created_at->diffForHumans() }}
                                            </flux:text>
                                        </div>

                                        {{-- Détails des changements --}}
                                        @if($audit->old_data || $audit->new_data)
                                            <div class="mt-2 bg-gray-50 rounded-lg p-3 text-sm">
                                                @if($audit->action === 'updated')
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <flux:text xs color="muted">Avant</flux:text>
                                                            <pre class="mt-1 text-xs">{{ json_encode($audit->old_data, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                        <div>
                                                            <flux:text xs color="muted">Après</flux:text>
                                                            <pre class="mt-1 text-xs">{{ json_encode($audit->new_data, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                    </div>
                                                @elseif($audit->action === 'created')
                                                    <flux:text xs>
                                                        Permissions initiales: {{ count($audit->new_data['permissions'] ?? []) }}
                                                    </flux:text>
                                                @elseif($audit->action === 'deleted')
                                                    <flux:text xs>
                                                        Permissions supprimées: {{ count($audit->old_data['permissions'] ?? []) }}
                                                    </flux:text>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Métadonnées --}}
                                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <flux:icon.globe-alt class="w-4 h-4" />
                                                {{ $audit->ip_address }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <div class="text-center py-12">
                            <flux:icon.document-text class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                            <flux:text color="muted">Aucun historique disponible</flux:text>
                        </div>
                    @endforelse
                </ul>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $audits->links() }}
            </div>
        </x-card>
    </div>
</x-layouts.content>