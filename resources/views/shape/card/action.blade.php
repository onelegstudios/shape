@blaze(fold: true)

{{--
    The controls at the top-right of a card, beside its title — a menu, a
    close button, a toggle.

    It only means something inside a `card.header`, which gives it a column of
    its own across the heading and its line of copy. Placement is the header's
    grid; the action lines its children up and keeps them to the top edge, so
    a two-line title does not push the menu down to its middle.
--}}

@php
$classes = Shape::classes()
    ->add('col-start-2 row-span-2 row-start-1 self-start justify-self-end')
    ->add('flex items-center [:where(&)]:gap-2');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-action>
    {{ $slot }}
</div>
