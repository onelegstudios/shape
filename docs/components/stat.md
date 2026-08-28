# Stat

```blade
<x-shape::stat label="Invoices sent" value="1,204" delta="12%" trend="up" />
```

## The value leads and the label recedes

"Labels are a last resort." The number takes the large treatment; the label is
rendered de-emphasized beside it. The label still comes *first in the DOM*, so
what is read aloud is "Invoices sent, 1,204, up 12%" rather than a number with
no subject.

Emphasizing the label is the opt-in, for the rarer case where the label is the
information and the value is the qualifier:

```blade
<x-shape::stat label="Team" value="Current plan" emphasis="label" />
```

| Prop | Default | Values |
| --- | --- | --- |
| `value` | — | the number |
| `label` | — | what it is a number of |
| `delta` | — | the change, as text |
| `trend` | — | `up`, `down`, `flat` |
| `color` | from `trend` | `success`, `danger`, `warning`, `accent`, `neutral` |
| `description` | — | a line under the value |
| `emphasis` | `value` | `value`, `label` |

The value is set in tabular figures, because a row of stats is a row of numbers
and they should line up.

## A direction is drawn, not only tinted

| `trend` | Glyph | Default tone |
| --- | --- | --- |
| `up` | `arrow-trending-up` | `success` |
| `down` | `arrow-trending-down` | `danger` |
| `flat` | `minus` | `neutral` |

Three different drawings, not one arrow at three angles — the same rule the
[icon](icon.md) component holds everywhere. Never relying on colour alone only
works if the two directions are actually distinguishable.

`color` overrides the tone for the metrics where up is the bad news:

```blade
<x-shape::stat label="Churn" value="4.1%" delta="0.6pp" trend="up" color="danger" />
```

State `trend` and `delta` together. A trend with no delta leaves a bare glyph
with nothing beside it.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['value', 'label', 'description', 'delta'])`.

Slotless and prop-first, so it memoizes at the self-closing call sites a row of
stats is made of — and it folds outright, with all four of those props bound
dynamically.

That last part is only true because of one detail. The delta row is always
rendered and collapsed with `empty:hidden` rather than written behind an
`@if ($delta)`. A prop that drives a condition cannot be declared `safe`, and a
delta is dynamic by definition — so the `@if` would have taken every stat with a
computed delta off the fold path, which is all of them. `trend` does drive a
branch, and is left unsafe: it is one of three literals at nearly every call
site.

See [Folding](../folding.md) and [Data display](../data.md).
