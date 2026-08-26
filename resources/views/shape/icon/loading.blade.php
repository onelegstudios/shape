@blaze(fold: true, memo: true)

@props([
    'variant' => 'outline',
])

@php
$classes = Shape::classes('shrink-0 animate-spin')
    ->add(match ($variant) {
        'micro' => '[:where(&)]:size-4',
        'mini' => '[:where(&)]:size-5',
        default => '[:where(&)]:size-6',
    });
@endphp

<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-20"/>
    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
</svg>
