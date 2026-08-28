@blaze(fold: true)

{{--
    A row of a list, and a slot rather than a set of props.

    The table's cell takes a `value` because a cell usually holds one, and that
    is what lets it memoize. A list item almost never holds one — it holds an
    avatar, two lines of text and a button — so a `value` prop here would buy
    nothing and cost the composition that is the whole reason to reach for a
    list instead of a table.

    No outer margin and no border. The list owns the space between its children
    and draws the rule between them.
--}}

@props([])

<li
    {{ $attributes->class('flex items-center [:where(&)]:gap-3 [:where(&)]:py-3') }}
    data-shape-list-item
>{{ $slot }}</li>
