# Stat

A number with a label. The value takes the large treatment; the label is
de-emphasized beside it.

@docs('preview', name: 'stat')

## Trend

`trend` draws a direction and tints it. State `delta` alongside it — a trend
with no delta leaves a bare glyph with nothing beside it:

@docs('preview', name: 'stat-trend')

| `trend` | Glyph | Default tone |
| --- | --- | --- |
| `up` | `arrow-trending-up` | `success` |
| `down` | `arrow-trending-down` | `danger` |
| `flat` | `minus` | `neutral` |

Three separate drawings rather than one arrow at three angles — never relying on
colour alone only works if the two directions are distinguishable.

## Color

`color` overrides the tone `trend` picked, for the metrics where up is the bad
news:

@docs('preview', name: 'stat-color')

## Emphasis

`emphasis="label"` swaps which of the two gets the large treatment, for the
rarer case where the label is the information and the value qualifies it:

@docs('preview', name: 'stat-emphasis')

## Description

@docs('preview', name: 'stat-description')

## Reading order

The label comes first in the DOM whichever way `emphasis` is set, so what is
read aloud is "Invoices sent, 1,204, up 12%" rather than a number with no
subject. The value is set in tabular figures, because a row of stats is a row of
numbers and they should line up.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `value` | — | the number |
| `label` | — | what it is a number of |
| `delta` | — | the change, as text |
| `trend` | — | `up`, `down`, `flat` |
| `color` | from `trend` | `neutral`, `accent`, `success`, `warning`, `danger` |
| `description` | — | a line under the value |
| `emphasis` | `value` | `value`, `label` |

There is no slot.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['value', 'label', 'description', 'delta'])`.

Slotless and prop-first, so it memoizes at the self-closing call sites a row of
stats is made of — and it folds outright with all four of those props bound
dynamically. `trend` drives a branch and is left unsafe; it is one of three
literals at nearly every call site.

See [Folding](../folding.md) and [Data display](../data.md).
