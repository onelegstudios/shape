@blaze(fold: true)

{{--
    The action row at the bottom of a modal or a drawer.

    A separate component rather than a named slot, for the same reason the card's
    footer is one: deciding whether a slot has content is a runtime question, and
    this library does not ask those.

    Actions read left to right in increasing importance, so the primary one goes
    last — the position closest to where the pointer already is.
--}}

@props([])

<div {{ $attributes->class('flex flex-wrap items-center justify-end [:where(&)]:gap-3 [:where(&)]:pt-2') }} data-shape-overlay-footer>
    {{ $slot }}
</div>
