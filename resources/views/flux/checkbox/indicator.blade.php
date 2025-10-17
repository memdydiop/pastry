@pure

@php
$classes = Flux::classes()
    ->add('shrink-0 size-[1.125rem] rounded-[.3rem] flex justify-center items-center')
    ->add('text-zinc-700')
    ->add('shadow-xs [ui-checkbox[disabled]_&]:opacity-75 [ui-checkbox[data-checked][disabled]_&]:opacity-50 [ui-checkbox[disabled]_&]:shadow-none [ui-checkbox[data-checked]_&]:shadow-none [ui-checkbox[data-indeterminate]_&]:shadow-none')
    ->add('[ui-checkbox[data-checked]:not([data-indeterminate])_&>svg:first-child]:block [ui-checkbox[data-indeterminate]_&>svg:last-child]:block')
    ->add([
        'border',
        '[ui-checkbox[data-checked]_&]:border-transparent [ui-checkbox[data-indeterminate]_&]:border-transparent',
        '[ui-checkbox[disabled][data-checked]_&]:border-transparent [ui-checkbox[disabled][data-indeterminate]_&]:border-transparent',
        '[print-color-adjust:exact]',
    ])
    ->add([
        'bg-white',
        '[ui-checkbox[data-checked]_&]:bg-[var(--color-accent)]',
        'hover:[ui-checkbox[data-checked]_&]:bg-(--color-accent)',
        'focus:[ui-checkbox[data-checked]_&]:bg-(--color-accent)',
        '[ui-checkbox[data-indeterminate]_&]:bg-[var(--color-accent)]',
        'hover:[ui-checkbox[data-indeterminate]_&]:bg-(--color-accent)',
        'focus:[ui-checkbox[data-indeterminate]_&]:bg-(--color-accent)',
    ])
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-checkbox-indicator>
    <flux:icon.check variant="micro" class="hidden text-[var(--color-accent-foreground)]" />
    <flux:icon.minus variant="micro" class="hidden text-[var(--color-accent-foreground)]" />
</div>
