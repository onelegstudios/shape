# Card

A surface. It separates itself from the page with a background shift and a
resting shadow, and draws no border unless you ask for one.

@docs('preview', name: 'card', layout: 'stack')

## Padding

Padding and the gap between children move together — `sm` is a tighter card
*and* tighter stacking:

@docs('preview', name: 'card-padding', layout: 'stack')

`padding="none"` keeps the gap and drops the inset, for a card whose contents
reach the edge:

@docs('preview', name: 'card-padding-none', layout: 'stack')

## Border

`border` adds a hairline, for a card sitting on a surface too close to its own
to read against:

@docs('preview', name: 'card-border', layout: 'stack')

## Header and footer

`card.header` stacks its children tightly; `card.footer` lays them out in a row.
Neither draws a rule — compose a [separator](separator.md) if you want one:

@docs('preview', name: 'card-regions', layout: 'stack')

Both are components rather than named slots, because deciding whether a slot has
content is a runtime question and asking it would take the card off the fold
path.

## Spacing

Nothing inside a card sets its own outer margin; the gap belongs to the card.
Change it with a utility on the card itself:

```blade
<x-shape::card class="gap-8">…</x-shape::card>
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `padding` | `base` | `none`, `sm`, `base`, `lg` |
| `border` | `false` | adds a hairline border |

`card.header` and `card.footer` take no props. The default slot is the card's
contents.

Cards use `shadow-sm`, the "raised" step — see [Elevation](../elevation.md).

## Folding

Tier A — `@blaze(fold: true)` on all three files. See
[Folding](../folding.md).
