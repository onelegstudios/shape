@blaze(fold: true, memo: true, safe: ['value'])

{{--
    The component in this library that renders most often, so it is the one whose
    annotation is worth the most.

    `value` is safe: it is interpolated and never branched on, so
    `<x-shape::table.cell :value="$invoice->total" />` — a different string in
    every row of every table — still folds into its parent at compile time. That
    single declaration is what keeps a table off the memo path, where a prop set
    unique per row means a cache entry per row and no hits at all.

    The slot is still here, for the cells that hold a badge or a button rather
    than a value. Writing one costs nothing; it is only the self-closing call
    sites that memoize, and those are the ones that repeat.

    `align="end"` also sets tabular figures, on the argument that a right
    aligned column is a number column and figures that do not line up are the
    reason it was right-aligned in the first place. A caller wanting proportional
    figures passes `class="normal-nums"`.
--}}

@props([
    'value' => null,
    'align' => 'start',
])

@php
$classes = Shape::classes()
    ->add('[:where(&)]:px-3 [:where(&)]:py-3 [:where(&)]:align-middle')

    ->add(match ($align) {
        'center' => '[:where(&)]:text-center',
        'end' => '[:where(&)]:text-end [:where(&)]:tabular-nums',
        default => '[:where(&)]:text-start',
    });
@endphp

<td {{ $attributes->class($classes) }} data-shape-table-cell>{{ $value }}{{ $slot }}</td>
