<div class="flex items-start">

    <div class="flex-1 self-stretch space-y-4">
        <div class="hidden lg:block leading-none bg-white shadow px-4 py-1">
            <flux:heading size="lg" level="4">{{ $heading ?? '' }}</flux:heading>
            <flux:text sm color="muted">{{ $subheading ?? '' }}</flux:text>
        </div>

        <div class="p-4">
            <div class="">
                <flux:heading>{{ $pageHeading ?? '' }}</flux:heading>
                <flux:text sm color="muted">{{ $pageSubheading ?? '' }}</flux:text>
            </div>
            <div class="mt-4 w-full">
                {{-- Affichez ici le message d'avertissement de la session --}}
                <div class="absolute top-20 right-10">
                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif<!-- Messages flash -->
                    @if (session('success'))
                        {{-- <div class="mb-6 rounded-md bg-green-50 p-4">
                            <p class="text-sm text-success">{{ session('success') }}</p>
                        </div> --}}
                        <x-action-message class="me-3" on="profile-updated">
                            {{ __('Profil mis à jour !') }}
                        </x-action-message>
                    @endif
                    @if (session('error'))
                        <div class="mb-6 rounded-md bg-red-50 p-4">
                            <p class="text-sm text-danger">{{ session('error') }}</p>
                        </div>
                    @endif
                </div>


                {{ $slot }}
            </div>
        </div>
    </div>

</div>