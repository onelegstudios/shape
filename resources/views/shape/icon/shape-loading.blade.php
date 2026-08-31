@blaze(fold: true, memo: true)

{{--
    The packaged slot, and the one icon here that is not generated. Every other
    slot is filled by whichever set is configured; this one Shape draws itself,
    because Heroicons' nearest drawing is `arrow-path` — a circular arrow, not a
    loader, and it does not read as one spinning. A circle and a 90° arc is
    geometric enough to sit beside any set.

    A set that has something better says so (`'shape-loading' => 'loader-circle'`)
    and the generated file shadows this one, since `components_path` resolves
    first. A set that says nothing, or `null`, leaves this rendering — which for
    a packaged slot is the designed answer rather than a hole.

    A single drawing, so it declares `variant` only so that a style named by a
    shared call site is ignored rather than rendered onto the `<svg>`.
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
