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

## Theming

A heading is `font-semibold` and `text-balance`, painted in `--shape-fg`, with
its own line height and letter spacing per size. All of it is written at zero
specificity, so a class at the call site wins outright:

@docs('preview', name: 'heading-override', layout: 'stack')

Shape sets no `font-family` anywhere, which is deliberate: the type is the one
decision an application has already made before it installs a component library.
Headings inherit whatever your page has chosen. A display face for headings only
is a rule of your own:

```css
[data-shape-heading] { font-family: 'Ivar Display', var(--font-serif); }
```

`data-shape-size` is on the element, so a rule can be narrower than every
heading — the three large sizes tighten their tracking and the two small ones
leave it alone, and that is the seam most type changes want:

```css
[data-shape-heading][data-shape-size='2xl'] { letter-spacing: -0.04em; }
```

`level` reaches only the tag, so `h1, h2, h3` is the other way in, and the one
to prefer for a rule that should also catch headings this library did not draw.

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
