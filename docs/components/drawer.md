# Drawer

The [modal](modal.md), pinned to an edge. Same `<dialog>`, same trigger, same
close button.

@docs('preview', name: 'drawer')

## Sides

`side` sets a data attribute and `shape.css` pins the dialog to that edge and
slides it in from the direction it is pinned to. Nothing measures anything:

@docs('preview', name: 'drawer-sides')

## Sizes

`size` is measured across the viewport — or down it, for `side="bottom"`:

@docs('preview', name: 'drawer-sizes')

## It shares the modal's trigger, close and footer

There is no `drawer.trigger`. The thing being opened is named in `for`, so one
trigger covers both:

```blade
<x-shape::overlay.trigger for="cart">Cart</x-shape::overlay.trigger>
<x-shape::overlay.close for="cart" label="Keep shopping" />
```

## The body scrolls, not the panel

A drawer holds a list, a filter panel, a form — content that outgrows the
viewport. The body is its own scroll container, so the heading stays put while
the content moves under it. Nothing is needed at the call site.

## Motion

`@starting-style` and `transition-behavior: allow-discrete` are what make a
transition *into* the top layer possible; without both, an overlay can only be
animated on the way out. The whole treatment sits behind
`prefers-reduced-motion: no-preference`, so a drawer appears without sliding for
anyone who asked for that.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the dialog's id |
| `heading` | — | title row, and the dialog's accessible name |
| `description` | — | a line under the heading |
| `side` | `right` | `right`, `left`, `bottom` |
| `size` | `base` | `sm`, `base`, `lg` |
| `dismissible` | `true` | `false` removes the close button and holds Escape off |

The default slot is the body.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md) and
[Overlays](../overlays.md).
