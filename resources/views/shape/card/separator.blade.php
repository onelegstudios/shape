@blaze(fold: true, memo: true)

{{--
    A hairline that reaches the card's edges.

    `<x-shape::separator />` stops at the card's padding, because it is a child
    like any other. This one cancels the inset the way a bleeding header does,
    on the axis it crosses: across a vertical card it is a horizontal rule taken
    to both sides, and between the picture and the body of a horizontal card it
    is a vertical rule taken to the top and the bottom. Which one is read from
    the card's attribute, so there is no `orientation` to keep in step with it.

    Anywhere else — inside a `card.body`, say — it is a plain horizontal rule,
    because the edges it would bleed to are not its parent's.

    Always self-closing and slotless, like the separator, so it memoizes. It
    has no label: a labelled rule is the separator's, and does not bleed.
--}}

@php
$classes = Shape::classes()
    ->add('shrink-0')
    ->add('[:where(&)]:bg-shape-200 dark:[:where(&)]:bg-shape-800')
    ->add('[:where(:not([data-shape-card][data-shape-orientation=horizontal])>&)]:h-px')
    ->add('[:where([data-shape-card][data-shape-orientation=vertical]>&)]:-mx-(--shape-card-inset)')
    ->add('[:where([data-shape-card][data-shape-orientation=horizontal]>&)]:w-px')
    ->add('[:where([data-shape-card][data-shape-orientation=horizontal]>&)]:-my-(--shape-card-inset)');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-separator role="separator" aria-hidden="true"></div>
