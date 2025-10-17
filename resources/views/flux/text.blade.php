@pure

@props([
    'inline' => false,
    'variant' => null,
    'color' => null,
    'size' => null,
])

@php
$classes = Flux::classes()
    ->add(match ($size) {
        'xl' => 'text-lg',
        'lg' => 'text-base',
        default => '[:where(&)]:text-sm',
        'sm' => 'text-xs',
    })
    ->add($color ? match($color) {
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'info' => 'text-info',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-danger',
        'muted' => 'text-muted',
        'light' => 'text-light',
        'dark' => 'text-dark',
    } : match ($variant) {
        'strong' => '[:where(&)]:text-zinc-800',
        'subtle' => '[:where(&)]:text-zinc-400',
        default => '[:where(&)]:text-zinc-500',
    })
    ;
@endphp
{{-- NOTE: It's important that this file has NO newline at the end of the file. --}}
<?php if ($inline) : ?><span {{ $attributes->class($classes) }} data-flux-text @if ($color) color="{{ $color }}" @endif>{{ $slot }}</span><?php else: ?><p {{ $attributes->class($classes) }} data-flux-text @if ($color) data-color="{{ $color }}" @endif>{{ $slot }}</p><?php endif; ?>