@blaze(fold: true)

{{--
    A table, in a box that can scroll, with an empty state beside it.

    Three things about that arrangement are deliberate.

    The attribute bag lands on the wrapper rather than on the `<table>`. Almost
    everything a caller wants to say about a table is really about its box — a
    `max-h-*`, a `wire:key`, a class — and the one thing that has to be said
    about the box is the height bound a sticky head needs to work at all.

    The empty state is a sibling of the `<table>`, not a row inside it. A `<div>`
    written inside a `<tbody>` is foster-parented back out of the table by the
    HTML parser before any stylesheet sees it, so the choice is between a full
    width `<td colspan>` — which needs a column count this component has no way
    to know — and a sibling. The wrapper the table already needs makes the
    sibling free.

    And the empty state is always rendered. Asking whether the table has rows
    would mean inspecting the slot, which is a runtime question that would take
    this component off the fold path; a `:has()` rule in the stylesheet answers
    it instead, and costs nothing.

    A table is content, not a surface: no border, no background, no elevation of
    its own. Compose it inside a card when it needs to sit on one.

    `size` is density, and it lives here rather than on the cell for the reason
    the tab strip's does: a table is one thing, and a table whose rows were set
    at four densities is not a table anyone asked for. Stating it once is also
    what keeps it off `table.cell`, which is the component in this library that
    renders most often and the one whose prop list is worth defending.

    It reaches the cells as descendant utilities. Every measurement a cell and a
    heading draw themselves with carries the library's zero specificity, so a
    rule from the wrapper outranks it — no `!important`, no prop handed through
    three components, and nothing extra rendered per row. `base` emits none of
    them and leaves those defaults standing.
--}}

@props([
    'size' => 'base',
    'empty' => true,
    'emptyIcon' => null,
    'emptyHeading' => 'Nothing here yet',
    'emptyDescription' => null,
])

@php
// The inset on both kinds of cell, the type the table is set in, and — at the
// two large steps — the column headings, which are `text-2xs` and have nowhere
// smaller to go. They are selected as elements rather than by their data
// attributes so the rule stays one class name long; a `<td>` inside a table
// inside a cell is the one arrangement that would catch the wrong one, and a
// table nested in a table has larger problems.
$classes = Shape::classes()
    ->add('[:where(&)]:overflow-x-auto')

    ->add(match ($size) {
        'xs' => '[&>table]:text-xs [&_th]:px-2 [&_th]:py-1 [&_td]:px-2 [&_td]:py-1.5',
        'sm' => '[&>table]:text-sm [&_th]:px-2.5 [&_th]:py-1.5 [&_td]:px-2.5 [&_td]:py-2',
        'lg' => '[&>table]:text-base [&_th]:px-4 [&_th]:py-3 [&_th]:text-xs [&_td]:px-4 [&_td]:py-4',
        'xl' => '[&>table]:text-lg [&_th]:px-5 [&_th]:py-4 [&_th]:text-sm [&_td]:px-5 [&_td]:py-5',
        default => null,
    });
@endphp

<div {{ $attributes->class($classes) }} data-shape-table data-shape-size="{{ $size }}">
    <table class="w-full [:where(&)]:text-sm [:where(&)]:text-[color:var(--shape-fg)]">{{ $slot }}</table>

    @if ($empty)
        <div data-shape-table-empty>
            <x-shape::empty
                :size="$size"
                :icon="$emptyIcon"
                :heading="$emptyHeading"
                :description="$emptyDescription"
            />
        </div>
    @endif
</div>
