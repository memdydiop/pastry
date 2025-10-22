@props([
    'heading' => '',
    'subheading' => '',
    'pageHeading' => '',
    'pageSubheading' => '', 
    'actions' => '',
])


<div class="flex items-start relative">

    <div class="flex-1 self-stretch">
        <div class="hidden lg:block leading-none bg-white shadow px-4 py-1">
            <flux:heading size="lg" level="4">{{ $heading ?? '' }}</flux:heading>
            <flux:text sm color="muted">{{ $subheading ?? '' }}</flux:text>
        </div>

        <div class="p-4 space-y-4">
            @if ($pageHeading || $pageSubheading || $actions)
                <div class="flex items-center justify-between">
                    <div class="">
                        <flux:heading size="lg" class="mb-1! leading-[1.2]">{{ $pageHeading ?? '' }}</flux:heading>
                    <flux:text sm color="muted">{{ $pageSubheading ?? '' }}</flux:text>
                </div>
                <div class="flex items-center space-x-2">
                    {{ $actions ?? '' }}
                </div>
            </div>
            @endif

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Profil mis à jour !') }}
                </x-action-message>
            {{ $slot }}
        </div>
    </div>

</div>