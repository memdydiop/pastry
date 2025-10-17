<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public string $full_name = '';
    public string $email = '';

    public function mount()
    {

        $this->full_name = Auth::user()->userProfile->full_name;
        $this->email = Auth::user()->email;
    }
};
?>

<x-layouts.content
    :heading="__('Dashboard')"
    :subheading="__('Welcome back, ' . ($full_name))"
    :pageHeading="__('Welcome back, ' . ($full_name)) .'👋'" 
    :pageSubheading="__('Ready to explore your dashboard?')">

    <!-- Welcome Section -->
    {{-- <div class="mb-8 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">{{ __('Welcome back') }}, {{ $full_name ?? explode('@', $this->user->email)[0] }}! 👋</h2>
                <p class="mt-2 text-blue-100">{{ __('Ready to explore your dashboard?') }}</p>
            </div>
            <div class="hidden h-16 w-16 items-center justify-center rounded-full bg-white/20 text-2xl font-bold text-white sm:flex">
                {{ auth()->user()->userProfile->initials() }}
            </div>
        </div>
    </div> --}}

    <!-- Stats Cards -->
    <div class="mb-8 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- Profile Completion Card -->
        <div class="rounded bg-white p-6 shadow">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('Profile Status') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ auth()->user()->userProfile ? __('Complete') : __('Incomplete') }}</p>
                </div>
            </div>
        </div>

        <!-- Account Created Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 9l6-6m0 0v6m0-6h-6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('Member Since') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ auth()->user()->userProfile->created_at->format('M Y') }}</p>
                </div>
            </div>
        </div>

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
                    @if(auth()->user()->userProfile)
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
                            <p class="text-xs text-gray-400">{{ auth()->user()->userProfile->email_verified_at?->format('M j, Y \a\t g:i A') ?? __('Pending') }}</p>
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
                    <a href="{{ route('profile.edit') }}" class="flex items-center rounded-lg p-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('Edit Profile') }}
                    </a>
                    <a href="{{ route('password.edit') }}" class="flex items-center rounded-lg p-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        {{ __('Change Password') }}
                    </a>
                    <a href="{{ route('appearance.edit') }}" class="flex items-center rounded-lg p-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
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
                        <span class="font-medium">{{ auth()->user()->userProfile->full_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Email') }}:</span>
                        <span class="font-medium">{{ auth()->user()->userProfile->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Member Since') }}:</span>
                        <span class="font-medium">{{ auth()->user()->created_at->format('M j, Y') }}</span>
                    </div>
                    @if(auth()->user()->userProfile?->phone)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Phone') }}:</span>
                            <span class="font-medium">{{ auth()->user()->userProfile->phone }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-layouts.content>