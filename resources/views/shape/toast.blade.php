@blaze(fold: true, safe: ['heading', 'description'])

{{--
    One toast. Rendered here so that its markup is Blade like everything else —
    and then rendered *into a `<template>`* by the toaster, one per tone, for
    shape.js to clone.

    That indirection is the point. The obvious alternative is a string of HTML
    inside the script, which puts half the design system in a file Tailwind
    doesn't scan and a designer doesn't open. Cloning a template keeps the markup
    here, keeps it folding, and leaves the script with nothing to do but fill in
    text.

    Which is why nothing below is wrapped in `@if`. Every element has to exist in
    the template whether or not this render has a value for it, because the
    script fills them afterwards. `empty:hidden` collapses the ones that stay
    empty — a CSS question rather than a Blade one.

    Prop-first and slotless, like the badge, for the same reason: there is
    nothing to compose inside a toast, and a slot would be markup the script
    cannot fill.

    Colour is carried by the bar and the glyph, never by the text. The surface
    stays white so a toast is readable over whatever it is floating above,
    including a modal's dimmed backdrop.
--}}

@props([
    'color' => null,
    'heading' => null,
    'description' => null,
    'icon' => null,
    'iconVariant' => 'mini',
    'dismissible' => true,
])

@php
$glyph = $icon ?? match ($color) {
    'success' => 'check-circle',
    'danger' => 'x-circle',
    'warning' => 'exclamation-triangle',
    'accent' => 'information-circle',
    default => null,
};

$classes = Shape::classes()
    ->add('pointer-events-auto flex w-full items-start')
    ->add('[:where(&)]:gap-3 [:where(&)]:rounded-shape [:where(&)]:p-4 [:where(&)]:shadow-lg')
    ->add('[:where(&)]:border [:where(&)]:border-shape-200 dark:[:where(&)]:border-shape-800')
    ->add('[:where(&)]:border-l-4 [:where(&)]:border-l-[var(--shape-tone)]')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]');
@endphp

<div
    {{ $attributes->class($classes) }}
    data-shape-toast
    data-shape-tone="{{ $color ?? 'neutral' }}"
>
    <span class="mt-0.5 shrink-0 text-[color:var(--shape-tone-ink)] empty:hidden" data-shape-toast-icon>
        @if ($glyph)
            <x-shape::icon :name="$glyph" :variant="$iconVariant" />
        @endif
    </span>

    <div class="flex min-w-0 flex-1 flex-col gap-1">
        <x-shape::heading :level="3" size="sm" class="empty:hidden" data-shape-toast-heading="">{{ $heading }}</x-shape::heading>

        <x-shape::text size="sm" variant="muted" class="empty:hidden" data-shape-toast-description="">{{ $description }}</x-shape::text>
    </div>

    @if ($dismissible)
        <x-shape::button
            square
            size="sm"
            variant="ghost"
            icon="x-mark"
            icon-variant="micro"
            aria-label="Dismiss"
            class="-mr-1.5 -mt-1.5 shrink-0"
            data-shape-dismiss=""
        />
    @endif
</div>
