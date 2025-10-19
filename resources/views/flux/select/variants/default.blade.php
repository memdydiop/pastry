@pure

@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
    'size' => null,
])

@php
$invalid ??= ($name && $errors->has($name));

$classes = Flux::classes()
    ->add('appearance-none') // Strip the browser's default <select> styles...
    ->add('w-full ps-3 pe-10 block')
    ->add(match ($size) {
        default => 'h-8 py-1 text-base sm:text-sm leading-[1.375rem] rounded',
        'sm' => 'h-8 py-1.5 text-sm leading-[1.125rem] rounded',
        'xs' => 'h-6 text-xs leading-[1.125rem] rounded',
    })
    ->add('shadow-xs border')
    ->add('bg-white')
    ->add('text-slate-600 disabled:text-muted placeholder:text-muted disabled:placeholder-muted/70')
    // Make the placeholder match the text color of standard input placeholders...
    ->add('disabled:shadow-none')
    ->add($invalid
        ? 'border border-red-500'
        : 'border border-slate-200 border-b-slate-300/80'
    )
@endphp

<select
    {{ $attributes->class($classes) }}
    @if ($invalid) aria-invalid="true" data-invalid @endif
    @isset ($name) name="{{ $name }}" @endisset
    @if (is_numeric($size)) size="{{ $size }}" @endif
    data-flux-control
    data-flux-select-native
    data-flux-group-target
>
    <?php if ($placeholder): ?>
        <option value="" disabled selected class="placeholder">{{ $placeholder }}</option>
    <?php endif; ?>

    {{ $slot }}
</select>
