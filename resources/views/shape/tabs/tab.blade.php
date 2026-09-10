@blaze(fold: true, safe: ['for'])

{{--
    One tab, and it decides its own element the way a menu item does: an `<a>`
    when it has an `href`, a `<button>` otherwise. Middle-click, "open in new
    tab" and the status bar all work for a link and none of them work for a
    button pretending to be one.

    Which mode it is in also decides which state attribute it carries.
    `aria-current="page"` is the claim a navigation link makes; `aria-selected`
    is the claim a tab makes about a panel in this document. Writing both would
    be saying two different things at once.

    The active look is a Tailwind variant on the tab itself rather than a rule in
    the parent that reaches down into its children. ARIA already requires the
    state to live here, so `aria-selected:` and `aria-[current=page]:` have
    everything they need — and the siblings recede because muted is their resting
    state, not because anything dims them.

    `selected` drives those attributes, so it branches and is not safe. A tab
    strip whose selection comes from the current route therefore does not fold,
    which is a handful of components on a page and worth knowing rather than
    worth avoiding.

    There is no `size` here. The strip carries it and reaches these through its
    own class list, which is why every measurement below is written at zero
    specificity — including the gap, which would otherwise be the one utility a
    strip could not move.
--}}

@props([
    'for' => null,
    'selected' => false,
    'icon' => null,
    'iconSize' => 'sm',
    'as' => null,
])

@php
$classes = Shape::classes()
    ->add('inline-flex items-center justify-center whitespace-nowrap [:where(&)]:gap-2')
    ->add('[:where(&)]:rounded-shape [:where(&)]:px-3 [:where(&)]:py-1.5 [:where(&)]:text-sm [:where(&)]:font-medium')
    ->add('text-[color:var(--shape-fg-muted)] hover:text-[color:var(--shape-fg)]')
    ->add('aria-selected:bg-[var(--shape-tone-tint)] aria-selected:text-[color:var(--shape-tone-ink)]')
    ->add('aria-[current=page]:bg-[var(--shape-tone-tint)] aria-[current=page]:text-[color:var(--shape-tone-ink)]')
    ->add('focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]')
    ->add('disabled:pointer-events-none disabled:opacity-50');

$link = $attributes->has('href');

$state = $link
    ? ['aria-current' => $selected ? 'page' : null]
    : [
        'role' => 'tab',
        'id' => $for.'-tab',
        'aria-controls' => $for,
        'aria-selected' => $selected ? 'true' : 'false',
        // Roving tab order: the selected tab is the strip's single tab stop, and
        // the arrow keys move within it. Exactly one tab may carry `0`.
        'tabindex' => $selected ? '0' : '-1',
    ];
@endphp

<x-shape::button.element
    :as="$as ?? ($link ? 'a' : null)"
    type="button"
    {{ $attributes->merge($state)->class($classes) }}
    data-shape-tab=""
    data-shape-tone="brand"
>
    @if ($icon)
        <x-shape::icon :name="$icon" :size="$iconSize" class="opacity-70" />
    @endif

    {{ $slot }}
</x-shape::button.element>
