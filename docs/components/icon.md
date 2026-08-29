# Icon

One component per icon, from [Heroicons](https://heroicons.com). Call the one
you want by name.

@docs('preview', name: 'icon')

## Variants

Each variant is a separate drawing made at its own size, so nothing is ever
scaled:

@docs('preview', name: 'icon-variants')

| Variant | Size | Style |
| --- | --- | --- |
| `micro` | 16px | solid |
| `mini` | 20px | solid |
| `solid` | 24px | solid |
| `outline` | 24px — the default | stroked |

Overriding the size with a utility works, but prefer the variant drawn at the
size you need:

```blade
<x-shape::icon.check variant="mini" />   {{-- drawn at 20px --}}
<x-shape::icon.check class="size-5" />   {{-- 24px drawing squeezed into 20px --}}
```

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

Add your own from any directory of SVGs:

```bash
php artisan shape:icon bell --from=resources/icons
```

See [Tooling](../tooling.md#shapeicon).

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `variant` | `outline` | `micro`, `mini`, `solid`, `outline` |

`<x-shape::icon>` — the by-name form — takes `name` as well.

## Folding

Tier B — `@blaze(fold: true, memo: true)` on every named icon.

`<x-shape::icon name="…">` is `@blaze(memo: false)`: it resolves a different
component per call, which is the opposite of what memoization is for. See
[Folding](../folding.md).
