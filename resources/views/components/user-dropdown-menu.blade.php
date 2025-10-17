{{--
Composant réutilisable pour le menu utilisateur
Usage: <x-user-dropdown-menu />
--}}

<flux:menu {{ $attributes->merge(['class' => 'w-[220px]']) }}>
    <flux:menu.radio.group>
        <div class="p-0 text-sm font-normal">
            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                            class="h-full w-full object-cover">
                    @else
                        <span class="flex h-full w-full items-center justify-center rounded bg-neutral-200 text-black">
                            {{ auth()->user()->initials() }}
                        </span>
                    @endif
                </span>

                <div class="grid flex-1 text-start text-sm leading-tight">
                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                    <span class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</span>
                </div>
            </div>
        </div>
    </flux:menu.radio.group>

    <flux:menu.separator />

    <flux:menu.radio.group>
        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
            {{ __('Paramètres') }}
        </flux:menu.item>
    </flux:menu.radio.group>

    <flux:menu.separator />

    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
            data-test="logout-button">
            {{ __('Se déconnecter') }}
        </flux:menu.item>
    </form>
</flux:menu>