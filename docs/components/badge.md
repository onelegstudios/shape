# Badge

A small piece of state attached to something else. The text is the `label` prop
rather than a slot.

@docs('preview', name: 'badge')

## Tones

`tone` says what the badge means, and resolves a matching icon:

@docs('preview', name: 'badge-tones')

## Variants

`variant` is how loud the badge is. `subtle` is the default, because a badge is
almost always annotating something rather than being the thing you look at:

@docs('preview', name: 'badge-variants')

Both read the same tone variables the [button](button.md) reads, so a badge and
a button given the same tone agree without either knowing about the other.

## Sizes

Four heights — 16px, 20px, 24px and 28px — moved apart by the vertical padding
rather than by the type size. `text-2xs` and `text-xs` share a 1rem line box, so
a scale that changed only the type and the side padding would draw two badges
the same height:

@docs('preview', name: 'badge-sizes')

## Icons

Every state colour resolves a glyph of its own, so a badge stays
readable in greyscale and to anyone who can't separate the hues. `icon` picks a
different one, `:icon="false"` removes it, `icon-trailing` adds one after the
label, and `icon-size` changes how big both of them are:

@docs('preview', name: 'badge-icons')

| `tone` | Icon |
| --- | --- |
| `success` | `shape-success` |
| `danger` | `shape-danger` |
| `warning` | `shape-warning` |
| `info` | `shape-info` |
| `neutral`, `brand`, `accent` | none — none of the three is a state to signal |

Nothing is ever resolved into `icon-trailing`: the state glyph belongs in front
of the label, and a second copy behind it would say the same thing twice. It is
for a drawing of your own — a chevron on a badge that opens something.

## Inline text

A badge is `inline-flex`, so it sits on its line as one atomic box. When its
padding makes it taller than the surrounding text's line-height, the browser
grows that line to fit it — a `base` badge is 24px tall, which is taller than
a 21px `text-sm` line, so the line carrying the badge sits further from its
neighbours than the rest of the paragraph. `inset` cancels the padding above
with an equal negative margin, so the badge keeps its size without growing
the line it's on:

@docs('preview', name: 'badge-inset', layout: 'stack')

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `variant` | `subtle` | `subtle`, `solid`, `outline` |
| `size` | `base` | `xs`, `sm`, `base`, `lg` |
| `icon` | resolved from `tone` | any [icon](icon.md) name, or `false` to omit |
| `icon-trailing` | — | any [icon](icon.md) name, rendered after the label |
| `icon-size` | `xs` | `xs`, `sm`, `base` |
| `inset` | `false` | `true` to cancel the vertical padding with a negative margin, for a badge inline in text |

There is no slot: Blaze memoizes a component only when it has none and is called
self-closing, and a badge — one per row, every row, every page — is the best
candidate for memoization in the library.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['label'])`.

`label` is interpolated and nothing more, so a badge folds even though its text
differs on every row. `tone` branches to resolve the state icon, so it cannot
be `safe`:

```blade
{{-- Folds. --}}
<x-shape::badge label="Paid" tone="success" />

{{-- Folds. The label is safe. --}}
<x-shape::badge :label="$invoice->reference" tone="success" />

{{-- Does not fold. Memoizes instead. --}}
<x-shape::badge :label="$invoice->state" :tone="$invoice->tone" />
```

Memoization pays off on a cache hit, so the last form is cheap while labels
repeat and expensive when they don't. Measured over 200 rows:

| Call site | Cost | Memo entries |
| --- | --- | --- |
| Static tone, any label | 0.26 ms | 0 — it folds |
| Dynamic tone, ~5 repeated labels | 0.77 ms | 5 |
| Dynamic tone, label unique per row | 17.0 ms | 200 |

Keep the tone static wherever you can. Deriving it at the call site costs
nothing and folds every branch:

```blade
@foreach ($invoices as $invoice)
    @if ($invoice->isPaid())
        <x-shape::badge :label="$invoice->reference" tone="success" />
    @else
        <x-shape::badge :label="$invoice->reference" tone="danger" />
    @endif
@endforeach
```

See [Folding](../folding.md).
