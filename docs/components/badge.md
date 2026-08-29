# Badge

A small piece of state attached to something else. The text is the `label` prop
rather than a slot.

@docs('preview', name: 'badge')

## Colors

`color` says what the badge means, and resolves a matching icon:

@docs('preview', name: 'badge-colors')

## Variants

`variant` is how loud the badge is. `subtle` is the default, because a badge is
almost always annotating something rather than being the thing you look at:

@docs('preview', name: 'badge-variants')

Both read the same tone variables the [button](button.md) reads, so a badge and
a button given the same colour agree without either knowing about the other.

## Sizes

@docs('preview', name: 'badge-sizes')

## Icons

Every colour but `neutral` resolves a glyph of its own, so a badge stays
readable in greyscale and to anyone who can't separate the hues. `icon` picks a
different one, `:icon="false"` removes it, and `icon-variant` changes the
drawing:

@docs('preview', name: 'badge-icons')

| `color` | Icon |
| --- | --- |
| `success` | `check-circle` |
| `danger` | `x-circle` |
| `warning` | `exclamation-triangle` |
| `accent` | `information-circle` |
| `neutral` | none — a neutral badge has no state to signal |

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text |
| `color` | `neutral` | `neutral`, `accent`, `danger`, `success`, `warning` |
| `variant` | `subtle` | `subtle`, `solid`, `outline` |
| `size` | `base` | `sm`, `base` |
| `icon` | resolved from `color` | any icon name, or `false` to omit |
| `icon-variant` | `micro` | `micro`, `mini`, `solid`, `outline` |

There is no slot: Blaze memoizes a component only when it has none and is called
self-closing, and a badge — one per row, every row, every page — is the best
candidate for memoization in the library.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['label'])`.

`label` is interpolated and nothing more, so a badge folds even though its text
differs on every row. `color` branches to resolve the state icon, so it cannot
be `safe`:

```blade
{{-- Folds. --}}
<x-shape::badge label="Paid" color="success" />

{{-- Folds. The label is safe. --}}
<x-shape::badge :label="$invoice->reference" color="success" />

{{-- Does not fold. Memoizes instead. --}}
<x-shape::badge :label="$invoice->state" :color="$invoice->tone" />
```

Memoization pays off on a cache hit, so the last form is cheap while labels
repeat and expensive when they don't. Measured over 200 rows:

| Call site | Cost | Memo entries |
| --- | --- | --- |
| Static colour, any label | 0.26 ms | 0 — it folds |
| Dynamic colour, ~5 repeated labels | 0.77 ms | 5 |
| Dynamic colour, label unique per row | 17.0 ms | 200 |

Keep the colour static wherever you can. Deriving it at the call site costs
nothing and folds every branch:

```blade
@foreach ($invoices as $invoice)
    @if ($invoice->isPaid())
        <x-shape::badge :label="$invoice->reference" color="success" />
    @else
        <x-shape::badge :label="$invoice->reference" color="danger" />
    @endif
@endforeach
```

See [Folding](../folding.md).
