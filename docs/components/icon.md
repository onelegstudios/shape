# Icon

```blade
<x-shape::icon.check />
<x-shape::icon.arrow-right variant="mini" />
```

Icons come from [Heroicons](https://heroicons.com) and are generated into the
package as one component per icon.

## Variants are drawings, not sizes

Each variant is a separate drawing made at its own size, so nothing is ever
scaled up or down:

| Variant | Size | Style |
| --- | --- | --- |
| `micro` | 16px | solid |
| `mini` | 20px | solid |
| `solid` | 24px | solid |
| `outline` | 24px (default) | stroked |

Overriding the size with a utility class works, but prefer the variant that is
drawn at the size you need:

```blade
<x-shape::icon.check variant="mini" />   {{-- drawn at 20px --}}
<x-shape::icon.check class="size-5" />   {{-- 24px drawing squeezed into 20px --}}
```

## Resolving by name

```blade
<x-shape::icon :name="$status === 'done' ? 'check-circle' : 'exclamation-triangle'" />
```

This form resolves the component at runtime and cannot fold. Inside a loop or a
table, use the direct form.

## Accessibility

Icons render `aria-hidden="true"`, on the assumption that they sit beside a
label. When an icon carries meaning on its own, expose it and give it a name:

```blade
<x-shape::icon.check-circle aria-hidden="false" role="img" aria-label="Paid" />
```

## Available icons

`arrow-right`, `arrow-trending-down`, `arrow-trending-up`, `check`,
`check-circle`, `chevron-down`, `chevron-left`, `chevron-right`,
`exclamation-triangle`, `information-circle`, `loading`, `minus`, `plus`,
`trash`, `x-circle`, `x-mark`.

`loading` spins, and is the one icon that isn't from Heroicons.

The set grows with the components that need it rather than by importing
Heroicons wholesale: the state glyphs arrived with the badge and the alert, the
chevrons with the pager, and the two trending arrows with the stat. They are two
separate drawings rather than one arrow rotated, which is the same rule that
gives every variant its own path — a direction that is only a rotation is a
direction that reads as one thing at a glance.
