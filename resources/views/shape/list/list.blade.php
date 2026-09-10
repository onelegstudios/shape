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

    `size` is the list's rather than the item's, the way the table's density is
    the table's: the rows of one list are one thing, and stating the word once
    keeps `list.item` a component with no props at all. It reaches the items as
    descendant utilities, which outrank the zero-specificity insets an item draws
    itself with — so nothing is handed down and nothing extra is rendered per row.
--}}

@props([
    'as' => 'ul',
    'size' => 'base',
    'empty' => true,
    'emptyIcon' => null,
    'emptyHeading' => 'Nothing here yet',
    'emptyDescription' => null,
])

@php
$classes = Shape::classes()
    ->add('[:where(&)]:divide-y [:where(&)]:divide-shape-200 dark:[:where(&)]:divide-shape-800')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')

    // The type the rows are set in and the room each one keeps, in one arm. The
    // gap is the distance between what an item holds — an avatar, two lines and
    // a button — and it grows with the padding, because a row that got taller and
    // kept its columns where they were would read as a stretched row rather than
    // as a roomier one.
    ->add(match ($size) {
        'xs' => '[:where(&)]:text-xs [&>li]:gap-2 [&>li]:py-1.5',
        'sm' => '[:where(&)]:text-sm [&>li]:gap-2.5 [&>li]:py-2',
        'lg' => '[:where(&)]:text-base [&>li]:gap-4 [&>li]:py-4',
        'xl' => '[:where(&)]:text-lg [&>li]:gap-5 [&>li]:py-5',
        default => '[:where(&)]:text-sm',
    });
@endphp

<div data-shape-list data-shape-size="{{ $size }}">
    <{{ $as }} {{ $attributes->class($classes) }}>{{ $slot }}</{{ $as }}>

    @if ($empty)
        <div data-shape-list-empty>
            <x-shape::empty
                :size="$size"
                :icon="$emptyIcon"
                :heading="$emptyHeading"
                :description="$emptyDescription"
            />
        </div>
    @endif
</div>
