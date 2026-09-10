# Drawer

The [modal](modal.md), pinned to an edge. Same `<dialog>`, same trigger, same
close button.

@docs('preview', name: 'drawer')

## Sizes

`size` is measured across the viewport — or down it, for
[`side="bottom"`](#sides):

@docs('preview', name: 'drawer-sizes')

## Sides

`side` sets a data attribute and `shape.css` pins the dialog to that edge and
slides it in from the direction it is pinned to. Nothing measures anything:

@docs('preview', name: 'drawer-sides')

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

## Theming

The panel takes the [modal](modal.md#theming)'s paint — white or `shape-900`,
`shadow-xl`, `--shape-fg` — with the radius rounded on the three corners away
from the edge it is pinned to. All of it is zero specificity, and `size` only
sets a `max-width` or a `max-height`, so a class wins:

@docs('preview', name: 'drawer-theming')

Open both: the second is `max-w-sm` against the default's `max-w-md`, `p-8`
against `p-6`, and square where the panel would have rounded its two right-hand
corners.

The rest of this section is rules rather than call sites, and stays written as
CSS for a reason each time — a `::backdrop` has no element to put a class on, and
the margins below sit in a layer that beats one. So the previews stop here: a
preview is a Blade file, and a Blade file cannot carry the stylesheet these need.

The scrim is the modal's `::backdrop` and is recoloured the same way, in a rule
that can name both:

```css
[data-shape-modal]::backdrop,
[data-shape-drawer]::backdrop {
    background: color-mix(in oklch, var(--color-shape-950) 70%, transparent);
}
```

Which edge the panel is pinned to is `data-shape-side`, and the margins that pin
it are in `@layer shape-overlay` — after Tailwind's utilities, so a margin class
on the drawer loses to them. That is what keeps a `space-y-*` on some ancestor
from sliding the panel off its edge, and it means an inset of your own is a
rule:

```css
[data-shape-drawer][data-shape-side='right'] {
    block-size: calc(100dvh - 2rem);
    margin-block: 1rem;
    margin-inline: auto 1rem;
}
```

The height is in that same layer, which is why a floating drawer is three
declarations rather than one: the panel is `100dvh` tall until something says
otherwise, and an inset added to a full-height panel is an inset that overflows.

The slide is drawn from the same attribute, behind
`prefers-reduced-motion: no-preference`. Anything written over it should stay
behind that query — see [Motion](#motion).

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `description` | — | a line under the heading |
| `name` | *required* | the dialog's id |
| `heading` | — | title row, and the dialog's accessible name |
| `dismissible` | `true` | `false` removes the close button and holds Escape off |
| `side` | `right` | `right`, `left`, `bottom` |

The default slot is the body.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md) and
[Overlays](../overlays.md).
