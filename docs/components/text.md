# Text

A paragraph. `size` sets the scale, `variant` sets the emphasis.

@docs('preview', name: 'text')

## Sizes

@docs('preview', name: 'text-sizes', layout: 'stack')

## Variants

`strong` emphasises with weight, `muted` de-emphasises with colour.

@docs('preview', name: 'text-variants', layout: 'stack')

## Muted follows the surface

`muted` reads the foreground the surface it sits on publishes, not a fixed grey.
On a white card that resolves to a neutral; on a coloured surface it resolves to
the same hue with its saturation and lightness dialled down:

@docs('preview', name: 'text-surface')

Nothing was passed down to make that work — the card sets
`data-shape-surface`, and the text reads it. [Theming](../theming.md#the-surface-contract)
covers the contract and how to declare a surface of your own.

## Other tags

`as` renders any tag with the same styling, for text that is a `span` inside a
sentence or a `div` around something else:

```blade
<x-shape::text as="span" size="sm" variant="muted">Draft</x-shape::text>
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg` |
| `variant` | `base` | `base`, `muted`, `strong` |
| `as` | `p` | any tag name |

The default slot is the text.

## Folding

Tier A — `@blaze(fold: true, safe: ['as'])`.

`as` reaches only the tag name, so it folds when bound dynamically. `size` and
`variant` each select a `match` arm and have to be static to fold. See
[Folding](../folding.md).
