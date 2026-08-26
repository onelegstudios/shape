# Text

```blade
<x-shape::text>We'll only use this for receipts.</x-shape::text>
```

## Muted is a relationship, not a colour

`variant="muted"` reads `--shape-fg-muted` from whatever surface the text is
sitting on, rather than a fixed grey. On a white card that resolves to a
neutral; on a tinted or accent surface it resolves to the same hue with its
saturation and lightness dialled down.

This is what makes "don't use grey text on coloured backgrounds" structurally
impossible rather than merely documented. There is no grey to reach for.

```blade
<x-shape::card data-shape-surface="accent">
    <x-shape::heading size="lg">Upgrade</x-shape::heading>
    {{-- Muted, and still legible, because it isn't grey. --}}
    <x-shape::text variant="muted">Cancel any time.</x-shape::text>
</x-shape::card>
```

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg` |
| `variant` | `base` | `base`, `muted`, `strong` |
| `as` | `p` | any tag name — `p`, `span`, `div` |

`strong` emphasises with weight, not colour. Reaching for a colour to say
"important" spends the one signal you have left for saying "wrong".

## Folding

Tier A — `@blaze(fold: true, safe: ['as'])`.

`as` reaches only the tag name, so it folds when bound dynamically. `size` and
`variant` each select a `match` arm and have to be static to fold.

See [Folding](../folding.md).
