@blaze(fold: true)

{{--
    The title block at the top of a card: a heading with an optional line of
    supporting copy under it.

    It draws no rule of its own. Separation is the tight gap here against the
    card's wider one — and a caller who genuinely wants a line composes
    `<x-shape::separator />` after it.

    `bleed` mirrors the footer's: the card's inset cancelled on the sides and
    the top and put back inside, for a header with a fill of its own.

    It is a grid rather than a column so that a `card.action` can sit to the
    right of the title. The action takes the second column across the first two
    rows, which is where a heading and its line of copy go; everything else is
    held to the first column. The second column only exists when an action is
    there to fill it, which the selector asks of the markup rather than of a
    slot.
--}}

@props([
    'bleed' => false,
])

@php
$classes = Shape::classes()
    ->add('grid [:where(&)]:gap-x-4 [:where(&)]:gap-y-1')
    ->add('[:where(&:has(>[data-shape-card-action]))]:grid-cols-[minmax(0,1fr)_auto]')
    ->add('[:where(&>:not([data-shape-card-action]))]:col-start-1')
    ->add($bleed ? '[:where(&)]:-mx-(--shape-card-inset) [:where(&)]:-mt-(--shape-card-inset) [:where(&)]:p-(--shape-card-inset) [:where(&)]:rounded-t-[inherit]' : '');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-header @if ($bleed) data-shape-bleed @endif>
    {{ $slot }}
</div>
