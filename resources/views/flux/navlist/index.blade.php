@pure

@props([
    'variant' => null,
])

@php
$classes = Flux::classes()
    ->add('flex flex-col')
    ->add('overflow-visible gap-y-1 min-h-auto')
    ;
@endphp

<nav {{ $attributes->class($classes) }} data-flux-navlist>
    {{ $slot }}
</nav>
