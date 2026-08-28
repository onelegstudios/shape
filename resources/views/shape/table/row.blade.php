@blaze(fold: true)

{{--
    A data row, and the thing the stylesheet counts when it decides whether the
    table has anything in it.

    No hover highlight by default. A row that lights up under the pointer is
    telling someone it does something, and most rows don't. The ones that do say
    so at the call site: `class="hover:bg-shape-50"`, or a link inside a cell.
--}}

@props([])

<tr {{ $attributes }} data-shape-table-row>{{ $slot }}</tr>
