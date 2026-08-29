@blaze(fold: true, memo: true)

{{--
    The one icon in this set that is not from Heroicons, and the one that is not
    generated: it is a single drawing with a spin on it, so it declares `variant`
    only so that a style named by a shared call site is ignored rather than
    rendered onto the `<svg>`.
--}}

@props([
    'variant' => 'outline',
    'size' => 'base',
])

@php
$classes = Shape::classes('shrink-0 animate-spin')
    ->add(match ($size) {
        'xs' => '[:where(&)]:size-4',
        'sm' => '[:where(&)]:size-5',
        default => '[:where(&)]:size-6',
    });
@endphp

<svg {{ $attributes->merge(['aria-hidden' => 'true'])->class($classes) }} data-shape-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-20"/>
    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
</svg>
