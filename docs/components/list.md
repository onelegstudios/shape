# List

The [table](table.md)'s answer without the columns. Reach for it when the
records have one shape rather than several columns — people, activity,
notifications.

@docs('preview', name: 'list', layout: 'stack')

An item is a slot rather than a set of props, unlike a table cell: a list item
almost always holds an avatar, two lines of text and a trailing button, and a
`value` prop would buy nothing while costing the composition that is the reason
to reach for a list at all.

## Other list elements

`as` takes any list element — `ol` for a ranked list:

@docs('preview', name: 'list-ordered', layout: 'stack')

`as` is only ever interpolated into the tag name, so a list whose element is
decided at runtime still folds.

## The empty state

Rendered every time and removed by a `:has()` rule as soon as one item exists:

@docs('preview', name: 'list-empty', layout: 'stack')

It sits beside the `<ul>` rather than inside it, because an `<li>` holding an
empty state would be an item like any other and would hide itself. Pass
`:empty="false"` to turn it off, and see
[Table](table.md#the-empty-state) for the reasoning at length.

## Separation is a rule on the list, not a border on each item

`divide-y` on the `<ul>`, nothing on the `<li>`. It draws the same line and has
nothing to reset on the last item — which is the whole of "use fewer borders",
in one class.

## Theming

The rule between items is `divide-shape-200` — `shape-800` in dark mode — and
the list's text is `--shape-fg` at `text-sm`. The attribute bag lands on the
`<ul>`, so a class reaches both:

@docs('preview', name: 'list-override', layout: 'stack')

The type yields the way everything else in the library does: `text-sm` is
written at zero specificity and `text-base` beats it.

The divider does not, and it is the one place in these pages where the flag is
not optional. Tailwind wraps every `divide-*` utility in `:where()` of its own
accord, so `divide-shape-100` at a call site has zero specificity too — the two
do not merely tie on equal footing, they are both unweighted, and the winner is
whichever Tailwind emitted last. That is the *higher* step, always: `100` is
written before `200`, so the package's default wins and a lighter rule silently
does nothing. `divide-shape-400` would have won for the same reason and taught
the wrong lesson, so the example above asks with `!` — which beats a normal
declaration whatever its specificity, in either direction.

An item's padding and gap are the ordinary arrangement, on the item — zero
specificity, so `py-2` above simply wins:

```blade
<x-shape::list.item class="py-2">…</x-shape::list.item>
```

### Every list at once

```css
[data-shape-list-item] { padding-block: 0.5rem; }
```

Rows have no hover treatment, for the [table](table.md#rows)'s reason: a row
that lights up under the pointer is saying it does something. Where they do, say
so — on the item that does, or on every item in a list that is a list of links:

```css
[data-shape-list-item]:has(a):hover { background-color: var(--color-shape-50); }
```

The empty state is an [`empty`](empty.md#the-ones-you-did-not-write) component
rendered inside the wrapper, so `[data-shape-list] [data-shape-empty]` is what
reaches it.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `as` | `ul` | any list element — `ol` for a ranked list |
| `empty` | `true` | render the built-in empty state |
| `empty-icon` | — | any [icon](icon.md) name |
| `empty-heading` | `Nothing here yet` | |
| `empty-description` | — | |

`list.item` takes no props. The default slot is the items.

## Folding

Tier A — `@blaze(fold: true, safe: ['as'])` on the list, `@blaze(fold: true)` on
the item.

See [Folding](../folding.md) and [Data display](../data.md).
