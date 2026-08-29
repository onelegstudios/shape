# Table

A `<table>` in a box that scrolls.

@docs('preview', name: 'table', layout: 'stack')

`table.head` renders the `<thead>` **and** its `<tr>`, so headings go directly
inside it. Rows go in `table.body`.

## Alignment

`align="end"` also sets tabular figures — a right-aligned column is a number
column, and figures that don't line up are the reason it was right-aligned:

@docs('preview', name: 'table-align', layout: 'stack')

## Cells: prop or slot

`value` is the form a table is mostly made of, and the one that folds on a
per-row value. The slot is there for a cell holding a component:

@docs('preview', name: 'table-cell-slot', layout: 'stack')

```blade
<x-shape::table.cell :value="$invoice->total" align="end" />

<x-shape::table.cell>
    <x-shape::badge :label="$invoice->status" color="success" />
</x-shape::table.cell>
```

## The empty state

You don't ask for it and you don't switch it on. It is rendered every time and
removed by a `:has()` rule the moment the table has a row in it:

@docs('preview', name: 'table-empty', layout: 'stack')

Asking Blade whether the table has rows would mean inspecting a slot, which is a
runtime question, and the table would stop folding for it.

Two consequences worth knowing. It sits *beside* the table rather than inside —
a `<div>` written into a `<tbody>` is thrown back out by the HTML parser. And it
counts `table.row` elements inside a `<tbody>`, so a row written by hand as
`<tr>` will not be counted.

Pass `:empty="false"` to turn it off.

## Sticky headers need a height bound

@docs('preview', name: 'table-sticky', layout: 'stack')

Without the `max-h-*` the prop does nothing at all, and it is worth knowing why:
the wrapper gives the table horizontal scrolling with `overflow-x: auto`, and
CSS forces the other axis to `auto` too — so the wrapper is the scroll
container, and sticking to the top of a box with no height of its own is
sticking to nothing.

The head draws its rule with an inset shadow rather than a bottom border, for a
second reason in the same area: under collapsed borders — which is every table,
because Tailwind's preflight sets it — a border belongs to the table box rather
than the cell, so a stuck `<th>` scrolls away and leaves its own border behind.

## The component is the box, not the table

The attribute bag lands on the wrapper that scrolls, not on the `<table>`
element. Almost everything you want to say about a table is really about its box
— a height bound, a key, a class.

A table is content rather than a surface: no border, no background, no elevation
of its own. Put it in a [card](card.md) when it should sit on one, with
`padding="none"`.

## Rows

`table.body` carries `divide-y`; rows carry nothing. That works because
Tailwind's preflight sets `border-collapse: collapse` on every table — in the
CSS default a border on a `<tr>` is ignored by specification, so an application
that disables preflight loses its row rules and wants a bottom border on the
cells instead.

Rows have no hover highlight by default. A row that lights up under the pointer
is saying it does something, and most rows don't. The ones that do say so:
`class="hover:bg-shape-50"`.

## Reference

### Table

| Prop | Default | Values |
| --- | --- | --- |
| `empty` | `true` | render the built-in empty state |
| `empty-icon` | — | icon name for it |
| `empty-heading` | `Nothing here yet` | |
| `empty-description` | — | |

### Head

| Prop | Default | Values |
| --- | --- | --- |
| `sticky` | `false` | needs a height bound on the table |

### Heading

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the heading text, as a prop |
| `align` | `start` | `start`, `center`, `end` |
| `scope` | `col` | |

### Cell

| Prop | Default | Values |
| --- | --- | --- |
| `value` | — | the cell's contents, as a prop |
| `align` | `start` | `start`, `center`, `end` |

`table.body` and `table.row` take no props.

## Folding

Tier A — `@blaze(fold: true)` on `table`, `table.head`, `table.body` and
`table.row`.

`table.heading` and `table.cell` are tier B —
`@blaze(fold: true, memo: true, safe: ['value'])` on the cell — because they are
what a table is mostly made of. `value` is interpolated and never branched on,
so a cell built from a per-row value still folds; `align` picks between class
strings, so it doesn't.

See [Folding](../folding.md) and [Data display](../data.md).
