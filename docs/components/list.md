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

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `as` | `ul` | any list element — `ol` for a ranked list |
| `empty` | `true` | render the built-in empty state |
| `empty-icon` | — | icon name for it |
| `empty-heading` | `Nothing here yet` | |
| `empty-description` | — | |

`list.item` takes no props. The default slot is the items.

## Folding

Tier A — `@blaze(fold: true, safe: ['as'])` on the list, `@blaze(fold: true)` on
the item.

See [Folding](../folding.md) and [Data display](../data.md).
