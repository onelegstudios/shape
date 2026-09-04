@blaze(fold: true, safe: ['name'])

{{--
    A menu of actions. The popover, with menu semantics and tighter padding.

    `role="menu"` is a promise about keyboard behaviour, not decoration: it says
    that arrow keys move between the items and that the whole menu is one tab
    stop. shape.js keeps that promise; the items keep their natural tab order
    underneath it, so a browser that never runs the script still leaves every
    item reachable.

    Items are children rather than an `:items` array, for the reason `select`
    gives about its options: an array prop would need a convention for labels,
    icons, destructive styling and `wire:click`, and children already compose
    with `@foreach`, with `<x-shape::separator />`, and with a caller's markup.
--}}

@props([
    'name',
    'placement' => 'bottom-start',
])

<x-shape::popover
    :name="$name"
    :placement="$placement"
    role="menu"
    padding="tight"
    {{ $attributes }}
    data-shape-menu=""
    data-shape-dropdown=""
>{{ $slot }}</x-shape::popover>
