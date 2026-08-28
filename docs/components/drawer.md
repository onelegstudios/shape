# Drawer

@docs('preview', name: 'drawer')

## The modal, with a side

Structurally identical: a `<dialog>` opened with `showModal()`, so the focus
trap, the top layer, Escape and inertness are the platform's. What differs is
placement, and placement is CSS — the side sets a data attribute, `shape.css`
pins the dialog to that edge and slides it in from the direction it is pinned
to. Nothing measures anything.

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the dialog's id |
| `heading` | — | title row, and the dialog's accessible name |
| `description` | — | a line under the heading |
| `side` | `right` | `right`, `left`, `bottom` |
| `size` | `base` | `sm`, `base`, `lg` — across the viewport, or down it for `bottom` |
| `dismissible` | `true` | `false` removes the close button and holds Escape off |

## It shares the modal's trigger, close and footer

There is no `drawer.trigger`. The thing being opened is named in `for`, so one
trigger covers both, and a second name for one component is a second thing to
keep in sync:

```blade
<x-shape::overlay.trigger for="cart" icon="plus">Cart</x-shape::overlay.trigger>
<x-shape::overlay.close for="cart" label="Keep shopping" />
```

## The body scrolls, not the panel

A drawer holds a list, a filter panel, a form — content that outgrows the
viewport. The body is its own scroll container so the heading stays put while
the content moves under it. Nothing is needed at the call site.

## Motion

`@starting-style` and `transition-behavior: allow-discrete` are what make a
transition *into* the top layer possible; without both, an overlay can only be
animated on the way out. The whole treatment sits behind
`prefers-reduced-motion: no-preference`, so a drawer appears without sliding for
anyone who asked for that.

## Folding

Tier A — `@blaze(fold: true)`.
