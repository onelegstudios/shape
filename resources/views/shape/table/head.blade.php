@blaze(fold: true)

{{--
    The `<thead>` and its one `<tr>`, together in one component.

    Splitting them would mean a header row written with `table.row`, and a header
    row carrying `data-shape-table-row` would satisfy the stylesheet rule that
    hides the empty state — so a table with a header and no data would show its
    header, no rows, and no empty state. Rendering both tags here means a header
    row cannot be mistaken for a data row by anything.

    The rule under the head is an inset shadow rather than a border. Under
    collapsed borders — which is every table, because Tailwind's preflight sets
    it — a border belongs to the table box rather than to the cell, and a stuck
    `<th>` scrolls away from its own border and leaves the rule behind. A shadow
    is painted by the cell and travels with it.

    `sticky` works only inside a wrapper with a height bound. `overflow-x: auto`
    on the wrapper forces the block axis to `auto` as well, which makes the
    wrapper the scroll container — and sticking to the top of a box with no
    height of its own is sticking to nothing. So it is
    `<x-shape::table class="max-h-96">`, and the prop does nothing without it.

    No z-index. A sticky box with `z-index: auto` creates no stacking context and
    paints with the positioned descendants, which is already above the in-flow
    cells scrolling under it.
--}}

@props([
    'sticky' => false,
])

@php
$classes = Shape::classes()
    ->add('[&>tr>th]:shadow-[inset_0_-1px_0_var(--color-shape-200)]')
    ->add('dark:[&>tr>th]:shadow-[inset_0_-1px_0_var(--color-shape-800)]')

    ->add($sticky ? '[&>tr>th]:sticky [&>tr>th]:top-0 [&>tr>th]:bg-white dark:[&>tr>th]:bg-shape-900' : '');
@endphp

<thead {{ $attributes->class($classes) }} data-shape-table-head>
    <tr>{{ $slot }}</tr>
</thead>
