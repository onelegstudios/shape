@blaze(fold: true)

{{--
    The `<tbody>`, and the one place row separation lives.

    `divide-y` here rather than a border on each row: it draws the same line and
    has nothing to reset on the last row. Worth knowing that it draws at all only
    because Tailwind's preflight sets `border-collapse: collapse` — in the CSS
    default a border on a `<tr>` is ignored by specification. An application that
    turns preflight off, or sets `border-separate` on a Shape table, loses its
    row rules and wants a bottom border on the cells instead.
--}}

@props([])

<tbody
    {{ $attributes->class('[:where(&)]:divide-y [:where(&)]:divide-shape-200 dark:[:where(&)]:divide-shape-800') }}
    data-shape-table-body
>{{ $slot }}</tbody>
