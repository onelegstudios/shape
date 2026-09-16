@blaze(fold: true)

{{--
    A picture at the top or the bottom of a card — or at the start or the end of
    a horizontal one — taken to the card's edge.

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

    In a horizontal card the same reading turns a quarter: the media reaches the
    top and the bottom always, and the start or the end by position. Its width is
    a third of the card until a class says otherwise, and the picture inside it
    is laid over the wrapper rather than sized by it, so a tall image fills the
    height the content sets instead of stretching the card to its own. Every
    rule is keyed to the card's `data-shape-orientation`, so the two sets never
    meet on one element.
--}}

@php
$classes = Shape::classes()
    ->add('[:where(&)]:overflow-hidden')
    ->add('[:where(&>*)]:block [:where(&>*)]:w-full')

    // Vertical: the sides always, the top or the bottom by position.
    ->add('[:where([data-shape-card][data-shape-orientation=vertical]>&)]:-mx-(--shape-card-inset)')
    ->add('[:where([data-shape-card][data-shape-orientation=vertical]>&:first-child)]:-mt-(--shape-card-inset) [:where([data-shape-card][data-shape-orientation=vertical]>&:first-child)]:rounded-t-[inherit]')
    ->add('[:where([data-shape-card][data-shape-orientation=vertical]>&:last-child)]:-mb-(--shape-card-inset) [:where([data-shape-card][data-shape-orientation=vertical]>&:last-child)]:rounded-b-[inherit]')

    // Horizontal: the top and the bottom always, the start or the end by
    // position, and the picture laid over the wrapper.
    ->add('[:where([data-shape-card][data-shape-orientation=horizontal]>&)]:relative [:where([data-shape-card][data-shape-orientation=horizontal]>&)]:shrink-0 [:where([data-shape-card][data-shape-orientation=horizontal]>&)]:w-1/3')
    ->add('[:where([data-shape-card][data-shape-orientation=horizontal]>&)]:-my-(--shape-card-inset)')
    ->add('[:where([data-shape-card][data-shape-orientation=horizontal]>&:first-child)]:-ms-(--shape-card-inset) [:where([data-shape-card][data-shape-orientation=horizontal]>&:first-child)]:rounded-s-[inherit]')
    ->add('[:where([data-shape-card][data-shape-orientation=horizontal]>&:last-child)]:-me-(--shape-card-inset) [:where([data-shape-card][data-shape-orientation=horizontal]>&:last-child)]:rounded-e-[inherit]')
    ->add('[:where([data-shape-card][data-shape-orientation=horizontal]>&>*)]:absolute [:where([data-shape-card][data-shape-orientation=horizontal]>&>*)]:inset-0 [:where([data-shape-card][data-shape-orientation=horizontal]>&>*)]:h-full');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card-media>
    {{ $slot }}
</div>
