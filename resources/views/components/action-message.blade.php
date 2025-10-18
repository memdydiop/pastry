@props([
    'on',
])

<div
    x-data="{ shown: false, timeout: null }"
    x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); })"
    x-show.transition.out.opacity.duration.1500ms="shown"
    x-transition:leave.opacity.duration.1500ms
    style="display: none"
    {{ $attributes->merge(['class' => 'mb-6 rounded-md bg-success text-success-foreground p-4 absolute top-4 right-0']) }}
>
    <Flux:text sm color="success"> {{ $slot->isEmpty() ? __('Saved.') : $slot }}</Flux:text>
</div>
