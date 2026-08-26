@blaze(fold: true)

{{--
    The title block at the top of a card: a heading with an optional line of
    supporting copy under it.

    It draws no rule of its own. Separation is the tight gap here against the
    card's wider one — and a caller who genuinely wants a line composes
    `<x-shape::separator />` after it.
--}}

@props([])

<div {{ $attributes->class('flex flex-col [:where(&)]:gap-1') }} data-shape-card-header>
    {{ $slot }}
</div>
