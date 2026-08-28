# Badge

@docs('preview', name: 'badge')

## The label is a prop, not a slot

This is the one place in Shape where the API is shaped by the compiler rather
than by taste.

Blaze memoizes a component only when it has **no slots** and is called
self-closing. Badges are the highest-volume component in any real application —
one per row, every row, every page — which makes them the single best candidate
for memoization in the library. A `{{ $slot }}` here would trade that away for a
composition nobody actually wants inside a badge.

So: `label`, not children.

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text |
| `color` | `neutral` | `neutral`, `accent`, `danger`, `success`, `warning` |
| `variant` | `subtle` | `subtle`, `solid`, `outline` |
| `size` | `base` | `sm`, `base` |
| `icon` | resolved from `color` | any icon name, or `false` to omit |
| `icon-variant` | `micro` | `micro`, `mini`, `solid`, `outline` |

## Colour is never the only signal

Every state resolves a glyph of its own, so a badge stays readable in greyscale
and to anyone who can't separate the hues:

| `color` | Icon |
| --- | --- |
| `success` | `check-circle` |
| `danger` | `x-circle` |
| `warning` | `exclamation-triangle` |
| `accent` | `information-circle` |
| `neutral` | none — a neutral badge has no state to signal |

Opting out is possible. Forgetting isn't:

```blade
<x-shape::badge label="Paid" color="success" :icon="false" />
```

## Hierarchy and semantics, same as the button

`variant` is how loud the badge is; `color` is what it means. `subtle` is the
default because a badge is almost always annotating something else rather than
being the thing you look at.

```blade
<x-shape::badge label="Overdue" color="danger" />              {{-- quiet --}}
<x-shape::badge label="Overdue" color="danger" variant="solid" /> {{-- loud --}}
```

Both read the same `--shape-tone-*` variables the button reads, so a badge and a
button given the same colour agree without either knowing about the other.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['label'])`.

`label` is `safe` — it is interpolated and nothing more — so a badge folds even
though its text differs on every row. `color` is not, and cannot be: the badge
branches on it to resolve the state icon.

```blade
{{-- Folds. --}}
<x-shape::badge label="Paid" color="success" />

{{-- Folds. The label is safe. --}}
<x-shape::badge :label="$invoice->reference" color="success" />

{{-- Does not fold. Memoizes instead. --}}
<x-shape::badge :label="$invoice->state" :color="$invoice->tone" />
```

The memo path is cheap while labels repeat, and expensive when they don't,
because memoization only pays off on a cache hit. Measured over 200 rows:

| Call site | Cost | Memo entries |
| --- | --- | --- |
| Static colour, any label | 0.26 ms | 0 — it folds |
| Dynamic colour, ~5 repeated labels | 0.77 ms | 5 |
| Dynamic colour, label unique per row | 17.0 ms | 200 |

The last row is the one to avoid, and it is easy to: **keep the colour static
wherever you can.** Deriving it at the call site costs nothing —

```blade
@foreach ($invoices as $invoice)
    @if ($invoice->isPaid())
        <x-shape::badge :label="$invoice->reference" color="success" />
    @else
        <x-shape::badge :label="$invoice->reference" color="danger" />
    @endif
@endforeach
```

— and folds every branch. A `:color` bound to an accessor is fine when the
labels are a small fixed vocabulary, which is the usual case for a status badge.

See [Folding](../folding.md).
