@blaze(fold: true, safe: ['as'])

{{--
    A stack of records, separated by a rule rather than by a border each.

    The empty state is always rendered. Deciding whether the list has items
    would mean inspecting the slot, which is a runtime question and would drop
    this component off the fold path — so the question is asked in CSS instead,
    with a `:has()` rule in the stylesheet that removes the empty state as soon
    as one item exists. The component promises an empty state and pays nothing
    for it.

    That is also why there is a wrapper around the list at all: the empty state
    is a sibling of the `<ul>`, not a child of it, because an `<li>` holding an
    empty state would be an item like any other and would hide itself.

    `as` is interpolated into the tag name and nothing else, so it stays safe —
    an ordered list bound from a variable still folds.

    Separation is `divide-y` on the list rather than a border on each item. Both
    draw the same line; only one of them draws it in the right number of places.
--}}

@props([
    'as' => 'ul',
    'empty' => true,
    'emptyIcon' => null,
    'emptyHeading' => 'Nothing here yet',
    'emptyDescription' => null,
])

@php
$classes = Shape::classes()
    ->add('[:where(&)]:divide-y [:where(&)]:divide-shape-200 dark:[:where(&)]:divide-shape-800')
    ->add('[:where(&)]:text-sm [:where(&)]:text-[color:var(--shape-fg)]');
@endphp

<div data-shape-list>
    <{{ $as }} {{ $attributes->class($classes) }}>{{ $slot }}</{{ $as }}>

    @if ($empty)
        <div data-shape-list-empty>
            <x-shape::empty
                :icon="$emptyIcon"
                :heading="$emptyHeading"
                :description="$emptyDescription"
            />
        </div>
    @endif
</div>
