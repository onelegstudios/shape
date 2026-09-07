# Avatar

A person, as a picture or as initials.

@docs('preview', name: 'avatar')

## Pictures

`src` renders an `<img>`. Without one the component renders a `<span>` with
`initials` inside it — the same circle at the same size, painted the same way,
around a different element:

@docs('preview', name: 'avatar-pictures')

Passing both is not a fallback. `src` decides which element renders, and there
is no `onerror` swapping a broken URL for the letters: the initials are what a
call site holding no picture shows, not what a picture that fails to arrive
leaves behind.

`alt` is a real `alt` attribute on this form, and screen-reader-only text on the
initials one — [the initials are not the accessible
name](#the-initials-are-not-the-accessible-name) says what to pass and when to
pass nothing.

Choosing the element rather than describing it is also the one thing about `src`
that costs something at compile time, which [Folding](#folding) sets out.

## Tones

`tone` says what an avatar means, and is the same set every other component
carries:

@docs('preview', name: 'avatar-tones')

## Variants

`variant` is how loud the circle is. `subtle` is the default, because an avatar
is almost always identifying a row rather than being the thing you look at:

@docs('preview', name: 'avatar-variants')

The three arms are the [badge](badge.md)'s, and read the same tone variables, so
an avatar and a badge given the same tone agree without either knowing about the
other. There is no `ghost`: it is the arm that paints nothing until it is
pointed at, and an avatar that paints nothing is two letters loose in a line of
text.

`outline` rings itself in the tone's own `--shape-tone-border-strong` rather
than the neutral border the outline badge and button take. Those two are chrome,
and their edge draws the control rather than what it means; with no fill under
it, an avatar's ring is the whole of the paint.

Every arm paints on the image form as well. Under an opaque photograph the fill
is not seen, under a transparent one it is the ground the face sits on, and in
either case it is what fills the circle while the image is still arriving. The
border rings the picture.

## Sizes

@docs('preview', name: 'avatar-sizes')

## Groups

`avatar.group` overlaps its children in DOM order, and rings each one so the
face underneath reads as a person rather than a smudge:

@docs('preview', name: 'avatar-group')

The last avatar paints on top, and that isn't configurable — choosing the other
order is a z-index, and this library doesn't have one. Reverse the collection at
the call site if the first face should be the front one.

## Initials are stated, never derived

Shape will not turn a name into initials for you. A derivation inside a folded
component runs once, at compile time, and would bake one person's initials into
every avatar the template renders.

Derive them where the data is — an accessor, a computed property — and pass the
result:

```blade
<x-shape::avatar :initials="$person->initials" :alt="$person->name" />
```

## The initials are not the accessible name

Initials are a picture of a name. "A L" read aloud is worse than silence, so
they are hidden and `alt` is carried in text only a screen reader reaches.

An avatar sitting next to a name already on the page should pass no `alt` at
all, and will announce nothing:

```blade
<x-shape::list.item>
    <x-shape::avatar :initials="$person->initials" size="sm" />
    <x-shape::text>{{ $person->name }}</x-shape::text>
</x-shape::list.item>
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `src` | — | an image URL |
| `initials` | — | shown when there is no image |
| `alt` | — | the person's name |
| `size` | `base` | `xs`, `sm`, `base`, `lg` |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `variant` | `subtle` | `subtle`, `solid`, `outline` |

`avatar.group` takes no props.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['initials', 'alt', 'tone'])`.

| Call site | Fold | Memo |
| --- | --- | --- |
| `initials` static or bound dynamically | folds | — |
| `src` bound per row | no | one entry per URL, no hits |

`tone` is interpolated into an attribute and nothing more, the way the
[button](button.md) carries it, so a per-person tone still folds. `variant`
branches to resolve the paint, so it cannot be `safe` — the badge's arrangement
exactly.

`src` decides which element renders — an `<img>` with no source is a broken
image request, and a `<span>` cannot show a photograph — so it branches and
cannot be `safe`. An avatar list built from per-row URLs therefore neither folds
nor usefully memoizes. That is worth knowing rather than worth avoiding: twenty
avatars is twenty components, not two hundred cells.

See [Folding](../folding.md) and [Data display](../data.md).
