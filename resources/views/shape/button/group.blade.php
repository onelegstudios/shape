@blaze(fold: true, safe: ['label'])

{{--
    Related actions joined into one control. A segmented row, a split button, a
    toolbar cluster — the shape a set of buttons takes when they are one
    decision rather than several.

    Three utilities and no stylesheet rule. The children are laid out in a row,
    the corners that face a neighbour are squared off, and every child past the
    first is pulled back a pixel so that two 1px borders meet as one seam rather
    than stacking into a 2px rule.

    The corners win without `!important`, because the button writes its own at
    zero specificity — `[:where(&)]:rounded-shape` — and a child selector
    carrying two pseudo-classes lands well above that. A caller's own
    `rounded-full` on the first button still shapes the end it is on: the group
    never names a corner that faces outward, only the ones that face a
    neighbour. Which is what makes a pill-ended group a class at a call site and
    not a prop here.

    The focus ring turns inward, and that is the one decision here worth
    arguing. A button draws its ring 2px outside itself, the neighbour begins a
    pixel away, and later siblings paint over earlier ones — so in a group every
    button but the last would have the ring on its right edge painted out by the
    button beside it. The usual answer is `z-index`, and this library has
    promised there isn't one anywhere. So the ring moves inside the button
    instead, where nothing can cover it and no stack has to be invented.
    `:not(:only-child)` is what keeps a group of one from paying for it: a lone
    button has no neighbour, so it keeps the outward ring it wears everywhere
    else.

    Nothing here paints. A group is a container, so a button in it looks exactly
    like a button outside it — which also means a group of `primary` buttons has
    no seam to show, because a fill has no edge. Ask for `border` there, or
    reach for `outline`, and the seam is the tone's own edge.

    Keep a menu outside the group. A closed popover is `display: none`, which is
    not the same as being absent — `:last-child` still counts it, and the button
    that is actually last would lose the corner it needs. So a split button is
    the trigger inside the group and the `<x-shape::dropdown>` after it, which
    is where the menu sits in every other example too.

    `orientation` picks the class set and so is read at compile time; `label` is
    only ever interpolated, and a group named from a variable still folds.
--}}

@props([
    'orientation' => 'horizontal',
    'label' => null,
])

@php
$vertical = $orientation === 'vertical';

$classes = Shape::classes()
    ->add('inline-flex')
    ->add($vertical ? 'flex-col items-stretch' : 'items-center')

    // The seam: a pixel back, so two borders overlap into one.
    ->add($vertical
        ? '[&>*:not(:first-child)]:-mt-px'
        : '[&>*:not(:first-child)]:-ml-px')

    // Only the corners that meet another button.
    ->add($vertical
        ? '[&>*:not(:first-child)]:rounded-t-none [&>*:not(:last-child)]:rounded-b-none'
        : '[&>*:not(:first-child)]:rounded-l-none [&>*:not(:last-child)]:rounded-r-none')

    // Inward, where the neighbour cannot paint over it.
    ->add('[&>*:not(:only-child):focus-visible]:-outline-offset-2');
@endphp

<div
    {{ $attributes->class($classes)->merge(['role' => 'group', 'aria-label' => $label]) }}
    data-shape-button-group
    data-shape-orientation="{{ $orientation }}"
>{{ $slot }}</div>
