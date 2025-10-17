@pure

@props([
    'expandable' => false,
    'expanded' => false,
    'heading' => null,
    'icon' => null,
    'iconVariant' => 'outline',
    'iconDot' => null,
    'variant' => null,
    'accent' => true,
])
    
@php
    // Button should be a square if it has no text contents...
    $square ??= $slot->isEmpty();

    // Size-up icons in square/icon-only buttons...
    $iconClasses = Flux::classes($square ? 'size-6!' : 'size-5!');

    $disclosureClasses = Flux::classes()
        ->add('group/disclosure rounded')
        ->add('data-open:bg-sidebar-item-active-bg/50')
        ->add('data-current:bg-sidebar-item-active-bg data-current:text-sidebar-item-active-color')
        ->add('text-sidebar-item-color/50')
        ;

    $classes = Flux::classes()
        ->add('h-10 cursor-pointer flex items-center gap-3 rounded ')
        ->add($square ? 'px-2.5!' : '')
        ->add('py-0 px-3 my-0 text-start w-full')
        ->add('data-open:rounded-b-none data-open:bg-sidebar-item-active-bg/50')
        ->add('data-open:border-b border-slate-700/30')
        ->add('data-current:text-sidebar-item-active-color')
        ->add('data-current:bg-sidebar-item-active-bg data-current:border-b')
        ->add('hover:text-sidebar-item-hover-color hover:bg-sidebar-item-active-bg/50')
        ->add('text-sidebar-item-color/35')
    ;
@endphp

<?php if ($expandable && $heading): ?>
    <ui-disclosure {{ $attributes->class($disclosureClasses) }} @if ($expanded === true) open @endif data-flux-navlist-group>
        <button type="button" {{ $attributes->class($classes) }}>
            <?php    if ($icon): ?>
            <div class="relative">
                <?php        if (is_string($icon) && $icon !== ''): ?>
                    <flux:icon :$icon :variant="$iconVariant" class="{!! $iconClasses !!}" />
                <?php        else: ?>
                    {{ $icon }}
                <?php        endif; ?>

                <?php        if ($iconDot): ?>
                    <div class="absolute top-[-2px] end-[-2px]">
                        <div class="size-[6px] rounded-full bg-zinc-500"></div>
                    </div>
                <?php        endif; ?>
            </div>
            <?php    endif; ?>
            <div class="flex-1 text-14 font-medium leading-none whitespace-nowrap [[data-nav-footer]_&]:hidden [[data-nav-sidebar]_[data-nav-footer]_&]:block" data-content">{{ $heading }}</div>
            <div class="ps-0 pe-0">
                <flux:icon.chevron-down class="size-3! hidden group-data-open/disclosure-button:block" />
                <flux:icon.chevron-right class="size-3! block group-data-open/disclosure-button:hidden rtl:rotate-180" />
            </div>

        </button>

        <div class="group-data-current/disclosure:block relative hidden data-open:block space-y-[2px] ps-7" @if ($expanded === true) data-open @endif>
            <div class="absolute hidden inset-y-[3px] w-px bg-slate-700 start-0 ms-5"></div>

            {{ $slot }}
        </div>
    </ui-disclosure>
    <?php elseif ($heading): ?>
        <div {{ $attributes->class('block space-y-[2px]') }}>
            <div class="px-3 py-2">
                <div class="text-sm text-zinc-400 font-medium leading-none">{{ $heading }}</div>
            </div>

            <div>
                {{ $slot }}
            </div>
        </div>
    <?php else: ?>
    <div {{ $attributes->class('block space-y-[2px]') }}>
        {{ $slot }}
    </div>
<?php endif; ?>
