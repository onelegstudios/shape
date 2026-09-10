# Alert

A message that stays in the flow of the page it belongs to. Other libraries call
this a callout.

@docs('preview', name: 'alert', layout: 'stack')

## Sizes

`size` moves the inset the block is drawn with and the type the message is set
in, together — an alert that grew its padding and left its sentence at 14px would
read as a small alert with a wide margin:

@docs('preview', name: 'alert-sizes', layout: 'stack')

`sm` and `base` share a type step and differ in the room around them, which is
the [button](button.md#sizes)'s arrangement and for the button's reason: the two
most common alerts on a page should not set their text differently.

The glyph and the dismiss control follow unless they are named, so a call site
says the word once rather than three times.

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

## Icons

Every state colour resolves a glyph of its own, so an alert stays readable in
greyscale and to anyone who can't separate the hues. `icon` picks a different
one, `:icon="false"` removes it, `icon-size` changes how big it is, and
`icon-variant` changes which style it is drawn in:

`brand` and `accent` are the exceptions, and deliberately: both are emphasis
rather than a state, so they draw nothing. An informational message wants
[`info`](../theming.md#the-state-colours-are-not-yours-to-rebrand), which is blue
whatever the brand becomes.

@docs('preview', name: 'alert-icons', layout: 'stack')

An icon picks its own style from its size — [solid at `xs` and `sm`, outline at
`base`](icon.md#size-and-style) — so an alert leaves `icon-variant` unset by
default and takes whichever drawing the set prefers at the size it asked for.
Name it where you want the other one: the stroked glyph at `sm` is reachable no
other way, because until this prop the only lever on the style was a size that
also changes how big the glyph is.

### Where the glyph sits

By default the glyph sits in a gutter: a column of its own, level with the first
line and beside everything under it. That is what a heading, a paragraph and a
row of buttons all want to be indented past, and it is the right shape for an
alert that has something to say.

It is the wrong shape for an alert that is one sentence long. A gutter under a
single line is a column holding one thing, and in a form field's width the
message wraps into a narrow channel beside a mark that has nothing left to mark.
`icon-placement="inline"` sets the glyph at the head of the first line instead,
so the sentence wraps under it:

@docs('preview', name: 'alert-icon-placement', layout: 'stack')

The glyph is floated rather than set inline, which is what lets the same markup
serve an alert with a heading and one without: it is placed before both and
lands beside whichever turns out to hold the first line. A heading takes it when
there is one, as the third example shows, and the sentence takes it when there
is not. Nothing about the message decides which.

The glyph stays the same size in both, because `icon-size` is a separate
question from where the glyph goes. At the default `sm` it is a 20px mark on a
24px line, which sits comfortably; `icon-size="xs"` is the quieter one where the
message is the point and the mark is only there to say which kind it is.

## Borders

`border` draws the alert's edge in its own tone. It is off by default, because on
three of the four variants the fill is already the boundary; turn it on where the
alert has to hold its own against a busy page, or sit beside a [card](card.md)
that is drawn with one:

@docs('preview', name: 'alert-border', layout: 'stack')

`subtle` and `outline` draw the same edge, from
[`--shape-tone-border-strong`](../theming.md#tones) — the tone's
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

## Actions

Some messages end in something to do about them. `actions` is a named slot for
that row:

@docs('preview', name: 'alert-actions', layout: 'stack')

It is a named slot rather than something you write into the body, because the
body is wrapped in a small muted [`<x-shape::text>`](text.md) — a button written
there would render inside a paragraph. As a named slot it is the prose's sibling
instead of its child, and the row is only rendered where you wrote one.

Most alerts don't need it. The commonest action in a message is a single link in
the sentence itself — "[Verify your email address](#actions)." — and that already
works with no slot at all. Reach for `actions` when the thing to do is a button.

### Where the row sits

A narrow alert wants the row beneath the message. A wide one has room for a
single call to action out on the right, level with the text. That is a question
about how wide the alert turned out to be, not about what you meant when you
wrote it, so `actions-placement` names a width and lets the alert's own width
answer it. It defaults to `lg`: the row is stacked, and flips to a side-by-side
row once the alert is about 544px across:

@docs('preview', name: 'alert-actions-width', layout: 'stack')

Both of those are the same markup. The second is in a `max-w-sm` wrapper, which
is the whole difference. Nothing about the message decides it — the query
measures the alert, so the same block with three words in it or thirty flips at
the same width.

544px is the boundary between an alert inside something and an alert across
something. `max-w-sm` through `max-w-lg` sit below it, which is most of the
alerts in a form or a panel; the 640-to-720px column that documentation and
settings pages are built out of sits above it. Narrow this page's window far
enough and the first example will stack too, which is the behaviour rather than
a fault in the example.

#### Choosing the step

One width cannot be right for every alert, because what fits beside a message
depends on the message. A three-word notice with an `Undo` shares a line
comfortably at 416px; a heading, a paragraph and three buttons are still cramped
at 700px. So `actions-placement` also takes the step itself:

| Value | Flips at | Alert width |
| --- | --- | --- |
| `sm` | `@sm` — 24rem | 416px |
| `md` | `@md` — 28rem | 480px |
| `lg` | `@lg` — 32rem | 544px |
| `xl` | `@xl` — 36rem | 608px |
| `2xl` | `@2xl` — 42rem | 704px |

The alert width column is the step plus the `p-4` gutters, since a size query
measures the container's content box.

Both of these are 448px wide. The first takes the default `lg`, which wants
544px and so stacks; the second says `sm`, which is enough room for two words and
a ghost button:

@docs('preview', name: 'alert-actions-step', layout: 'stack')

**These are Tailwind's container sizes, not its breakpoints.** `md:` is 768px of
viewport; `@md:` is 28rem of the nearest query container, which here is the alert
itself. The same alert in a sidebar and across a page reaches `@md` at two very
different viewport widths and the same alert width, which is the only reading
that makes sense for a component that does not know where it was put. Nothing in
this library ships a viewport breakpoint, and this prop is not the exception.

There is no `auto`, deliberately. Omitting the prop is how you ask for the
library's step, and it is how the alerts written before this paragraph existed
will pick up the step if it moves again. A value that meant the same thing would
be a second spelling of leaving the prop off. Name a step when you have looked at
an alert and found the default wrong for what is in it; leave it alone otherwise.

`base` is not among them either, and the missing name is the tell. A `size` in
this library runs `sm`, `base`, `lg`, because that is Tailwind's *type* scale and
`text-md` does not exist. The container scale is a different one: it has an `md`
and no `base`. Calling the default step `base` would put an invented name into a
borrowed scale, in a slot the scale it was borrowed from has never had.

It is a container query, not a breakpoint. Nothing else in this library ships a
`sm:` or an `md:`, deliberately: a component cannot see the viewport it landed
in, and the same alert in a sidebar and across a page is the same markup at two
widths. Asking about its own width is the version of that rule a component can
keep. The alert declares itself the query container only when there is a row to
move, so an alert with no actions is an ordinary block with no `container-type`
on it.

`below` and `side` pin the row, for the two things a width cannot know. Three
buttons should stack whatever the room; one small "Leave" should stay out on the
right even in a narrow panel:

@docs('preview', name: 'alert-actions-placement', layout: 'stack')

When the row shares a line with the message, the message is the part that gives —
it takes `min-w-0` and the actions take `shrink-0` — and the row wraps before it
overflows, the way a [card](card.md)'s footer does.

The prop is named for the slot it places, the way `bar-square` is named for
`bar` and `icon-size` for `icon`.

### Buttons on a fill

Nothing is passed to the buttons you put in the row, and nothing corrects them
either. A [button](button.md) declares a `data-shape-tone` of its own, so it
keeps whatever tone you gave it — that is the point of the rule the
[dismiss](#dismissing) control is scoped by, and a button you put here is not a
control that was never given a tone.

Which mostly takes care of itself. The default `outline` button paints
`--shape-tone-surface`, which is white, so it reads as a white button on a
`solid` alert of any tone — a shape that has been on coloured banners for as long
as there have been coloured banners. The two to think about are `ghost` and
`subtle` on `solid`: both paint the neutral tint, which is a grey wash on a
saturated fill. Give those a tone matching the alert's, or reach for `outline`.

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
the control follows it. So does
[`data-shape-surface-hover`](#toning-the-text), which is what carries
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

@docs('preview', name: 'alert-shadow-step', layout: 'stack')

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

## Bars

`bar` draws one thick edge down a side of the alert, in the tone at full
strength. It is the [toast](toast.md)'s edge, given a side to choose:

@docs('preview', name: 'alert-bar', layout: 'stack')

A toast carries its whole tone in that rule, because its fill stays white so the
message reads over whatever it is floating above. An alert has four variants for
saying the same thing, so here the rule is opt-in. Reach for it where the alert
has to be findable down a long page without being filled — a `ghost` alert with a
bar is the quietest way this component has of saying which tone a message is — or
where the fill is already spoken for and the tone needs somewhere else to go.

The colour is `--shape-tone` itself, not the step back that
[`border`](#borders) takes. The two are drawing different things: a border bounds
the block and lets the fill speak, so it sits a step down the ramp, where a bar
*is* the speaking, and four pixels of a pale edge colour would say less than the
one pixel it replaced. `solid` is the exception it has to be — the fill is already
that colour — so there the bar takes the step *past* the fill, the same move the
border makes on that variant and for the same reason.

`left` is where a bare `bar` lands, because it is the toast's side and the one a
page read left to right marks a block on. The other three are there for layouts
that read better with the rule somewhere else — a `top` bar under a heading it
belongs to, a `right` bar in a sidebar that already has a left edge of its own:

@docs('preview', name: 'alert-bar-sides', layout: 'stack')

Unlike the [border](#borders) and the [shadow](#elevation), `ghost` draws its bar
at rest. What those two wait for is a surface to belong to: both ring the block,
and a ring around something that paints nothing is an edge nothing drew. A rule
down one side rings nothing — it is the mark in the margin a blockquote takes, and
it reads on the bare page as well as it reads on a fill.

A bar and a [`border`](#borders) compose: the border draws the other three sides
and the bar thickens and recolours the one it runs along, which is the toast's own
recipe.

### Squaring the bar

`bar-square` straightens the bar's ends. `rounded-shape` bends the last few
pixels of a four-pixel rule around the block, which reads as a stripe wrapped
round a corner rather than a cut down one side; squaring the two corners the bar
runs between leaves the other two rounded:

@docs('preview', name: 'alert-bar-square', layout: 'stack')

It follows the bar, so `bar="top" bar-square` flattens the top two corners and
`bar="right" bar-square` the right. Without a bar it does nothing, deliberately:
unrounding a block that has no rule to straighten is a decision about the shape of
the library rather than the end of one edge, and a call site that wants it says so
directly:

@docs('preview', name: 'alert-square', layout: 'stack')

The name carries the prop it modifies, the way [`icon-size`](#icons) does, because
it does nothing on its own. Bare `square` is the
[button](button.md#icon-only-buttons)'s word for an equal-sided control, and one word meaning two things across the
library is worse than a longer name.

## Heading and body

`heading` is a title above the body; the default slot is the body. Either can
stand alone:

@docs('preview', name: 'alert-heading', layout: 'stack')

## Alert or toast

If a message is still true after someone has read it, it is an alert. A
[toast](toast.md) is an event: it happened, it is announced, it goes away.

An alert carries no `role="alert"` and no `aria-live`, because this markup was on
the page when it loaded and announcing it repeats what a screen reader is about
to read anyway. Announcements belong to the toaster, where content arrives after
the fact.

## Theming

Every colour on an alert is a tone variable, and there are six of them here:
`--shape-tone` for the `solid` fill and the [bar](#bars), `--shape-tone-hover`
for the edge that fill takes, `--shape-tone-tint` for the `subtle` wash and the
one `ghost` paints under the pointer, `--shape-tone-ink` for
[toned](#toning-the-text) text, `--shape-tone-border` for the neutral chrome
edge `outline` draws, and `--shape-tone-border-strong` for the toned one
[`border`](#borders) asks for. None of them is a colour this component keeps, so
a [retint](../theming.md) moves every alert on the page with everything else
that carries the tone. A toned alert also publishes `data-shape-surface`, which
is where everything nested inside it takes its foreground from — see
[Toning the text](#toning-the-text).

Unlike the [button](button.md#theming) and the [badge](badge.md#theming), the
resting paint is written at zero specificity along with the geometry, so a class
at the call site wins outright — over the fill as well as the padding:

@docs('preview', name: 'alert-override', layout: 'stack')

What a `ghost` alert paints under the pointer is the exception: those are plain
classes, so a hover colour of your own ties with them and has to be made
important.

### The body copy

The slot is rendered inside a muted [`<x-shape::text>`](text.md) at `sm`, which
is the hierarchy the component means: the [heading](#heading-and-body) carries
the message and the body recesses under it. An alert with no heading is the case
where that is wrong — the one line *is* the message, and recessing it says the
opposite. The same line, muted and then at full strength:

@docs('preview', name: 'alert-body', layout: 'stack')

`as="span"` rather than the default `p`, because the slot is already inside a
paragraph — see [Actions](#actions), which is the same fact from the other
direction. `variant` is left at `base`, so the line paints `--shape-fg`: the
full-strength foreground of whatever surface the alert published, which is the
tone's ink on a toned alert and the page's on an untoned one.

To say it for every alert rather than at one call site, the wrapper is reachable
by attribute:

```css
[data-shape-alert] [data-shape-text][data-shape-variant='muted'] {
    color: var(--shape-fg);
}
```

`--shape-fg` rather than a colour, and that is the whole discipline here: a
literal would be right on one variant and wrong on the three others, because
`subtle`, `solid` and a hovered `ghost` each publish a different foreground for
the same tone.

### Every alert at once

`data-shape-variant` and `data-shape-tone` are both on the element, so a rule
can be as narrow as one arm of one tone:

```css
[data-shape-alert] { border-radius: 0; }
[data-shape-alert][data-shape-variant='subtle'] { padding: 1.25rem; }
[data-shape-alert][data-shape-tone='danger'] {
    --shape-tone-tint: var(--color-shape-danger-50);
}
```

The last of those is the general move: an alert reads the tone variables, so
restating one on the alert restyles it without touching the buttons and badges
that read the same variable elsewhere on the page.

The [dismiss control](#dismissing) is the one thing here a class cannot reach
inside a toned alert. The rule that corrects it lives in `@layer shape-surface`,
which is declared after Tailwind's layers and therefore beats a utility — that
section says why. It is the same arrangement the overlays use for their
[placement](modal.md#theming), and the two are the only places in the library
where a package rule outranks a class you passed.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `tone` | `neutral` | `info`, `success`, `warning`, `danger`, `brand`, `accent` |
| `icon` | resolved from `tone` | any [icon](icon.md) name, or `false` for none |
| `variant` | `subtle` | `subtle`, `outline`, `solid`, `ghost` |
| `heading` | — | a title above the body |
| `icon-size` | resolved from `size` | `xs`, `sm`, `base`, `lg`, `xl` |
| `dismissible` | `false` | adds a close button |
| `border` | `false` | draws the edge in the tone; on `outline` recolours the border it already has, on `ghost` shows it on hover only |
| `icon-variant` | chosen by `icon-size` | `outline`, `solid` |
| `toned` | `false` | paints the text in the tone, on `outline` and `ghost`; ignored on `subtle` and `solid`, where the fill decides |
| `shadow` | `false` | lifts the alert with `shadow-sm`; on `ghost` shows it on hover only |
| `bar` | — | draws one thick edge in the tone: `left`, `right`, `top`, `bottom`; bare `bar` is `left` |
| `bar-square` | `false` | squares the two corners the `bar` runs between; nothing without a bar |
| `icon-placement` | `gutter` | `inline` sets the glyph at the head of the first line and wraps the message under it |
| `actions-placement` | `lg` | the width the `actions` row flips beside the message at: `sm`, `md`, `lg`, `xl`, `2xl` ([container sizes](#choosing-the-step), not breakpoints); `below` and `side` pin it instead |

The default slot is the body. `actions` is a named slot for a row of
buttons.

## Folding

Tier A — `@blaze(fold: true, safe: ['heading', 'actions'])`.

`heading` is interpolated and nothing more, so an alert whose title comes from a
variable still folds. `tone` branches to resolve its glyph, `variant`, `toned`,
`border`, `shadow`, `bar` and `bar-square` branch to resolve the paint, and
`actions-placement` and `icon-placement` branch to resolve the layout, so
`:tone="$tone"` drops to the compiled path — the same prop is safe on the [button](button.md),
which only ever interpolates it. See [Folding](../folding.md).

`actions` is a named slot and is declared safe anyway, which is the one entry
worth a sentence. Blaze treats a named slot listed in `@props` as unsafe by
default, on the reasonable assumption that a component branching on a prop is
branching on its value — and a slot's value is whatever the call site wrote. This
one branches on whether the slot is *there*, which is not a runtime fact: a call
site either wrote `<x-slot:actions>` or it did not, and Blaze resolves that while
it folds.

`bar` branches hardest of the six: a side is a different border utility rather
than a different value of one, and Tailwind reads those class names out of the
component as text, so every side is spelled out and the arm is chosen at compile
time. `shadow` branches because `ghost` waits for the hover with it, which is a
different class and not a different value of one. Written literally —
`<x-shape::alert shadow>` — it is read at compile time like every other prop
here and costs nothing; `:shadow="$isFloating"` is what drops the alert to the
compiled path.
