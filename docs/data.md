# Data display

Everything else in Shape renders a handful of times on a page. These components
render once per row, so the rules that were advice elsewhere have a price here.

## The empty state is a CSS question

[Table](components/table.md) and [list](components/list.md) both ship an empty
state you don't have to ask for. Neither of them ever finds out whether it has
rows.

Asking is the problem. "Does this slot have content in it" is answerable only at
render time, and a component that asks it stops folding — so a promise made for
the sake of the applications that ship a blank div would have been paid for by
every application that doesn't. So the empty state is always in the markup, and
one rule in the stylesheet removes it:

```css
[data-shape-table]:has(tbody [data-shape-table-row]) [data-shape-table-empty],
[data-shape-list]:has([data-shape-list-item]) [data-shape-list-empty] {
    display: none;
}
```

Two things follow from that, and both are visible at the call site.

The empty state is a **sibling** of the `<table>` or the `<ul>`, inside a wrapper
the component renders for the purpose. A `<div>` written into a `<tbody>` is
thrown back out of the table by the HTML parser before any stylesheet sees it,
and an `<li>` holding an empty state would be an item like any other and would
hide itself.

And the rule counts *Shape's* rows. A row written by hand as `<tr>` rather than
`<x-shape::table.row>` is not counted, and a table full of them will show its
empty state underneath.

The `<select>` does not do this, and cannot. A `<select>` may contain only
`option`, `optgroup` and script-supporting elements; anything else is discarded
by the parser. Its empty state is the `placeholder` option it already takes.

## What a loop costs

Inside a row, only two questions matter: does the component fold, and if not,
does its memo actually hit.

| In a row | | |
| --- | --- | --- |
| `table.cell` with `:value` | folds | `value` is safe |
| `table.heading` with `:label` | folds | `label` is safe |
| `badge` with a static colour | folds | `label` is safe, `color` is not |
| `stat` with `:value` and `:delta` | folds | both safe; see below |
| `avatar` with `:initials` | folds | |
| `avatar` with a per-row `src` | no | memo entry per URL, no hits |
| `tabs.tab` with a computed `selected` | no | not in a loop, so it doesn't matter |

The cell is the one that earns the most. A table is mostly cells, and
`safe: ['value']` is the single declaration that keeps a per-row value on the
fold path instead of dropping it onto the memo path — where a prop set unique
per row means one cache entry per row and no hits at all. Two hundred cells is
the difference between free and the expensive case
[Folding](folding.md) measures.

Two components in this set are prop-first for exactly this reason, and it is
worth seeing why the API is shaped the way it is:

- **`stat` renders its delta row always, and collapses it with `empty:hidden`.**
  Written as `@if ($delta)` the prop would drive a branch, could not be declared
  safe, and every stat with a computed delta — which is all of them — would stop
  folding. The same device the [empty](components/empty.md) component uses for
  its action row, doing more work here.
- **`table.cell` takes a `value` prop and a slot.** Blaze memoizes per call site
  rather than per file, so a cell written self-closing memoizes even though the
  file also renders a slot for the cells that hold a badge.

And one that pays, knowingly. An avatar's `src` decides which element renders —
an `<img>` with no source is a broken image request — so it branches, and a list
of per-row photographs neither folds nor usefully memoizes. It is annotated
`memo: true` because the initials form is common and does fold. Twenty avatars
is twenty components; the arithmetic is different from two hundred cells, and
the docs say so rather than leaving it to be found.

## The pager loops, so it does not fold

[Pagination](components/pagination.md) is the second component in the library
compiled but not folded, after the toaster. It iterates a collection the server
produced this request, and a folded template would hold one visitor's page of
links forever.

The rule the two of them share is short: **a component that loops data the
server produced has nothing to bake.** It is worth stating because the reflex —
annotate everything `fold: true` and let Blaze abort where it must — produces a
component that folds successfully and is wrong.

That also decides how the pager is built inside. Everywhere else a nested Shape
component folds into its parent and costs nothing at render time; here nothing
folds, so the links are plain elements rather than nested components, in the one
place in the library that iterates.

## Islands, for the parts that change

Livewire 4's `@island` is the right tool for a region inside a component that
updates on its own — a table's rows under sort and pagination, a notifications
list, an expensive stat block. It avoids splitting a component in two just to
get a partial re-render.

Shape implements nothing here. There is no Livewire in this package's
`composer.json` and no Livewire component in it, so an island is something you
write around a Shape table rather than something a Shape component gives you:

```blade
@island
    <x-shape::table>
        <x-shape::table.head>…</x-shape::table.head>

        <x-shape::table.body>
            @foreach ($this->rows as $row)
                <x-shape::table.row wire:key="{{ $row->id }}">…</x-shape::table.row>
            @endforeach
        </x-shape::table.body>
    </x-shape::table>

    <x-shape::pagination :paginator="$this->rows" wire:navigate />
@endisland
```

Two constraints to design around, and they decide where the island goes rather
than what is inside it: an island cannot sit inside a loop or a conditional, and
it cannot see template-level `@php` variables — only component properties and
methods. So the island wraps the loop; it never lives inside one.
