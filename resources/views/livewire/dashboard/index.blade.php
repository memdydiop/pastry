<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public string $full_name = '';
    public string $email = '';

    public function mount()
    {

        $this->full_name = Auth::user()->profile->full_name;
        $this->email = Auth::user()->email;
    }
};
?>

<x-layouts.content
    :heading="__('Dashboard')"
    :subheading="__('Welcome back, ' . $full_name)"
    :pageHeading="__('Good Morning, ' . $full_name) . '👋'" 
    :pageSubheading="__('Ready to explore your dashboard?')">

    <!-- Welcome Section -->
    {{-- <div class="mb-8 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">{{ __('Welcome back') }}, {{ $full_name ?? explode('@', $this->user->email)[0] }}! 👋</h2>
                <p class="mt-2 text-blue-100">{{ __('Ready to explore your dashboard?') }}</p>
            </div>
            <div class="hidden h-16 w-16 items-center justify-center rounded-full bg-white/20 text-2xl font-bold text-white sm:flex">
                {{ auth()->user()->profile->initials() }}
            </div>
        </div>
    </div> --}}

    <!-- Stats Cards -->
    <div class="mb-8 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- Profile Completion Card -->
        <x-card>
            <div class="flex items-center justify-between">
                <div class=" flex-grow-1 overflow-hidden">
                    <p class=" uppercase font-medium text-muted truncate mb-0"> Total Earnings</p>
                </div>
                <div class=" shrink-0">
                    <h5 class="text-success mb-0 flex items-center gap-x-1 ">
                        <flux:icon class="size-3!" name="arrow-up-right"/> +16.24 %
                    </h5>
                </div>
            </div>

            <div class="flex items-end mt-4">
                <div class="flex flex-col grow">
                    <h4 class="text-22 font-semibold text-secondary mb-4">$<span class="counter-value" data-target="559.25">559.25</span>k
                    </h4>
                    <a href="" class="text-decoration-underline">View net earnings</a>
                </div>
                <span class="bg-success/50 rounded font-13 size-12 flex items-center justify-center flex-shrink-0">
                    <flux:icon name="currency-dollar" class="text-success"/>
                </span>
            </div>
        </x-card>

        <!-- Account Created Card -->
        <x-card>
            <div class="flex items-center justify-between">
                <div class=" flex-grow-1 overflow-hidden">
                    <p class=" uppercase font-medium text-muted truncate mb-0"> Orders</p>
                </div>
                <div class=" shrink-0">
                    <h5 class="text-danger mb-0 flex items-center gap-x-1">
                        <flux:icon class="size-3!" name="arrow-down-right" /> -3,57 %
                    </h5>
                </div>
            </div>
        
            <div class="flex items-end mt-4">
                <div class="flex flex-col grow">
                    <h4 class="text-22 font-semibold text-secondary mb-4">$<span class="counter-value"
                            data-target="36,894">36,894</span>k
                    </h4>
                    <a href="" class="text-decoration-underline">View net earnings</a>
                </div>
                <span class="bg-success/50 rounded font-13 size-12 flex items-center justify-center flex-shrink-0">
                    <flux:icon name="shopping-bag" class="text-success" />
                </span>
            </div>
        </x-card>

        <!-- Profile Views Card (Placeholder) -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('Profile Views') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ __('Soon') }}</p>
                </div>
            </div>
        </div>

        <!-- Activity Card (Placeholder) -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('Last Activity') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ __('Today') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left Column - Recent Activity -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Recent Activity') }}</h3>
                <div class="space-y-4">
                    @if(auth()->user()->profile)
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">{{ __('Profile completed successfully') }}</p>
                            <p class="text-xs text-gray-400">{{ now()->format('M j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                            <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">{{ __('Account verification completed') }}</p>
                            <p class="text-xs text-gray-400">{{ auth()->user()->email_verified_at?->format('M j, Y \a\t g:i A') ?? __('Pending') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Quick Actions & Profile Summary -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Quick Actions') }}</h3>
                <div class="space-y-3">
                    <a href="{{ route('settings.profile') }}" class="flex items-center rounded-lg p-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('Edit Profile') }}
                    </a>
                    <a href="{{ route('settings.password') }}" class="flex items-center rounded-lg p-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        {{ __('Change Password') }}
                    </a>
                    <a href="{{ route('settings.appearance') }}" class="flex items-center rounded-lg p-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a4 4 0 004-4V5z"/>
                        </svg>
                        {{ __('Appearance') }}
                    </a>
                </div>
            </div>

            <!-- Profile Summary -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Profile Summary') }}</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Full Name') }}:</span>
                        <span class="font-medium">{{ auth()->user()->profile?->full_name ?? __('Not set') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Email') }}:</span>
                        <span class="font-medium">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Member Since') }}:</span>
                        <span class="font-medium">{{ auth()->user()->created_at->format('M j, Y') }}</span>
                    </div>
                    @if(auth()->user()->profile?->phone)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Phone') }}:</span>
                            <span class="font-medium">{{ auth()->user()->profile->phone }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Exemple 1: Card simple --}}
        <x-card title="Titre Simple">
            <p>Contenu de base de la carte.</p>
        </x-card>
        
        {{-- Exemple 2: Card avec sous-titre et footer --}}
        <x-card title="Mon Projet" subtitle="Description courte du projet" footer="Créé le 18 octobre 2025">
            <p class="text-gray-700">
                Voici le contenu principal de ma carte avec tous les détails importants.
            </p>
        </x-card>
        
        {{-- Exemple 3: Card avec variants (couleurs) --}}
        <div class="space-y-4">
            <x-card title="Information" variant="info">
                <p>Message d'information</p>
            </x-card>
        
            <x-card title="Succès" variant="success">
                <p>Opération réussie!</p>
            </x-card>
        
            <x-card title="Attention" variant="warning">
                <p>Veuillez vérifier ces informations</p>
            </x-card>
        
            <x-card title="Erreur" variant="danger">
                <p>Une erreur s'est produite</p>
            </x-card>
        </div>
        
        {{-- Exemple 4: Card hoverable (avec effet au survol) --}}
        <x-card title="Carte Interactive" :hoverable="true" shadow="lg">
            <p>Survolez cette carte pour voir l'effet!</p>
        </x-card>
        
        {{-- Exemple 5: Card avec actions personnalisées --}}
        <x-card title="Profil Utilisateur">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                    <span class="text-2xl">👤</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Jean Dupont</h4>
                    <p class="text-sm text-gray-600">jean.dupont@example.com</p>
                    <p class="text-xs text-gray-500">Membre depuis 2023</p>
                </div>
            </div>
        
            <x-slot:actions>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Modifier
                </button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Voir détails
                </button>
            </x-slot:actions>
        </x-card>
        
        {{-- Exemple 6: Card avec header personnalisé --}}
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">En-tête Personnalisé</h3>
                        <p class="text-sm text-gray-600">Avec des éléments custom</p>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                        Actif
                    </span>
                </div>
            </x-slot:header>
        
            <p>Contenu de la carte avec un header totalement personnalisé.</p>
        </x-card>
        
        {{-- Exemple 7: Card avec footer personnalisé --}}
        <x-card title="Statistiques">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-3xl font-bold text-blue-600">150</p>
                    <p class="text-sm text-gray-600">Vues</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-green-600">45</p>
                    <p class="text-sm text-gray-600">Likes</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-purple-600">12</p>
                    <p class="text-sm text-gray-600">Partages</p>
                </div>
            </div>
        
            <x-slot:footerSlot>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Dernière mise à jour: il y a 2h</span>
                    <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Rafraîchir
                    </button>
                </div>
            </x-slot:footerSlot>
        </x-card>
        
        {{-- Exemple 8: Card sans padding --}}
        <x-card title="Image en pleine largeur" padding="none">
            <img src="https://via.placeholder.com/800x400" alt="Image" class="w-full">
            <div class="p-6">
                <p>Texte sous l'image avec padding personnalisé</p>
            </div>
        </x-card>
        
        {{-- Exemple 9: Card avec différentes tailles de padding --}}
        <div class="space-y-4">
            <x-card title="Petit padding" padding="small">
                <p>Contenu avec espacement réduit</p>
            </x-card>
        
            <x-card title="Padding normal" padding="normal">
                <p>Contenu avec espacement standard</p>
            </x-card>
        
            <x-card title="Grand padding" padding="large">
                <p>Contenu avec espacement généreux</p>
            </x-card>
        </div>
        
        {{-- Exemple 10: Grille de cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(['Laravel', 'Vue.js', 'Tailwind CSS'] as $tech)
                <x-card :title="$tech" variant="primary" :hoverable="true" shadow="md">
                    <p class="text-gray-700 mb-4">
                        Framework/bibliothèque moderne pour le développement web.
                    </p>
                    <div class="flex gap-2">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Web</span>
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Dev</span>
                    </div>

                    <x-slot:actions>
                        <button class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm">
                            En savoir plus
                        </button>
                    </x-slot:actions>
                </x-card>
            @endforeach
        </div>
        
        {{-- Exemple 11: Card cliquable avec lien --}}
        <a href="/details" class="block">
            <x-card title="Article de blog" subtitle="Publié le 18 octobre 2025" :hoverable="true">
                <p class="text-gray-700 line-clamp-3">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                </p>
            </x-card>
        </a>
        
        {{-- Exemple 12: Card avec classes personnalisées --}}
        <x-card title="Carte Personnalisée" class="border-2 border-dashed border-purple-400 bg-purple-50">
            <p>Cette carte a des styles personnalisés supplémentaires.</p>
        </x-card>
    </div>

</x-layouts.content>