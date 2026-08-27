@blaze(fold: true, safe: ['as'])

{{--
    One action in a menu.

    A `<button>` by default and an `<a>` when given an `href`, which is the
    distinction that matters to everything downstream: middle-click, "open in new
    tab" and the status bar all work for a link and none of them work for a
    button pretending to be one.

    Colour rides on `data-shape-tone` the way the button's does, so a destructive
    item reads as destructive without a second variant table.

    Clicking an item closes the menu — shape.js does that, because the popover
    only light-dismisses on a click *outside* it, and an action that leaves its
    own menu standing looks like it didn't fire.
--}}

@props([
    'icon' => null,
    'iconVariant' => 'mini',
    'color' => null,
    'as' => null,
])

@php
$classes = Shape::classes()
    ->add('flex w-full items-center gap-2.5 text-left')
    ->add('[:where(&)]:rounded-[calc(var(--radius-shape)-0.25rem)] [:where(&)]:px-2.5 [:where(&)]:py-1.5 [:where(&)]:text-sm')
    ->add('text-[color:var(--shape-tone-ink)]')
    ->add('hover:bg-[var(--shape-tone-tint)] focus-visible:bg-[var(--shape-tone-tint)]')
    ->add('focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-[var(--shape-ring)]')
    ->add('disabled:pointer-events-none disabled:opacity-50')
    ->add('aria-disabled:pointer-events-none aria-disabled:opacity-50');
@endphp

<x-shape::button.element
    :as="$as ?? ($attributes->has('href') ? 'a' : null)"
    type="button"
    role="menuitem"
    {{ $attributes->class($classes) }}
    data-shape-menu-item=""
    data-shape-tone="{{ $color ?? 'neutral' }}"
>
    @if ($icon)
        <x-shape::icon :name="$icon" :variant="$iconVariant" class="opacity-70" />
    @endif

    {{ $slot }}
</x-shape::button.element>
