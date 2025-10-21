@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'resize' => 'vertical',
    'invalid' => null,
    'rows' => 4,
])

@php
$invalid ??= ($name && $errors->has($name));

$classes = Flux::classes()
    ->add('block p-3 w-full')
    ->add('shadow-xs disabled:shadow-none border rounded')
    ->add('bg-white')
    ->add($resize ? 'resize-y' : 'resize-none')
    ->add('text-base sm:text-sm')
    ->add('text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70')
    ->add($invalid ? 'border-red-500' : 'border-zinc-200 border-b-zinc-300/80')
    ;

$resizeStyle = match ($resize) {
    'none' => 'resize: none',
    'both' => 'resize: both',
    'horizontal' => 'resize: horizontal',
    'vertical' => 'resize: vertical',
};
@endphp

<flux:with-field :$attributes>
    <textarea
        {{ $attributes->class($classes) }}
        rows="{{ $rows }}"
        style="{{ $resizeStyle }}; {{ $rows === 'auto' ? 'field-sizing: content' : '' }}"
        @isset ($name) name="{{ $name }}" @endisset
        @if ($invalid) aria-invalid="true" data-invalid @endif
        data-flux-control
        data-flux-textarea
    >{{ $slot }}</textarea>
</flux:with-field>
