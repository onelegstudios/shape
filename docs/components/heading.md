# Heading

@docs('preview', name: 'heading')

## Document hierarchy and visual hierarchy are separate props

`level` picks the element. `size` picks the type. Neither is derived from the
other, because the whole reason for the pair is that they routinely disagree:

```blade
{{-- The page's h1, set small because it isn't the thing you look at first. --}}
<x-shape::heading level="1" size="sm">Billing</x-shape::heading>

{{-- The number you actually look at, which is not a heading at all. --}}
<x-shape::heading level="6" size="2xl">£12,480</x-shape::heading>
```

Most libraries fuse the two into one prop and then need an escape hatch. Keeping
them apart means there is nothing to escape from.

| Prop | Default | Values |
| --- | --- | --- |
| `level` | `2` | `1`–`6` |
| `size` | `base` | `sm`, `base`, `lg`, `xl`, `2xl` |

## Line height and letter spacing are not yours to set

Each `size` ships its own leading and tracking. Large sizes tighten both; small
sizes leave them alone. That pairing is the part of a type scale people get
wrong most often, so the component does not offer a way to get it wrong:

```blade
{{-- text-2xl, leading-8 and tracking-tighter — always together. --}}
<x-shape::heading size="2xl">Total outstanding</x-shape::heading>
```

You can still override it, because the defaults carry zero specificity. You just
have to mean it.

## Folding

Tier A — `@blaze(fold: true, safe: ['level'])`.

`level` is only ever interpolated into the tag name, so it is declared `safe`
and folds even when bound dynamically. `size` selects a `match` arm, so a
dynamic `:size` falls back to the compiled path:

```blade
{{-- Folds. --}}
<x-shape::heading :level="$depth + 1">Section</x-shape::heading>

{{-- Does not fold. Renders correctly, just not inlined. --}}
<x-shape::heading :size="$isLead ? '2xl' : 'base'">Section</x-shape::heading>
```

See [Folding](../folding.md).
