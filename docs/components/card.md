# Card

A surface. It separates itself from the page with a background shift and a
resting shadow, and draws no border unless you ask for one.

@docs('preview', name: 'card', layout: 'stack')

## Padding

Padding and the gap between children move together — `sm` is a tighter card
*and* tighter stacking. Five steps, `xs` to `xl`, under the same words `size`
takes everywhere else:

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

@docs('preview', name: 'card-spacing')

## Theming

A card is white — `shape-900` in dark mode — at `--radius-shape-lg`, with
`shadow-sm` under it and, if you asked for one, a `shape-200` hairline. Its text
is `--shape-fg`. Every one of those is written at zero specificity, so the card
is the component that yields most completely to a class:

@docs('preview', name: 'card-override')

The shadow is Tailwind's `--shadow-sm` rather than a scale of Shape's own, so
retheming the scale carries the card with everything else that sits on the page
— see [Elevation](../elevation.md).

### It is where a surface is published

A card with a fill of its own is the usual place to declare
`data-shape-surface`, which is what lets the [text](text.md) and
[headings](heading.md) inside it find a foreground that belongs on that fill
rather than the page's grey:

@docs('preview', name: 'text-surface')

The six filled surfaces, the two that read the tone, and how to declare one of
your own are in [the surface contract](../theming.md#the-surface-contract).

### Every card at once

```css
[data-shape-card] { border-radius: 0; box-shadow: none; }
```

`data-shape-padding` carries the arm the card was called with, so a rule can
reach one of them:

```css
[data-shape-card][data-shape-padding='lg'] { padding: 2.5rem; }
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `padding` | `base` | `xs`, `sm`, `base`, `lg`, `xl`, `none` |
| `border` | `false` | adds a hairline border |

`card.header` and `card.footer` take no props. The default slot is the card's
contents.

Cards use `shadow-sm`, the "raised" step — see [Elevation](../elevation.md).

## Folding

Tier A — `@blaze(fold: true)` on all three files. See
[Folding](../folding.md).
