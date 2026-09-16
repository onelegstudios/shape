@blaze(fold: true)

{{--
    The action row at the bottom of a card.

    Wraps rather than overflows, because a card is often narrower than whoever
    wrote the button labels expected.

    `bleed` is the one place something inside a card sets its own outer margin,
    and only when asked: it cancels the card's inset on the sides and the bottom
    and puts the same inset back inside, so a fill reaches the edge while the
    actions stay where they were. The margin is the card's own variable, so the
    two cannot drift apart, and the corners inherit the card's radius rather
    than the card clipping its children.
--}}

@props([
    'bleed' => false,
])

@php
$classes = Shape::classes()
    ->add('flex flex-wrap items-center [:where(&)]:gap-3')
    ->add($bleed ? '[:where(&)]:-mx-(--shape-card-inset) [:where(&)]:-mb-(--shape-card-inset) [:where(&)]:p-(--shape-card-inset) [:where(&)]:rounded-b-[inherit]' : '');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-footer @if ($bleed) data-shape-bleed @endif>
    {{ $slot }}
</div>
