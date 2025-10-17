<div class="flex items-start">
    
    <div class="flex-1 self-stretch space-y-4">
        <div class="hidden lg:block leading-none bg-white shadow px-4 py-1">
            <flux:heading size="lg" level="4">{{ $heading ?? '' }}</flux:heading>
            <flux:text sm color="muted">{{ $subheading ?? '' }}</flux:text>
        </div>

        <div class="px-4">
            <div class="">
                <flux:heading>{{ $pageHeading ?? '' }}</flux:heading>
                <flux:text sm color="muted">{{ $pageSubheading ?? '' }}</flux:text>
            </div>
            <div class="mt-4 w-full">
                {{-- Affichez ici le message d'avertissement de la session --}}
                @if (session('warning'))
                    <div class="alert alert-warning">
                        {{ session('warning') }}
                    </div>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>

</div>
