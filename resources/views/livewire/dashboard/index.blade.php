<?php

use Livewire\Volt\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;

new #[Title('Tableau de bord')]
    class extends Component {
    public string $full_name = '';
    public string $email = '';
    public array $stats = [];
    public array $rolesDistribution = [];

    public function mount(): void
    {
        $this->full_name = Auth::user()->profile->full_name;
        $this->email = Auth::user()->email;
        $this->loadStats();
        $this->loadRolesDistribution();
    }

    protected function loadStats(): void
    {
        $this->stats = Cache::remember('dashboard:stats', 600, function () {
            return [
                'total_users' => User::count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
                'completed_profiles' => User::where('profile_completed', true)->count(),
                'users_with_2fa' => User::whereNotNull('two_factor_confirmed_at')->count(),
                'total_roles' => Role::count(),
                'total_permissions' => Permission::count(),
            ];
        });
    }

    protected function loadRolesDistribution(): void
    {
        $this->rolesDistribution = Cache::remember('dashboard:roles_distribution', 600, function () {
            return Role::withCount('users')
                ->get()
                ->map(function ($role) {
                    return [
                        'name' => $role->name,
                        'count' => $role->users_count,
                        'color' => $this->getRoleColor($role->name),
                    ];
                })
                ->toArray();
        });
    }

    protected function getRoleColor(string $roleName): string
    {
        return match ($roleName) {
            'Ghost' => 'purple',
            'admin' => 'blue',
            'moderator' => 'green',
            'user' => 'gray',
            default => 'slate',
        };
    }
};

?>

<x-layouts.content 
    :heading="__('Tableau de bord')" 
    :subheading="__('Vue d\'ensemble de votre application')"
    :pageHeading="__('Good Morning, ' . $full_name) . '👋'" 
    :pageSubheading="__('Ready to explore your dashboard?')">

    <div class="space-y-4">

        {{-- Statistiques Principales --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Total Utilisateurs --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text sm color="muted">Total Utilisateurs</flux:text>
                        <flux:heading size="xl" class="mt-2">
                            {{ number_format($stats['total_users']) }}
                        </flux:heading>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                        <flux:icon.users class="h-6 w-6 text-blue-600" />
                    </div>
                </div>
            </x-card>

            {{-- Emails Vérifiés --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text sm color="muted">Emails Vérifiés</flux:text>
                        <flux:heading size="xl" class="mt-2">
                            {{ number_format($stats['verified_users']) }}
                        </flux:heading>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <flux:icon.check-badge class="h-6 w-6 text-green-600" />
                    </div>
                </div>
            </x-card>

            {{-- Profils Complétés --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text sm color="muted">Profils Complétés</flux:text>
                        <flux:heading size="xl" class="mt-2">
                            {{ number_format($stats['completed_profiles']) }}
                        </flux:heading>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100">
                        <flux:icon.user-circle class="h-6 w-6 text-purple-600" />
                    </div>
                </div>
            </x-card>

            {{-- 2FA Activé --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text sm color="muted">2FA Activé</flux:text>
                        <flux:heading size="xl" class="mt-2">
                            {{ number_format($stats['users_with_2fa']) }}
                        </flux:heading>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100">
                        <flux:icon.shield-check class="h-6 w-6 text-orange-600" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Répartition des Rôles --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- Widget Rôles --}}
            <x-card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">Répartition des Rôles</flux:heading>
                        @can('view roles')
                            <flux:button size="sm" variant="ghost" :href="route('admin.roles.index')" wire:navigate>
                                Gérer
                            </flux:button>
                        @endcan
                    </div>

                    <div class="space-y-3">
                        @forelse ($rolesDistribution as $role)
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-full bg-{{ $role['color'] }}-100">
                                        <flux:icon.shield-check class="w-5 h-5 text-{{ $role['color'] }}-600" />
                                    </div>
                                    <flux:heading size="sm">{{ $role['name'] }}</flux:heading>
                                </div>

                                <flux:badge color="{{ $role['color'] }}" size="lg">
                                    {{ $role['count'] }}
                                </flux:badge>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <flux:text color="muted">Aucun rôle configuré</flux:text>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-card>

            {{-- Widget Statistiques Système --}}
            <x-card>
                <div class="space-y-4">
                    <flux:heading size="lg">Système</flux:heading>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <flux:icon.shield-check class="w-5 h-5 text-blue-600" />
                                <flux:text>Rôles configurés</flux:text>
                            </div>
                            <flux:badge color="blue">{{ $stats['total_roles'] }}</flux:badge>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <flux:icon.key class="w-5 h-5 text-green-600" />
                                <flux:text>Permissions actives</flux:text>
                            </div>
                            <flux:badge color="green">{{ $stats['total_permissions'] }}</flux:badge>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <flux:icon.user-group class="w-5 h-5 text-purple-600" />
                                <flux:text>Utilisateurs actifs</flux:text>
                            </div>
                            <flux:badge color="purple">{{ $stats['verified_users'] }}</flux:badge>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Actions Rapides --}}
        @can('create users')
            <x-card>
                <div class="space-y-4">
                    <flux:heading size="lg">Actions Rapides</flux:heading>

                    <div class="flex flex-wrap gap-3">
                        @can('view users')
                            <flux:button :href="route('admin.users.index')" wire:navigate variant="secondary">
                                <flux:icon.users class="w-5 h-5" />
                                Gérer les utilisateurs
                            </flux:button>
                        @endcan

                        @can('view roles')
                            <flux:button :href="route('admin.roles.index')" wire:navigate variant="secondary">
                                <flux:icon.shield-check class="w-5 h-5" />
                                Gérer les rôles
                            </flux:button>
                        @endcan

                        @can('view settings')
                            <flux:button :href="route('settings.profile')" wire:navigate variant="secondary">
                                <flux:icon.cog-6-tooth class="w-5 h-5" />
                                Paramètres
                            </flux:button>
                        @endcan
                    </div>
                </div>
            </x-card>
        @endcan
    </div>

</x-layouts.content>