# Heading

A heading. `level` picks the element, `size` picks the type, and neither is
derived from the other.

@docs('preview', name: 'heading')

## Sizes

@docs('preview', name: 'heading-sizes', layout: 'stack')

Each size ships its own line height and letter spacing. Large sizes tighten
both; small sizes leave them alone. You can still override either, because the
defaults carry zero specificity — you just have to mean it.

## Levels

`level` renders `<h1>` through `<h6>` and changes nothing you can see. Keeping
it apart from `size` is what lets the two disagree, which they routinely do:

@docs('preview', name: 'heading-levels', layout: 'stack')

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `level` | `2` | `1`–`6` |
| `size` | `base` | `sm`, `base`, `lg`, `xl`, `2xl` |

The default slot is the heading text.

## Folding

Tier A — `@blaze(fold: true, safe: ['level'])`.

`level` is only ever interpolated into the tag name, so `:level="$depth + 1"`
still folds. `size` selects a `match` arm, so a dynamic `:size` renders
correctly but drops to the compiled path. See [Folding](../folding.md).
