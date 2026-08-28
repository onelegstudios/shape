# Avatar

```blade
<x-shape::avatar src="/avatars/ada.jpg" alt="Ada Lovelace" />

<x-shape::avatar initials="AL" alt="Ada Lovelace" />
```

| Prop | Default | Values |
| --- | --- | --- |
| `src` | — | an image URL |
| `initials` | — | shown when there is no image |
| `alt` | — | the person's name |
| `size` | `base` | `xs`, `sm`, `base`, `lg` |

## Initials are stated, never derived

Shape will not turn a name into initials for you, and the reason is the same one
that keeps translations out of folded components: a derivation inside a folded
component runs once, at compile time, and bakes one person's initials into every
avatar the template renders.

Derive them where the data is — an accessor on the model, a computed property —
and pass the result.

## The initials are not the accessible name

Initials are a picture of a name. "A L" read aloud is worse than silence, so
they are hidden and the name itself is carried in text only a screen reader
reaches.

An avatar sitting next to a name already on the page should pass no `alt` at
all, and will announce nothing:

```blade
<x-shape::list.item>
    <x-shape::avatar :initials="$person->initials" size="sm" />
    <x-shape::text>{{ $person->name }}</x-shape::text>
</x-shape::list.item>
```

## Groups stack in DOM order

```blade
<x-shape::avatar.group>
    <x-shape::avatar initials="AL" size="sm" />
    <x-shape::avatar initials="GH" size="sm" />
    <x-shape::avatar initials="KJ" size="sm" />
</x-shape::avatar.group>
```

A negative gap to overlap them and a ring on each so the one underneath reads as
a person rather than a smudge. Two utilities, no stylesheet rule.

The last avatar paints on top, and that is not configurable: choosing the other
order is a z-index, and this library doesn't have one. Reverse the collection at
the call site if the first face should be the front one.

## Which call sites pay

| Call site | Fold | Memo |
| --- | --- | --- |
| `initials` static or bound dynamically | folds | — |
| `src` bound per row | no | one entry per URL, no hits |

`src` decides which element renders — an `<img>` with no source is a broken
image request, and a `<span>` cannot show a photograph — so it branches and
cannot be declared `safe`. No attribute-bag trick avoids that; dropping a null
attribute with `merge()`, the way the progress bar drops its `aria-label`, only
helps when it is the same element either way.

So an avatar list built from per-row URLs neither folds nor usefully memoizes.
That is the badge's expensive call site in different clothes, and it is worth
knowing rather than worth avoiding — twenty avatars is twenty components, not
two hundred cells.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['initials', 'alt'])`.

See [Folding](../folding.md) and [Data display](../data.md).
