@pure

@props([
    'badge' => null,
    'aside' => null,
])

@php
    $classes = Flux::classes()
        ->add('inline-flex items-center')
        ->add('text-sm font-medium')
        ->add('[:where(&)]:text-zinc-800')
        ;
@endphp

<ui-label {{ $attributes->class($classes) }} data-flux-label>
    {{ $slot }}

    <?php if (is_string($badge)): ?>
        <span class="ms-1.5 text-zinc-800/70 text-xs bg-zinc-800/5 px-1.5 py-1 -my-1 rounded-[4px]" aria-hidden="true">
            {{ $badge }}
        </span>
    <?php elseif ($badge): ?>
        <span class="ms-1.5" aria-hidden="true">
            {{ $badge }}
        </span>
    <?php endif; ?>

    <?php if ($aside): ?>
        <span class="ms-1.5 text-zinc-800/70 text-xs bg-zinc-800/5 px-1.5 py-1 -my-1 rounded-[4px]" aria-hidden="true">
            {{ $aside }}
        </span>
    <?php endif; ?>
</ui-label>
