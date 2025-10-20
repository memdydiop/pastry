@php $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
    'iconTrailing' => null,
    'iconVariant' => 'mini',
    'variant' => 'default',
    'suffix' => null,
    'value' => null,
    'icon' => null,
    'kbd' => null,
])

@php
    if ($kbd) $suffix = $kbd;

    $iconClasses = Flux::classes()
        ->add('me-2')
        // When using the outline icon variant, we need to size it down to match the default icon sizes...
        ->add($iconVariant === 'outline' ? 'size-5' : null)
        ;

    $trailingIconClasses = Flux::classes()
        ->add('ms-auto text-zinc-400 [[data-flux-menu-item-icon]:hover_&]:text-current')
        // When using the outline icon variant, we need to size it down to match the default icon sizes...
        ->add($iconVariant === 'outline' ? 'size-5' : null)
        ;

    $classes = Flux::classes()
        ->add('flex items-center px-2 py-1.5 w-full focus:outline-hidden')
        ->add('rounded-md')
        ->add('text-start text-sm font-medium')
        ->add('[&[disabled]]:opacity-50')
        ->add(match ($variant) {
        'primary' => [
            'text-primary-foreground/80 bg-primary',
            'data-active:text-primary-foreground data-active:bg-primary',
            '**:data-flux-menu-item-icon:text--primary-foreground/50 [&[data-active]_[data-flux-menu-item-icon]]:text-primary-foreground',
        ],
        'secondary' => [
            'text-secondary-foreground/80 bg-secondary',
            'data-active:text-secondary-foreground data-active:bg-secondary',
            '**:data-flux-menu-item-icon:text--secondary-foreground/50 [&[data-active]_[data-flux-menu-item-icon]]:text-secondary-foreground',
        ],
        'info' => [
            'text-info-foreground/80 bg-info',
            'data-active:text-info-foreground data-active:bg-info',
            '**:data-flux-menu-item-icon:text--info-foreground/50 [&[data-active]_[data-flux-menu-item-icon]]:text-info-foreground',
        ],
        'warning' => [
                'text-warning-foreground/80 bg-warning',
                'data-active:text-warning-foreground data-active:bg-warning',
                '**:data-flux-menu-item-icon:text-warning-foreground/50 [&[data-active]_[data-flux-menu-item-icon]]:text-warning-foreground',
            ], 
            'danger' => [
                'text-danger-foreground/80 bg-danger',
                'text-danger-foreground/50 data-active:text-danger-foreground data-active:bg-danger',
                '**:data-flux-menu-item-icon:text-danger-foreground/50 [&[data-active]_[data-flux-menu-item-icon]]:text-danger-foreground',
            ],
            'default' => [
                'text-zinc-800 data-active:bg-zinc-50',
                '**:data-flux-menu-item-icon:text-zinc-400 [&[data-active]_[data-flux-menu-item-icon]]:text-current',
            ]
        })
        ;

    $suffixClasses = Flux::classes()
        ->add('ms-auto text-xs text-zinc-400')
        ;
@endphp

<flux:button-or-link :attributes="$attributes->class($classes)" data-flux-menu-item :data-flux-menu-item-has-icon="!! $icon">
    <?php if (is_string($icon) && $icon !== ''): ?>
        <flux:icon :$icon :variant="$iconVariant" :class="$iconClasses" data-flux-menu-item-icon />
    <?php elseif ($icon): ?>
        {{ $icon }}
    <?php else: ?>
        <div class="w-7 hidden [[data-flux-menu]:has(>[data-flux-menu-item-has-icon])_&]:block"></div>
    <?php endif; ?>

    {{ $slot }}

    <?php if ($suffix): ?>
        <?php if (is_string($suffix)): ?>
            <div class="{{ $suffixClasses }}">
                {{ $suffix }}
            </div>
        <?php else: ?>
            {{ $suffix }}
        <?php endif; ?>
    <?php endif; ?>

    <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
        <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$trailingIconClasses" data-flux-menu-item-icon />
    <?php elseif ($iconTrailing): ?>
        {{ $iconTrailing }}
    <?php endif; ?>

    {{ $submenu ?? '' }}
</flux:button-or-link>
