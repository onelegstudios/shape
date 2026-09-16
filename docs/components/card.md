# Card

A surface. It separates itself from the page with a background shift and a
resting shadow, and draws no border unless you ask for one.

@docs('preview', name: 'card', layout: 'stack')

## Borders

`border` adds a hairline, for a card sitting on a surface too close to its own
to read against:

@docs('preview', name: 'card-border', layout: 'stack')

## Shadow

`shadow` is on by default — the resting elevation is what separates a card from
the page without a border. Turn it off for a card sitting inside a surface that
already reads as raised, where a second shadow would just be noise:

@docs('preview', name: 'card-shadow', layout: 'stack')

## Padding

Padding and the gap between children move together — `sm` is a tighter card
*and* tighter stacking. Five steps, `xs` to `xl`, under the same words `size`
takes everywhere else:

@docs('preview', name: 'card-padding', layout: 'stack')

`padding="none"` keeps the gap and drops the inset, for a card whose contents
reach the edge:

@docs('preview', name: 'card-padding-none', layout: 'stack')

## Links and buttons

`href` makes the whole card a link, and `as="button"` makes it a button. The
card answers the pointer with a faint tint laid under its content, and draws the
focus ring when a keyboard reaches it:

@docs('preview', name: 'card-link', layout: 'stack')

A card that is a link or a button cannot hold another control — an `<a>` or a
`<button>` with a button inside it is markup the browser rewrites. For a card
that has buttons of its own, see [the stretched link](#a-link-with-controls-beside-it).

## Header and footer

`card.header` stacks its children tightly; `card.footer` lays them out in a row.
Neither draws a rule — compose a [separator](separator.md) if you want one:

@docs('preview', name: 'card-regions', layout: 'stack')

### An action beside the title

`card.action` goes inside `card.header` and sits to the right of the title,
across the heading and its line of copy, held to the top edge — the place for a
menu, a close button or a toggle:

@docs('preview', name: 'card-action', layout: 'stack')

### A link with controls beside it

`card.link` is for a card that should open on a click and also holds controls.
Put it where the link's text belongs, usually inside the heading. It stretches
over the whole card, the card takes over the tint and the focus ring, and every
other control in the card is lifted above the stretch so it still does its own
job:

@docs('preview', name: 'card-stretched-link', layout: 'stack')

### Bleeding to the edge

A header or footer sits inside the card's padding, so a fill on one stops short
of the edge. `bleed` takes it to the edge: the card's padding is cancelled on
the sides and on the edge the region sits against, and put back inside it, so
the content stays where it was and the corners follow the card's radius:

@docs('preview', name: 'card-bleed', layout: 'stack')

It works at every `padding`, because the card publishes its inset as
`--shape-card-inset` and the region reads that rather than a size of its own.
Two things follow. Bleed a header only when it is the card's first child and a
footer only when it is the last — the margin pulls toward the edge whatever is
there. And if you change a card's padding with a class, set the variable with
it, or the bleed will cancel the old one:

```blade
<x-shape::card class="p-5 [--shape-card-inset:--spacing(5)]">
```

## Media

`card.media` takes a picture to the card's edge. Put an `<img>` — or a
`<picture>`, a `<video>`, an embed — inside it; the child becomes a full-width
block, and its height and fit are yours to set with classes on it:

@docs('preview', name: 'card-media', layout: 'stack')

There is no prop for where it goes. The media always reaches the sides, and it
reaches the top when it is the card's first child and the bottom when it is the
last, with the corners on that edge following the card's radius. It reads the
same `--shape-card-inset` a bleeding header does, so it works at every
`padding` and follows a padding you set with a class as long as you set the
variable with it.

It is made for the top and the bottom. Between two other children it bleeds to
the sides only, which works, but a picture in the middle of a card usually wants
to be two cards.

## Horizontal cards

`orientation="horizontal"` lays the card's children in a row, for a picture
beside the content. Wrap the content in `card.body`, a column that spaces its
children with the card's own gap. Media reaches the top and the bottom, and the
start or the end depending on where it sits. It takes a third of the card's
width until a class on it says otherwise, and the picture fills the height the
content sets, so give the image `object-cover`:

@docs('preview', name: 'card-horizontal', layout: 'stack')

A horizontal card stays horizontal at every width. For a card that stacks on a
phone, render a vertical one there. A bleeding header or footer is for vertical
cards: inside a `card.body` it would reach past the body into the picture.

## Separators

`<x-shape::separator />` stops at the card's padding like any other child.
`card.separator` reaches the edges: across a vertical card it is a horizontal
rule taken to both sides, and between two children of a horizontal card it is a
vertical rule taken to the top and the bottom. Inside a `card.body` it is a
plain horizontal rule:

@docs('preview', name: 'card-separator', layout: 'stack')

Header, footer, action, body, media and separator are all components rather than named slots, because deciding whether a slot has
content is a runtime question and asking it would take the card off the fold
path.

## Spacing

Nothing inside a card sets its own outer margin; the gap belongs to the card.
Change it with a utility on the card itself:

@docs('preview', name: 'card-spacing')

## Theming

A card is white — `shape-900` in dark mode — at `--radius-shape-lg`, with
`shadow-sm` under it and, if you asked for one, a `shape-200` hairline. Its text
is `--shape-fg`. Every one of those is written at zero specificity, so the card
is the component that yields most completely to a class:

@docs('preview', name: 'card-override')

The shadow is Tailwind's `--shadow-sm` rather than a scale of Shape's own, so
retheming the scale carries the card with everything else that sits on the page
— see [Elevation](../elevation.md).

### It is where a surface is published

A card with a fill of its own is the usual place to declare
`data-shape-surface`, which is what lets the [text](text.md) and
[headings](heading.md) inside it find a foreground that belongs on that fill
rather than the page's grey:

@docs('preview', name: 'text-surface')

The six filled surfaces, the two that read the tone, and how to declare one of
your own are in [the surface contract](../theming.md#the-surface-contract).

### Every card at once

```css
[data-shape-card] { border-radius: 0; box-shadow: none; }
```

Everything inside a card carries its own attribute —
`data-shape-card-header`, `-footer`, `-action`, `-body`, `-media`, `-link` and
`-separator` — and a bleeding header or footer also carries `data-shape-bleed`:

```css
[data-shape-card-footer][data-shape-bleed] { background: var(--color-shape-50); }
```

```css
[data-shape-card-media] img { filter: grayscale(1); }
```

`data-shape-padding` and `data-shape-orientation` carry the arms the card was
called with, so a rule can reach one of them:

```css
[data-shape-card][data-shape-padding='lg'] { padding: 2.5rem; }
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `border` | `false` | adds a hairline border |
| `shadow` | `true` | the resting elevation; set `false` to drop it |
| `padding` | `base` | `xs`, `sm`, `base`, `lg`, `xl`, `none` |
| `orientation` | `vertical` | `vertical`, `horizontal` |
| `href` | — | renders the card as a link |
| `as` | — | `a`, `button`, `div`; wins over the element `href` implies |

| `card.header` / `card.footer` prop | Default | Values |
| --- | --- | --- |
| `bleed` | `false` | cancels the card's padding at the sides and the region's own edge |

`card.media` takes no props; where it sits in the card, and the card's
`orientation`, decide which edges it reaches. `card.action`, `card.body` and
`card.separator` take none either. `card.link` takes the attributes of an `<a>`.

The default slot is each component's contents.

Cards use `shadow-sm`, the "raised" step — see [Elevation](../elevation.md).

## Folding

Tier A — `@blaze(fold: true)` on every file, with `card.separator` also
memoized, as the separator is. See
[Folding](../folding.md).
