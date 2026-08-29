# Popover

Anchored content in the top layer. The [dropdown](dropdown.md) without the menu
semantics — reach for that one when the content is a list of things to do, and
this when it is something to read or a small form.

@docs('preview', name: 'popover')

## Placement

@docs('preview', name: 'popover-placement')

It flips and clamps the same way the dropdown does, and for the same reason:
`shape.js` places every anchored overlay from one path.

## Padding

`tight` is what the dropdown uses:

@docs('preview', name: 'popover-padding')

Padding is a prop rather than a class you override. Two package defaults for one
property both carry zero specificity, so which of them wins would be decided by
Tailwind's ordering of the utilities rather than by which component meant it. A
`match` emits one class and the question never comes up.

The rule generalises: `[:where(&)]:` makes *your* classes win over the package's,
not one of the package's classes win over another.

## The trigger

`popovertarget` opens and closes it, with `aria-haspopup="dialog"`,
`aria-controls` and an `aria-expanded` that `shape.js` keeps honest from the
popover's own `toggle` event — state and announcement from one source, so they
cannot drift.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the popover's id |
| `placement` | `bottom-start` | `bottom-start`, `bottom-end`, `bottom`, `top-start`, `top-end`, `top` |
| `padding` | `base` | `base`, `tight` |
| `role` | — | set it if the content warrants one |

`popover.trigger` takes `for` and `haspopup` (`dialog` by default) and passes
everything else to a [button](button.md). The default slot is the content.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md) and
[Overlays](../overlays.md).
