# Stat

A number with a label. The value takes the large treatment; the label is
de-emphasized beside it.

@docs('preview', name: 'stat')

## Sizes

The number moves further across the scale than the word under it does — four
type steps against two — because that is the relationship a stat is made of. A
label that grew as fast as its number would flatten the block into two lines of
large text:

@docs('preview', name: 'stat-sizes', layout: 'stack')

It composes with `emphasis` rather than fighting it: `size` picks the pair of
steps, `emphasis` decides which of the two the number gets.

## Tones

`tone` overrides the one [`trend`](#trend) picked, for the metrics where up is the bad
news:

@docs('preview', name: 'stat-tone')

## Description

@docs('preview', name: 'stat-description')

## Trend

`trend` draws a direction and tints it. State `delta` alongside it — a trend
with no delta leaves a bare glyph with nothing beside it:

@docs('preview', name: 'stat-trend')

| `trend` | Glyph | Default tone |
| --- | --- | --- |
| `up` | `shape-trend-up` | `success` |
| `down` | `shape-trend-down` | `danger` |
| `flat` | `shape-trend-flat` | `neutral` |

Three separate drawings rather than one arrow at three angles — never relying on
colour alone only works if the two directions are distinguishable.

## Emphasis

`emphasis="label"` swaps which of the two gets the large treatment, for the
rarer case where the label is the information and the value qualifies it:

@docs('preview', name: 'stat-emphasis')

## Reading order

The label comes first in the DOM whichever way `emphasis` is set, so what is
read aloud is "Invoices sent, 1,204, up 12%" rather than a number with no
subject. The value is set in tabular figures, because a row of stats is a row of
numbers and they should line up.

## Theming

The value and the label paint `--shape-fg` and `--shape-fg-muted`, so a stat
inside a filled [card](card.md) takes that card's foregrounds rather than the
page's greys. The delta is the only coloured part, and it reads
`--shape-tone-ink` under the tone `trend` resolved — which is why a retint of
`success` and `danger` moves every trend in the application at once.

The four parts are paragraphs inside the wrapper, and the attribute bag lands on
the wrapper, so a class reaches the box and the parts are a rule's business:

@docs('preview', name: 'stat-override')

```css
[data-shape-stat-value] { font-size: 1.875rem; letter-spacing: -0.02em; }
[data-shape-stat-label] { text-transform: uppercase; letter-spacing: 0.04em; }
[data-shape-stat-delta] { font-weight: 500; }
```

`data-shape-emphasis` carries which of the two got the large treatment, so a
rule can be written for one arrangement without disturbing the other:

```css
[data-shape-stat][data-shape-emphasis='label'] [data-shape-stat-value] {
    font-size: 1rem;
}
```

The value is set in `tabular-nums` so a row of stats lines up. Keep that in
anything you write over it — figures that do not line up are the reason the
column was set in them.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `label` | — | what it is a number of |
| `description` | — | a line under the value |
| `tone` | from `trend` | `neutral`, `brand`, `accent`, `info`, `success`, `warning`, `danger` |
| `value` | — | the number |
| `delta` | — | the change, as text |
| `emphasis` | `value` | `value`, `label` |
| `trend` | — | `up`, `down`, `flat` |

There is no slot.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['value', 'label', 'description', 'delta'])`.

Slotless and prop-first, so it memoizes at the self-closing call sites a row of
stats is made of — and it folds outright with all four of those props bound
dynamically. `trend` drives a branch and is left unsafe; it is one of three
literals at nearly every call site.

See [Folding](../folding.md) and [Data display](../data.md).
