<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen font-poppins bg-body text-13">
    <flux:sidebar sticky stashable class="border-e border-sidebar-border bg-sidebar-bg">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navlist.item>

            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />
        <flux:navlist variant="outline">
            @can('view administration')
            <flux:navlist.group :heading="__('Administration')">

                <flux:navlist.group icon="user-group" heading="Gestion des Utilisateurs" expandable>
                    @can('view users')
                        <flux:navlist.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.index')"
                            wire:navigate>
                            {{ __('Utilisateurs') }}
                        </flux:navlist.item>
                    @endcan
                    @can('create users')
                        <flux:navlist.item icon="envelope" :href="route('admin.invitations.index')"
                            :current="request()->routeIs('admin.invitations.index')" wire:navigate>
                            {{ __('Invitations') }}
                        </flux:navlist.item>
                    @endcan
                </flux:navlist.group>

                @can('view roles')
                    <flux:navlist.item icon="shield-check" :href="route('admin.roles.index')"
                        :current="request()->routeIs('admin.roles.index')" wire:navigate>
                        {{ __('Rôles & Permissions') }}
                    </flux:navlist.item>
                @endcan

                </flux:sidebar.group>
        @endcan
        </flux:navlist>
        
        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :avatar="auth()->user()->avatar"
                :initials="auth()->user()->initials()" icon-trailing="chevrons-up-down"
                data-test="sidebar-menu-button" />
            <x-user-dropdown-menu />
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile class="bg-sidebar-bg! hover:bg-sidebar-bg! focus:bg-sidebar-bg!"
                :avatar="auth()->user()->avatar" :initials="auth()->user()->initials()" :name="auth()->user()->name"
                icon-trailing="chevron-down" />

            <x-user-dropdown-menu />
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>