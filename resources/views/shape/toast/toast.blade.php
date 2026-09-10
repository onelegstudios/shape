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

    `size` is the alert's, step for step, because the two are the same block
    saying the same thing in two places. The bar is the one thing that does not
    move with it: four pixels is a mark rather than a measurement, and a tone
    stripe that thinned at `xs` would be hardest to see exactly where the toast
    is smallest.

    The toaster stamps its seven templates at the default and has no `size` of
    its own, because a prop it resolved per request would cost those templates
    their fold. Ejecting the toaster and writing the word into its call sites is
    the way to move every toast on a page; the prop here is what that writes.
--}}

@props([
    'tone' => null,
    'heading' => null,
    'description' => null,
    'icon' => null,
    'iconSize' => null,
    'dismissible' => true,
    'size' => 'base',
])

@php
$glyph = $icon ?? match ($tone) {
    'success' => 'shape-success',
    'danger' => 'shape-danger',
    'warning' => 'shape-warning',
    'info' => 'shape-info',
    default => null,
};

// The alert's steps: the inset and the gap beside the glyph, the type the two
// lines are set in, and the glyph's own step. The `mt-0.5` under the glyph holds
// across them for the alert's reason — half the difference between the line and
// the mark is two pixels wherever the pair lands.
[$inset, $messageSize, $glyphSize] = match ($size) {
    'xs' => ['[:where(&)]:gap-2 [:where(&)]:p-2.5', 'xs', 'xs'],
    'sm' => ['[:where(&)]:gap-2.5 [:where(&)]:p-3', 'sm', 'sm'],
    'lg' => ['[:where(&)]:gap-4 [:where(&)]:p-5', 'base', 'base'],
    'xl' => ['[:where(&)]:gap-5 [:where(&)]:p-6', 'lg', 'base'],
    default => ['[:where(&)]:gap-3 [:where(&)]:p-4', 'sm', 'sm'],
};

[$dismissSize, $dismissGlyph, $dismissInset] = match ($size) {
    'xs' => ['xs', 'xs', '-mr-1 -mt-1'],
    'sm' => ['xs', 'xs', '-mr-1.5 -mt-1.5'],
    'lg' => ['base', 'sm', '-mr-2 -mt-2'],
    'xl' => ['lg', 'sm', '-mr-2.5 -mt-2.5'],
    default => ['sm', 'xs', '-mr-1.5 -mt-1.5'],
};

$iconSize ??= $glyphSize;

$classes = Shape::classes()
    ->add('pointer-events-auto flex w-full items-start')
    ->add('[:where(&)]:rounded-shape [:where(&)]:shadow-lg')
    ->add($inset)
    ->add('[:where(&)]:border [:where(&)]:border-shape-200 dark:[:where(&)]:border-shape-800')
    ->add('[:where(&)]:border-l-4 [:where(&)]:border-l-[var(--shape-tone)]')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]');
@endphp

<div
    {{ $attributes->class($classes) }}
    data-shape-toast
    data-shape-size="{{ $size }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
>
    <span class="mt-0.5 shrink-0 text-[color:var(--shape-tone-ink)] empty:hidden" data-shape-toast-icon>
        @if ($glyph)
            <x-shape::icon :name="$glyph" :size="$iconSize" />
        @endif
    </span>

    <div class="flex min-w-0 flex-1 flex-col gap-1">
        <x-shape::heading :level="3" :size="$messageSize" class="empty:hidden" data-shape-toast-heading="">{{ $heading }}</x-shape::heading>

        <x-shape::text :size="$messageSize" variant="muted" class="empty:hidden" data-shape-toast-description="">{{ $description }}</x-shape::text>
    </div>

    @if ($dismissible)
        <x-shape::button
            square
            :size="$dismissSize"
            variant="ghost"
            icon="shape-close"
            :icon-size="$dismissGlyph"
            aria-label="Dismiss"
            class="{{ $dismissInset }} shrink-0"
            data-shape-dismiss=""
        />
    @endif
</div>
