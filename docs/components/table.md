# Table

```blade
<x-shape::table>
    <x-shape::table.head>
        <x-shape::table.heading label="Invoice" />
        <x-shape::table.heading label="Client" />
        <x-shape::table.heading label="Amount" align="end" />
    </x-shape::table.head>

    <x-shape::table.body>
        @foreach ($invoices as $invoice)
            <x-shape::table.row wire:key="{{ $invoice->id }}">
                <x-shape::table.cell :value="$invoice->number" />
                <x-shape::table.cell :value="$invoice->client" />
                <x-shape::table.cell :value="$invoice->total" align="end" />
            </x-shape::table.row>
        @endforeach
    </x-shape::table.body>
</x-shape::table>
```

## The component is the box, not the table

The attribute bag lands on the wrapper that scrolls, not on the `<table>`
element. Almost everything you want to say about a table is really about its box
— a height bound, a key, a class — and the one thing a sticky header needs said
is a height bound.

A table is content rather than a surface: no border, no background, no elevation
of its own. Put it in a card when it should sit on one.

```blade
<x-shape::card padding="none">
    <x-shape::table>…</x-shape::table>
</x-shape::card>
```

| Prop | Default | Values |
| --- | --- | --- |
| `empty` | `true` | render the built-in empty state |
| `empty-icon` | — | icon name for it |
| `empty-heading` | `Nothing here yet` | |
| `empty-description` | — | |

## A cell is a prop or a slot, and the prop is the one that pays

```blade
<x-shape::table.cell :value="$invoice->total" align="end" />

<x-shape::table.cell>
    <x-shape::badge :label="$invoice->status" color="success" />
</x-shape::table.cell>
```

Both work on the same component. The self-closing form is the one that folds on
a per-row value and memoizes when it can't — see [Folding](#folding) below — and
it is also the form a table is mostly made of.

| Prop | Default | Values |
| --- | --- | --- |
| `value` | — | the cell's contents, as a prop |
| `align` | `start` | `start`, `center`, `end` |

`align="end"` also sets tabular figures. A right-aligned column is a number
column, and figures that don't line up are the reason it was right-aligned.

## Headings render their own row

`table.head` renders the `<thead>` **and** its `<tr>`. Write the headings
directly inside it:

```blade
<x-shape::table.head>
    <x-shape::table.heading label="Invoice" />
</x-shape::table.head>
```

Splitting them would mean writing a header row with `table.row` — and the rule
that hides the empty state counts data rows, so a header row would satisfy it.
A table with a header and no data would then show a header, no rows, and no
empty state.

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the heading text, as a prop |
| `align` | `start` | `start`, `center`, `end` |
| `scope` | `col` | |

## The empty state is always there

You don't ask for it and you don't switch it on:

```blade
<x-shape::table empty-heading="No invoices yet"
                empty-description="They'll appear here as you raise them.">
    …
</x-shape::table>
```

It is rendered every time and removed by a `:has()` rule the moment the table
has a row in it. Asking Blade whether the table has rows would mean inspecting a
slot, which is a runtime question, and the table would stop folding for it.

Two consequences worth knowing. It sits *beside* the table rather than inside —
a `<div>` written into a `<tbody>` is thrown back out of the table by the HTML
parser before any stylesheet sees it. And it counts `table.row` elements inside
a `<tbody>`, so a row written by hand as `<tr>` will not be counted.

Pass `:empty="false"` to turn it off.

## Sticky headers need a height bound

```blade
<x-shape::table class="max-h-96">
    <x-shape::table.head sticky>…</x-shape::table.head>
</x-shape::table>
```

Without the `max-h-*` the prop does nothing at all, and it is worth knowing why:
the wrapper gives the table horizontal scrolling with `overflow-x: auto`, and
CSS forces the other axis to `auto` too — so the wrapper is the scroll container,
and sticking to the top of a box with no height of its own is sticking to
nothing.

The head draws its rule with an inset shadow rather than a bottom border, for a
second reason in the same area: under collapsed borders — which is every table,
because Tailwind's preflight sets it — a border belongs to the table box rather
than the cell, so a stuck `<th>` scrolls away and leaves its own border behind.

There is no z-index. A sticky box with `z-index: auto` creates no stacking
context and paints with the positioned descendants, which is already above the
cells sliding under it.

## Rows separate on the body

`table.body` carries `divide-y`; rows carry nothing. It draws the same line and
has nothing to reset on the last row.

That works because Tailwind's preflight sets `border-collapse: collapse` on
every table. In the CSS default a border on a `<tr>` is ignored by
specification, so an application that disables preflight — or sets
`border-separate` on a Shape table — loses its row rules and wants a bottom
border on the cells instead.

Rows have no hover highlight by default. A row that lights up under the pointer
is saying it does something, and most rows don't. The ones that do say so:
`class="hover:bg-shape-50"`.

## Folding

Tier A — `@blaze(fold: true)` on `table`, `table.head`, `table.body` and
`table.row`.

`table.heading` and `table.cell` are tier B —
`@blaze(fold: true, memo: true, safe: ['value'])` on the cell — because they are
what a table is mostly made of. `value` is interpolated and never branched on,
so a cell built from a per-row value still folds; `align` picks between class
strings, so it doesn't.

See [Folding](../folding.md) and [Data display](../data.md).
