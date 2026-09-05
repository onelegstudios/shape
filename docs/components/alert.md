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

`outline` takes a neutral border and leaves the colour to the text and the glyph
— the same recipe the [badge](badge.md#variants) uses, and what keeps `brand` and
`accent`, the two tones that draw no glyph, visibly toned. `solid` fills with the
tone and is worth spending sparingly: a saturated block that size competes with
everything around it.

`ghost` is `outline` with the border dropped, and the quietest of the four: no
fill, no edge, just the glyph and the tone's ink in the flow of the page. Reach
for it where the alert is already inside something boxed — a
[card](card.md), a panel, a form section — and a second border would only draw a
box inside a box. It keeps the padding the others have, so switching a variant
never shifts the text.

It is also the one variant that paints on hover, in `subtle`'s tint — the fill
it would have had at rest. An alert is not a control and the hover promises no
click; what it does is show the bounds an unpainted block has no other way of
drawing, which is worth having when the block carries a
[dismiss](#dismissing) button. The ghost [button](button.md#variants) hovers to
the same variable, so a ghost control inside a ghost alert agrees with it.

Inside a `solid` alert the muted foreground is not dialled back, because there is
nowhere for it to go. White on the 700 fills starts at 4.9:1 for `success`, so
any tint that reads as recessed lands under AA. Hierarchy comes from the
heading's size and weight instead.

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

An alert publishes its own foreground, so a nested muted paragraph reads a
dialled-back version of the tone rather than grey on pink:

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
| `heading` | — | a title above the body |
| `icon` | resolved from `tone` | any [icon](icon.md) name, or `false` for none |
| `icon-size` | `sm` | `xs`, `sm`, `base` |
| `dismissible` | `false` | adds a close button |

The default slot is the body.

## Folding

Tier A — `@blaze(fold: true, safe: ['heading'])`.

`heading` is interpolated and nothing more, so an alert whose title comes from a
variable still folds. `tone` branches to resolve its glyph and `variant` branches
to resolve its paint, so `:tone="$tone"` drops to the compiled path — the same
prop is safe on the [button](button.md), which only ever interpolates it. See
[Folding](../folding.md).
