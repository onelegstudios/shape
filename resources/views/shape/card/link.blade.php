@blaze(fold: true)

{{--
    A link that makes the whole card pressable, for a card that also holds
    controls of its own.

    `href` on the card is the simpler answer and the right one when there is
    nothing else in the card to press. Once there is, the card cannot be the
    anchor: an `<a>` with a button inside it is markup the parser rewrites. So
    the link goes where its text belongs — usually the heading — and its
    `::after` is stretched over the card, which is positioned for it. The card
    draws the hover tint and the focus ring, and lifts its other controls above
    the stretch.

    The link keeps its own outline off because the card draws the ring; the
    link's own box is only as big as its text.
--}}

@php
$classes = Shape::classes()
    ->add('focus-visible:outline-none')
    ->add('after:absolute after:inset-0 after:rounded-[inherit]');
@endphp

<a {{ $attributes->class($classes) }} data-shape-card-link>{{ $slot }}</a>
