@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'variant' => 'default', // default, primary, success, warning, danger, info
    'hoverable' => false,
    'padding' => 'normal', // none, small, normal, large
    'shadow' => 'none', // none, sm, md, lg, xl
])

@php
    $variantClasses = [
        'default' => 'bg-white border-gray-200',
        'primary' => 'bg-blue-50 border-blue-200',
        'success' => 'bg-green-50 border-green-200',
        'warning' => 'bg-yellow-50 border-yellow-200',
        'danger' => 'bg-red-50 border-red-200',
        'info' => 'bg-cyan-50 border-cyan-200',
    ];
    
    $headerVariantClasses = [
        'default' => 'bg-gray-50 border-gray-200',
        'primary' => 'bg-blue-100 border-blue-200',
        'success' => 'bg-green-100 border-green-200',
        'warning' => 'bg-yellow-100 border-yellow-200',
        'danger' => 'bg-red-100 border-red-200',
        'info' => 'bg-cyan-100 border-cyan-200',
    ];
    
    $paddingClasses = [
        'none' => '',
        'small' => 'px-3 py-3',
        'normal' => 'px-4 py-4',
        'large' => 'px-6 py-6',
    ];
    
    $shadowClasses = [
        'none' => '',
        'sm' => 'shadow-sm',
        'md' => 'shadow-md',
        'lg' => 'shadow-lg',
        'xl' => 'shadow-xl',
    ];
    
    $baseClasses = "rounded border overflow-hidden transition-all duration-300";
    $variantClass = $variantClasses[$variant] ?? $variantClasses['default'];
    $shadowClass = $shadowClasses[$shadow] ?? $shadowClasses['none'];
    $hoverClass = $hoverable ? 'hover:shadow-xl hover:-translate-y-1 cursor-pointer' : '';
@endphp

<div {{ $attributes->merge(['class' => "$baseClasses $variantClass $shadowClass $hoverClass"]) }}>
    {{-- En-tête de la carte --}}
    @if($title || $subtitle || isset($header))
    <div class="border-b {{ $headerVariantClasses[$variant] ?? $headerVariantClasses['default'] }} {{ $paddingClasses[$padding] }}">
        @if(isset($header))
            {{ $header }}
        @else
            @if($title)
            <flux:heading level="3"  >{{ $title }}</flux:heading>
            @endif
            
            @if($subtitle)
            <p class="mt-1 text-sm text-gray-600">
                {{ $subtitle }}
            </p>
            @endif
        @endif
    </div>
    @endif

    {{-- Contenu principal --}}
    <div class="{{ $paddingClasses[$padding] }}">
        {{ $slot }}
    </div>

    {{-- Pied de page --}}
    @if($footer || isset($actions) || isset($footerSlot))
    <div class="border-t border-gray-200 bg-gray-50 {{ $paddingClasses[$padding] }}">
        @if(isset($footerSlot))
            {{ $footerSlot }}
        @else
            @if($footer)
            <div class="text-sm text-gray-600">
                {{ $footer }}
            </div>
            @endif
            
            @if(isset($actions))
            <div class="mt-3 flex flex-wrap gap-2">
                {{ $actions }}
            </div>
            @endif
        @endif
    </div>
    @endif
</div>