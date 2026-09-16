@blaze(fold: true)

{{--
    The title block at the top of a card: a heading with an optional line of
    supporting copy under it.

    It draws no rule of its own. Separation is the tight gap here against the
    card's wider one — and a caller who genuinely wants a line composes
    `<x-shape::separator />` after it.

    `bleed` mirrors the footer's: the card's inset cancelled on the sides and
    the top and put back inside, for a header with a fill of its own.
--}}

@props([
    'bleed' => false,
])

@php
$classes = Shape::classes()
    ->add('flex flex-col [:where(&)]:gap-1')
    ->add($bleed ? '[:where(&)]:-mx-(--shape-card-inset) [:where(&)]:-mt-(--shape-card-inset) [:where(&)]:p-(--shape-card-inset) [:where(&)]:rounded-t-[inherit]' : '');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-header @if ($bleed) data-shape-bleed @endif>
    {{ $slot }}
</div>
