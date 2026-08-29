# Icon

One component per icon, from [Heroicons](https://heroicons.com). Call the one
you want by name.

@docs('preview', name: 'icon')

## Size and style

An icon has two axes, and they are the two words the rest of this library uses,
on the same scale [text](text.md), [button](button.md) and [avatar](avatar.md)
use: `size` is how big it is, `variant` is which style it is drawn in.

@docs('preview', name: 'icon-sizes')

| Size | Drawn at |
| --- | --- |
| `xs` | 16px |
| `sm` | 20px |
| `base` | 24px — the default |

@docs('preview', name: 'icon-styles')

| Style | |
| --- | --- |
| `outline` | stroked |
| `solid` | filled |

Most call sites name only a size. The style follows from it: `xs` and `sm` are
drawn solid, because a 1.5px stroke does not read at 16px — which is also why
Heroicons draws no outline below 24px. Name a style to override that.

```blade
<x-shape::icon.check size="sm" />                     {{-- 20px, solid --}}
<x-shape::icon.check variant="outline" size="sm" />   {{-- 20px, stroked --}}
```

Each cell that a set draws is a separate drawing at its own size, so nothing is
scaled. Where a set draws no glyph of its own — outline below 24px — the largest
one it has is used and sized down; never sized up. Overriding the size with a
utility works, but prefer the size the drawing was made at:

```blade
<x-shape::icon.check size="sm" />      {{-- drawn at 20px --}}
<x-shape::icon.check class="size-5" />   {{-- 24px drawing squeezed into 20px --}}
```

Sizes and styles are whatever the icon set declares — see
[Tooling](../tooling.md#shapeicon). A set with a single style, like Lucide,
still takes `size`, and ignores `variant` rather than rendering it onto the
`<svg>`.

## Colour

Icons paint in `currentColor`, so they take the colour of whatever they sit in —
or a utility class of your own:

@docs('preview', name: 'icon-color')

Inside a [button](button.md), [badge](badge.md) or [alert](alert.md) that
happens on its own, and the icon picks up the tone.

## Resolving by name

When the name is not known until runtime, `<x-shape::icon>` takes it as a prop:

```blade
<x-shape::icon :name="$status === 'done' ? 'check-circle' : 'exclamation-triangle'" />
```

This form resolves the component at runtime and cannot fold or memoize. Inside a
loop or a table, use the direct form.

## Accessibility

Icons render `aria-hidden="true"`, on the assumption that they sit beside a
label. When an icon carries meaning on its own, expose it and give it a name:

```blade
<x-shape::icon.check-circle aria-hidden="false" role="img" aria-label="Paid" />
```

## Available icons

@docs('preview', name: 'icon-gallery')

`arrow-right`, `arrow-trending-down`, `arrow-trending-up`, `check`,
`check-circle`, `chevron-down`, `chevron-left`, `chevron-right`,
`exclamation-triangle`, `information-circle`, `loading`, `minus`, `plus`,
`trash`, `x-circle`, `x-mark`.

`loading` spins, and is the one icon that isn't from Heroicons.

Add your own from any directory of SVGs, in any set's layout:

```bash
php artisan shape:icon bell --from=resources/icons
php artisan shape:icon bell --set=lucide --from=vendor/lucide/icons
```

See [Tooling](../tooling.md#shapeicon).

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base` |
| `variant` | chosen by `size` | `outline`, `solid` |

`<x-shape::icon>` — the by-name form — takes `name` as well.

## Folding

Tier B — `@blaze(fold: true, memo: true)` on every named icon.

`<x-shape::icon name="…">` is `@blaze(memo: false)`: it resolves a different
component per call, which is the opposite of what memoization is for. See
[Folding](../folding.md).
