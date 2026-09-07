# Alert

A message that stays in the flow of the page it belongs to. Other libraries call
this a callout.

@docs('preview', name: 'alert', layout: 'stack')

## Tones

`tone` says what the alert means, and resolves a matching icon. With no tone the
alert is neutral and draws no glyph:

@docs('preview', name: 'alert-tones', layout: 'stack')

## Variants

`variant` is how loud the alert is; `tone` is what it means. `subtle` is the
default, because an alert is usually not the loudest thing on its page:

@docs('preview', name: 'alert-variants', layout: 'stack')

`outline` draws an edge around the outside and leaves the text as the page's own
ink, so a long alert reads as ordinary body copy inside a box. The edge is the
neutral border every outlined thing in the library takes — the same one the
outline [button](button.md#variants) and [badge](badge.md) take — so the glyph is
what carries the tone, and it is what stops the message from losing its meaning
along with its colour. [`toned`](#toning-the-text) puts the colour back in the
text where you want it, and [`border`](#borders) puts it in the edge. `solid` fills with the tone and is worth spending sparingly: a saturated
block that size competes with everything around it.

`ghost` is `outline` with the border dropped, and the quietest of the four: no
fill, no edge, just the glyph and the page's own ink in the flow of the content.
Reach for it where the alert is already inside something boxed — a
[card](card.md), a panel, a form section — and a second border would only draw a
box inside a box. It keeps the padding the others have, so switching a variant
never shifts the text.

It is also the one variant that paints on hover, and what it paints is `subtle`
entire: the tint it would have had at rest, and the tone's ink that belongs on
that tint. An alert is not a control and the hover promises no click; what it
does is show the bounds an unpainted block has no other way of drawing, which is
worth having when the block carries a [dismiss](#dismissing) button. The ghost
[button](button.md#variants) hovers to the same variable, so a ghost control
inside a ghost alert agrees with it.

Inside a `solid` alert the muted foreground is not dialled back, because there is
nowhere for it to go. White on the 700 fills starts at 4.9:1 for `success`, so
any tint that reads as recessed lands under AA. Hierarchy comes from the
heading's size and weight instead.

## Toning the text

`outline` and `ghost` paint no fill, so an alert in either sits directly on the
page and its text reads both ways. `toned` is which way:

@docs('preview', name: 'alert-toned', layout: 'stack')

Both default to the page's ink. Nothing about the message is lost with it — the
glyph still says what the alert means, and on `outline` with a toned
[`border`](#borders) so does the edge — and what is gained is a block of body copy
that reads as body copy. A full paragraph of red is the loudest thing on a page
for no reason.

A ghost alert takes the tone back under the pointer, because that is when it
paints the tint it would have had as a `subtle` alert, and grey text on a coloured
wash is the thing this library will not do. It is published as
`data-shape-surface-hover`, for as long as the pointer is there — a `hover:text-`
on the alert could not reach the heading and the body inside it, both of which
paint their own foreground from the pair. The [dismiss](#dismissing) control
follows the same attribute.

`subtle` and `solid` ignore the prop. Both paint a background, and what is
readable on one is not a call site's to choose — grey text on a pink wash and dark
ink on a saturated fill are the two failures the
[surface contract](../theming.md#the-surface-contract) exists to make impossible.

An untoned alert publishes no `data-shape-surface` at all, rather than publishing
the page's own colours under a name. The difference shows when it is not on the
page: an outline alert inside a `solid` card should read in the card's foreground,
and inheriting is what does that.

## Borders

`border` draws the alert's edge in its own tone. It is off by default, because on
three of the four variants the fill is already the boundary; turn it on where the
alert has to hold its own against a busy page, or sit beside a [card](card.md)
that is drawn with one:

@docs('preview', name: 'alert-border', layout: 'stack')

`subtle` and `outline` draw the same edge, from
[`--shape-tone-border-strong`](../theming.md#the-tone-variables) — the tone's
answer to the neutral `--shape-tone-border` that an outlined thing takes by
default. It sits a step further along the ramp than the tint pair does, which is
what makes it read as an edge someone chose rather than a definition line. Both
arms read the one variable because the edge is doing the same job in each: on
`subtle` it bounds the wash, on `outline` it is the whole of the paint. `outline`
is the one variant the prop adds no border to — it has one already — so there it
only decides whether that border carries the tone or the grey.

`solid` cannot use that step: a pale edge on a saturated fill reads as a
highlight, so it takes the step *past* the fill instead — darker in light mode,
brighter in dark, which is the same move the tone's own hover makes and the reason
it is not a fixed darkening.

`ghost` shows its edge only under the pointer, arriving with the tint it already
paints there, so the alert stays unpainted at rest and the hover draws the whole
block at once. The border is reserved as a transparent one, so nothing shifts by a
pixel when it lands.

Both colours are steps of `--shape-tone`, not a palette of their own, so a border
follows a [retheme](../theming.md) with everything else. To make every toned edge
in the library heavier or lighter at once, move `--shape-tone-border-strong`.

## Elevation

`shadow` lifts the alert off the page. It is the one prop here that says nothing
about the tone, and like [`border`](#borders) it is off by default — an alert is
part of the content it is about, and a block in the flow of that content has
nothing to lift away from:

@docs('preview', name: 'alert-shadow', layout: 'stack')

There is one step and it is `shadow-sm`, the raised one that buttons, cards and
inputs already take for sitting on the page. Shape defines no elevation scale of
its own, so this is Tailwind's `--shadow-sm` and a
[retheme](../elevation.md#overriding) of it carries the alert with everything
else. It is applied at zero specificity, so a call site that wants another step
of the scale asks for it directly:

```blade
<x-shape::alert tone="info" shadow class="shadow-lg">Deploy finished.</x-shape::alert>
```

Reach for it where the alert has to read as laid *on* the page rather than set
into it — floating over a dense table, or sitting beside a [card](card.md) that
has a resting shadow of its own and would otherwise look like the only raised
thing there. A `subtle` alert in the flow of a form does not need one.

`ghost` shows it only under the pointer, with the fill and the
[border](#borders), because a shadow is a cast from a surface and that variant
has none until the hover paints one. Drawn at rest it would ring a transparent
block with an edge nothing in it drew. Nothing has to be reserved for it the way
the border is: a shadow paints outside the box and moves no text when it lands.

The hover names the properties it transitions rather than taking
`transition-colors`, so the cast fades in with the fill instead of appearing at
once — `box-shadow` is not a colour, and Tailwind's shorthand does not carry it.

## Heading and body

`heading` is a title above the body; the default slot is the body. Either can
stand alone:

@docs('preview', name: 'alert-heading', layout: 'stack')

## Icons

Every state colour resolves a glyph of its own, so an alert stays readable in
greyscale and to anyone who can't separate the hues. `icon` picks a different
one, `:icon="false"` removes it, and `icon-size` changes how big it is:

`brand` and `accent` are the exceptions, and deliberately: both are emphasis
rather than a state, so they draw nothing. An informational message wants
[`info`](../theming.md#the-state-colours-are-not-yours-to-rebrand), which is blue
whatever the brand becomes.

@docs('preview', name: 'alert-icons', layout: 'stack')

## Dismissing

`dismissible` adds a close button. `shape.js` removes the nearest alert when it
is clicked:

@docs('preview', name: 'alert-dismissible', layout: 'stack')

Dismissal is not remembered. If an alert should stay dismissed, that is state
your application owns.

The close button is a [ghost](button.md#variants), and a ghost paints with
`--shape-tone-ink` so that a ghost `danger` button is red on a page that is not.
To do that it declares a `data-shape-tone` of its own — which means that left
alone it resolves the *neutral* ink and the *neutral* tint inside an alert of any
tone at all. On `solid` that is dark grey on a saturated fill; on the tints it is
a grey × beside a coloured glyph, hovering to a grey square on a coloured block.

It reads the surface's foreground instead, and hovers to a wash of the same, so
the control belongs to whatever the variant painted:

@docs('preview', name: 'alert-dismissible-solid', layout: 'stack')

An [untoned](#toning-the-text) alert publishes no surface, so it misses the rule
and the × goes on resolving its own neutral ink — which is the right answer on the
page background, and grey beside a black heading rather than beside a coloured
one. Inside something that does publish a surface, that ancestor still matches and
the control follows it. So does `data-shape-surface-hover`, which is what carries
the × into the tint with the rest of a ghost alert instead of leaving it the one
grey thing on a block that has just gone coloured.

This is the one component that reads a tone rather than a surface, so it is the
one place the contract is corrected by a rule instead of being followed. The rule
is scoped to the dismiss control rather than to ghost buttons generally: a ghost
`danger` button you put inside a `brand` alert is meant to be red and stays red,
where a close button was never given a tone to keep. It lives in a layer after
Tailwind's, because `text-[var(--shape-tone-ink)]` is a utility and nothing in
`@layer components` outranks a utility — see
[Theming](../theming.md#the-surface-contract).

A [toast](toast.md) is the case the rule deliberately misses. It publishes a tone
but no surface, because its fill stays white so it reads over whatever it is
floating above, so its × goes on resolving neutral grey — which is the right
answer on white.

Focus is the same failure in a property colour does not reach. `--shape-ring` is
the brand, so focusing the close button on a solid `brand` alert would draw
brand-600 on brand-700 and there would be no ring to see; on `solid` the ring
takes the surface's foreground too. On the tints it stays the brand ring, which
is off-hue but never invisible.

## Muted text inside an alert

An alert that carries a tone publishes its own foreground, so a nested muted
paragraph reads a dialled-back version of the tone rather than grey on pink:

@docs('preview', name: 'alert-surface', layout: 'stack')

Nothing was passed down — see [Theming](../theming.md#the-surface-contract).

## Alert or toast

If a message is still true after someone has read it, it is an alert. A
[toast](toast.md) is an event: it happened, it is announced, it goes away.

An alert carries no `role="alert"` and no `aria-live`, because this markup was on
the page when it loaded and announcing it repeats what a screen reader is about
to read anyway. Announcements belong to the toaster, where content arrives after
the fact.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `tone` | `neutral` | `info`, `success`, `warning`, `danger`, `brand`, `accent` |
| `variant` | `subtle` | `subtle`, `outline`, `solid`, `ghost` |
| `toned` | `false` | paints the text in the tone, on `outline` and `ghost`; ignored on `subtle` and `solid`, where the fill decides |
| `border` | `false` | draws the edge in the tone; on `outline` recolours the border it already has, on `ghost` shows it on hover only |
| `shadow` | `false` | lifts the alert with `shadow-sm`; on `ghost` shows it on hover only |
| `heading` | — | a title above the body |
| `icon` | resolved from `tone` | any [icon](icon.md) name, or `false` for none |
| `icon-size` | `sm` | `xs`, `sm`, `base` |
| `dismissible` | `false` | adds a close button |

The default slot is the body.

## Folding

Tier A — `@blaze(fold: true, safe: ['heading'])`.

`heading` is interpolated and nothing more, so an alert whose title comes from a
variable still folds. `tone` branches to resolve its glyph, and `variant`,
`toned`, `border` and `shadow` branch to resolve the paint, so `:tone="$tone"`
drops to the compiled path — the same prop is safe on the [button](button.md),
which only ever interpolates it. See [Folding](../folding.md).

`shadow` branches because `ghost` waits for the hover with it, which is a
different class and not a different value of one. Written literally —
`<x-shape::alert shadow>` — it is read at compile time like every other prop
here and costs nothing; `:shadow="$isFloating"` is what drops the alert to the
compiled path.
