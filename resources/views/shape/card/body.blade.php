@blaze(fold: true)

{{--
    A column inside a card, for a horizontal card's content.

    A horizontal card lays its children in a row, so the header, the copy and
    the footer beside a picture need something to stack them. The body is that
    column, and it takes the card's gap rather than one of its own — `inherit`
    is the card's padding step, whichever it was — so the content spaces itself
    exactly as it would in a vertical card. It grows to fill the row and may
    shrink below its content's width, so long text wraps rather than pushing
    the picture out of the card.
--}}

@php
$classes = Shape::classes()
    ->add('flex flex-col min-w-0')
    ->add('[:where(&)]:flex-1 [:where(&)]:gap-[inherit]');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-body>
    {{ $slot }}
</div>
