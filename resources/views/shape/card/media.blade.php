@blaze(fold: true)

{{--
    A picture at the top or the bottom of a card, taken to the card's edge.

    It is a wrapper rather than an image with a `src`, so an `<img>` with its
    `srcset` and `loading`, a `<picture>`, a `<video>` or an embed all go inside
    it without this component having to hand each of their attributes on. The
    child is made a full-width block; its height and fit are the caller's, as
    classes on the child — `aspect-video object-cover`.

    It always cancels the card's inset on the sides. Which of the top and the
    bottom it also cancels is not a prop: the selector reads where the element
    sits, so media first in a card bleeds to the top, media last bleeds to the
    bottom, and media anywhere else only reaches the sides. There is nothing to
    keep in step with the markup, and no runtime question to take it off the
    fold path.

    The corners on the bled edge inherit the card's radius, and the wrapper clips
    its child to them — the card itself still clips nothing.
--}}

@php
$classes = Shape::classes()
    ->add('[:where(&)]:overflow-hidden')
    ->add('[:where(&)]:-mx-(--shape-card-inset)')
    ->add('[:where(&:first-child)]:-mt-(--shape-card-inset) [:where(&:first-child)]:rounded-t-[inherit]')
    ->add('[:where(&:last-child)]:-mb-(--shape-card-inset) [:where(&:last-child)]:rounded-b-[inherit]')
    ->add('[:where(&>*)]:block [:where(&>*)]:w-full');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-media>
    {{ $slot }}
</div>
