@blaze(fold: true)

{{--
    The action row at the bottom of a card.

    Wraps rather than overflows, because a card is often narrower than whoever
    wrote the button labels expected.
--}}

@props([])

<div {{ $attributes->class('flex flex-wrap items-center [:where(&)]:gap-3') }} data-shape-card-footer>
    {{ $slot }}
</div>
