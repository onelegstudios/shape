# Avatar

A person, as a picture, as initials, or as a glyph standing in for both.

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

The image is cropped to the circle, not squashed into it. `size` sets a width
and a height, and a photograph of a person is usually taller than it is wide, so
the picture takes `object-cover`. Pass `class="object-contain"` to letterbox the
whole frame instead.

`alt` is a real `alt` attribute on this form, and screen-reader-only text on the
initials one — [the initials are not the accessible
name](#the-initials-are-not-the-accessible-name) says what to pass and when to
pass nothing.

Choosing the element rather than describing it is also the one thing about `src`
that costs something at compile time, which [Folding](#folding) sets out.

## Icons

`icon` fills the circle with a glyph instead of letters, for the rows that have
neither a face nor a name — an invitation nobody has accepted, an account that
has been deleted, a service acting on its own:

@docs('preview', name: 'avatar-icons')

It takes an icon name, and there is no default: an avatar with nothing to show
still shows nothing, as it always has. The glyph is a thing you name.

`shape-user` above is the drawing this package ships so that these previews
render for a reader who has generated nothing. It is [not a
slot](icon.md#every-other-icon-is-yours) — nothing in Shape resolves it, and
`shape:icon:replace` will not swap it for the set you are wearing. The person in
your own avatars is one you generate, under the name your set gives it:

```bash
php artisan shape:icon user
```

```blade
<x-shape::avatar icon="user" alt="Unassigned" />
```

The circle is squared as often as not here. A glyph in an avatar usually means
the row is not a person, which is what [`square`](#squares) is for.

### Which one wins

Three props can fill the same circle, and they resolve in one order: `src`,
then `icon`, then `initials`. A prop bound to null drops through to the next, so
a call site holding all three writes the ladder out and gets whichever it has:

```blade
<x-shape::avatar :src="$user->avatar_url" :icon="$user->isBot ? 'cpu-chip' : null" :initials="$user->initials" />
```

The glyph sits above the initials rather than below them for a reason that is
about [folding](#folding) rather than about meaning: asking whether initials are
present would make `initials` a prop this component branches on, and it is
`safe` today. One of the two has to take the branch, and the icon is the one a
call site names deliberately.

None of it is a runtime fallback, for the same reason [`src` is not](#pictures):
a picture that fails to load leaves a broken image, never a glyph.

### Size and style

The glyph's size follows the circle's, so there is nothing to pass:

| `size` | Circle | Glyph |
| --- | --- | --- |
| `xs` | 24px | 16px |
| `sm` | 32px | 16px |
| `base` | 40px | 20px |
| `lg` | 48px | 24px |

About half the circle, except at `xs` — 16px is the smallest drawing an icon set
has, and squeezing it into 12px would throw away the work that made it a
separate drawing. See [Size and style](icon.md#size-and-style).

Every size draws the glyph `solid`, including `lg`, where an icon left to itself
would take the stroked drawing. Four avatars in a row should wear the same
weight, and the filled drawing is the one that matches the initials it stands in
for. A set that draws a single style ignores the word.

The glyph paints in `currentColor`, so it takes the variant's ink exactly as the
initials do, and it is `aria-hidden` — `alt` is carried in the same
screen-reader-only text the initials form uses.

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

## Squares

`square` swaps the circle for the library's own corner radius:

@docs('preview', name: 'avatar-squares')

The circle is the default because a circle is how a person is drawn everywhere
else on the page. Square is for the rows where the avatar is not a person — a
company, a repository, a product, an initial standing for something that was
never a face.

Every size squares to the same `--radius-shape` the [button](button.md) and the
[card](card.md) take, rather than to a radius proportional to `size`. A squared
avatar's job is to sit beside squared things and agree with them, which a
per-size radius would undo.

Nothing else moves. The picture is still cropped, because the box is still
fixed, and a squared avatar in a group is ringed and overlapped like any other.

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
| `icon` | — | an icon name, shown when there is no image |
| `initials` | — | shown when there is no image and no icon |
| `alt` | — | the person's name |
| `size` | `base` | `xs`, `sm`, `base`, `lg` |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `variant` | `subtle` | `subtle`, `solid`, `outline` |
| `square` | `false` | squares the circle to `--radius-shape` |

`avatar.group` takes no props.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['initials', 'alt', 'tone'])`.

| Call site | Fold | Memo |
| --- | --- | --- |
| `initials` static or bound dynamically | folds | — |
| `icon` named statically | folds | — |
| `src` bound per row | no | one entry per URL, no hits |

`tone` is interpolated into an attribute and nothing more, the way the
[button](button.md) carries it, so a per-person tone still folds. `variant`
branches to resolve the paint and `square` to resolve the radius, so neither can
be `safe` — the badge's arrangement exactly.

`src` decides which element renders — an `<img>` with no source is a broken
image request, and a `<span>` cannot show a photograph — so it branches and
cannot be `safe`. `icon` decides what goes inside that element and branches for
the same reason, which is [why it resolves above `initials`](#which-one-wins)
rather than below: there is no arrangement of the two that leaves both safe. An avatar list built from per-row URLs therefore neither folds
nor usefully memoizes. That is worth knowing rather than worth avoiding: twenty
avatars is twenty components, not two hundred cells.

See [Folding](../folding.md) and [Data display](../data.md).
