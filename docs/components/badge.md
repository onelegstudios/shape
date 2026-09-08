# Badge

A small piece of state attached to something else. The text is the `label` prop
rather than a slot.

@docs('preview', name: 'badge')

## Tones

`tone` says what the badge means, and resolves a matching icon:

@docs('preview', name: 'badge-tones')

## Variants

`variant` is how loud the badge is. `subtle` is the default, because a badge is
almost always annotating something rather than being the thing you look at:

@docs('preview', name: 'badge-variants')

Both read the same tone variables the [button](button.md) reads, so a badge and
a button given the same tone agree without either knowing about the other.

## Sizes

Four heights — 16px, 20px, 24px and 28px — moved apart by the vertical padding
rather than by the type size. `text-2xs` and `text-xs` share a 1rem line box, so
a scale that changed only the type and the side padding would draw two badges
the same height:

@docs('preview', name: 'badge-sizes')

## Icons

Every state colour resolves a glyph of its own, so a badge stays
readable in greyscale and to anyone who can't separate the hues. `icon` picks a
different one, `:icon="false"` removes it, `icon-trailing` adds one after the
label, and `icon-size` changes how big both of them are:

@docs('preview', name: 'badge-icons')

| `tone` | Icon |
| --- | --- |
| `success` | `shape-success` |
| `danger` | `shape-danger` |
| `warning` | `shape-warning` |
| `info` | `shape-info` |
| `neutral`, `brand`, `accent` | none — none of the three is a state to signal |

Nothing is ever resolved into `icon-trailing`: the state glyph belongs in front
of the label, and a second copy behind it would say the same thing twice. It is
for a drawing of your own — a chevron on a badge that opens something.

## Inline text

A badge is `inline-flex`, so it sits on its line as one atomic box. When its
padding makes it taller than the surrounding text's line-height, the browser
grows that line to fit it — a `base` badge is 24px tall, which is taller than
a 21px `text-sm` line, so the line carrying the badge sits further from its
neighbours than the rest of the paragraph. `inset` cancels the padding above
with an equal negative margin, so the badge keeps its size without growing
the line it's on:

@docs('preview', name: 'badge-inset', layout: 'stack')

## Long labels

A badge never wraps, so a long label makes a wide badge — and a wide badge in a
table sets the width of the column it sits in. Give it a maximum width and the
label truncates instead:

@docs('preview', name: 'badge-truncate')

```blade
<x-shape::badge :label="$contract->state" tone="warning" class="max-w-40" />
```

The width is yours to choose, because only the call site knows what it has room
for: a fixed one in a filter bar, or `max-w-full` in a cell that already has a
width of its own — the third badge above.

Only the label gives. The state glyph, a trailing icon and the dismiss × are all
`shrink-0`, so they keep their size and the ellipsis lands in the text.

The label is wrapped in a span of its own to make that possible. Text sitting
straight inside a flex container is an anonymous flex item, and `text-overflow`
does not reach into one — a width and a `truncate` on the badge itself clip the
label mid-word with no ellipsis at all, and eat the right padding while they do
it. The ellipsis has to sit on an element, and since nothing you pass reaches
inside a badge, that element has to be one the component draws.

It costs a badge that isn't capped nothing: a badge sized by its own content
measures the same with the span as without.

## Buttons and links

`as="button"` makes the badge a control, and an `href` makes it a link without
being asked:

@docs('preview', name: 'badge-controls')

Most badges are labels on a row. Some are the way into something — a filter chip
that clears itself, a state that opens the record behind it — and those have to
be pressable by a keyboard as well as by a pointer.

`href` resolving to an `<a>` on its own is the same resolution the
[avatar](avatar.md), the [tab](tabs.md) and the [menu item](dropdown.md) make.
Middle-click, "open in new tab" and the status bar all work for a link and none
of them work for a button pretending to be one. Pass `as` as well where you want
a button that happens to carry an `href`; `as` wins.

`as` also takes `div`, for a badge inside something already clickable — the same
escape hatch the [button](button.md#links) has, for the same reason.

### The control is the badge

It swaps the tag and nothing else about the box. The same element carries the
same classes, the same padding, the same `data-shape-badge` and the same
attribute bag it always carried, so everything Shape doesn't claim as a prop
lands on the thing being pressed:

```blade
<x-shape::badge :label="$filter->name" tone="brand" icon-trailing="shape-arrow-right" as="button" wire:click="clear" />
```

`type` is part of that bag, and passing it is how a chip inside a form submits
it rather than doing nothing:

```blade
<x-shape::badge label="Apply" tone="brand" as="button" type="submit" />
```

A badge that asks for no type is a `type="button"`, the same default the
[button](button.md) takes, so a chip that sits in a form and answers a
`wire:click` never submits it by accident.

The label is the accessible name, exactly as it is the text — there is nothing
else in a badge to name it with. A control whose `label` is a glyph and an
`:icon="false"` is a control announced as "button" and nothing else, so give it
`aria-label` where the text isn't the name.

### It repaints rather than dims

Which is where it parts from the [avatar](avatar.md#it-dims-rather-than-repaints).
An avatar's paint is what the avatar means; a badge's is the same chrome the
button paints, out of the same variables. So the hover is the button's too — a
louder version of the same paint, one step per `variant`, and `outline` takes
the tint the ghost button takes because it has no fill to lift.

The focus ring is the button's exactly: `--shape-ring`, two pixels, offset two.
A control that focused differently from every other control in the library would
be reporting a difference that is not there. `disabled` and `aria-disabled` both
dim the badge and remove pointer events, for the reason the button carries both
— an anchor cannot be disabled.

A badge that is not a control gets none of it: no transition, no hover, no ring,
no dimming. A `<span>` that lit up under the pointer would be promising a press
that isn't there.

## Selecting

`selected` says whether a chip is on. It is the other half of a filter bar:
[`dismissible`](#dismissing) takes a chip off, and this turns one on.

@docs('preview', name: 'badge-selected')

```blade
@foreach ($states as $state)
    <x-shape::badge :label="$state->name" as="button" :selected="$state->is($applied)" wire:click="apply('{{ $state->key }}')" />
@endforeach
```

The state goes where the element can carry it. A button gets `aria-pressed`,
which is a button's claim about itself; a link gets `aria-current="page"`, which
is its claim about where it points — the same split the [tab](tabs.md) makes,
and `aria-current` is global, so an `as="div"` chip takes it too.

Passing nothing at all is different from passing `false`. A chip that clears a
filter is an action rather than a state, and an `aria-pressed="false"` on it
would report a pressed-ness nobody asked about, so a badge is a toggle only once
`selected` is written:

```blade
<x-shape::badge label="Overdue" as="button" :selected="false" />  {{-- a toggle, off --}}
<x-shape::badge label="Clear" as="button" />                      {{-- not a toggle --}}
```

### On is the fill

A selected chip takes the fill its tone would have had as a `solid` badge,
because that is the loudest a badge gets and the one that is on should be the
one you see first. `solid` has nowhere louder to go, so it takes the step it
uses for hover instead — which is a real difference and a quiet one, so a bar of
toggles reads best built out of the default `subtle` or out of `outline`.

The paint is written as `aria-pressed:` and `aria-[current=page]:` variants
rather than as a branch, so the colour cannot disagree with the announcement:
the attribute that carries the state to a screen reader is the same one that
paints it. A badge that is not a toggle carries neither the attributes nor the
rules.

### It has to be pressable

`selected` needs a control, and a badge given one without the other throws:

```blade
{{-- Throws. A span has no state anyone can change, and nowhere to announce one. --}}
<x-shape::badge label="Overdue" :selected="true" />
```

Both ways out of that are silent: an `aria-pressed` on a `<span>` is not a state
any reader is given, and painting the chip without one is colour saying what
nothing says out loud.

It cannot be [dismissible](#dismissing) either, and for the reason a dismissible
badge cannot be a control — a toggle and a dismiss button are two controls, and
a badge is one element.

## Dismissing

`dismissible` adds a close button after the label, which turns the badge into a
chip — a filter someone can take off, a tag they can remove:

@docs('preview', name: 'badge-dismissible')

The button carries `data-shape-dismiss`, the same hook the
[alert](alert.md#dismissing) and the [toast](toast.md) carry, and the same
delegated listener in `shape.js` removes the nearest of the three. There is one
dismissal mechanism in the library and a badge does not add a second.

Dismissal is not remembered, the same as the [alert](alert.md#dismissing): the
element is removed from the page and the next render brings it back, so the
filter a chip stood for has to be cleared where it is kept. The attribute bag is
on the badge and the × is inside it, so a handler on the badge catches the click
on its way up:

```blade
<x-shape::badge :label="$filter->name" dismissible wire:click="remove({{ $filter->id }})" />
```

That is a `wire:click` on a `<span>` rather than on a control, which is exactly
what it looks like: the badge is not pressable, the × inside it is, and the
handler is listening to a click it did not draw the button for. It is also the
only place to put one, since nothing a call site passes reaches inside.

It draws its own button rather than composing the [button](button.md). The
registry ejects a component with everything it composes, so a badge that used
one would pull the button into an application that asked for a badge; and the
button's box is taller than a 16px `xs` badge, so the control would end up
setting the height of the thing it sits in. The × takes no colour of its own
either — it inherits whatever ink the `variant` resolved, so it stays readable
on a tint, on a fill and on an outline without branching on any of them.

The `label` goes into the button's accessible name — "Dismiss Overdue" rather
than "Dismiss", because a filter bar of twenty chips all announced the same way
names the control and not the chip it removes.

### Not also a control

`dismissible` cannot be combined with `as` or an `href`, and a badge given both
throws:

```blade
{{-- Throws. --}}
<x-shape::badge label="Overdue" href="/invoices" dismissible />
```

The × is a control, and it is inside the badge. A `<button>` inside a `<button>`
is not invalid-but-tolerated markup — it is markup the parser rewrites, closing
the outer control at the inner one, so a call site that wrote a nesting gets two
siblings. An `<a>` does the same to any interactive content inside it.

It raises rather than picking one, because both choices available are silent:
dropping the control loses the navigation, dropping the × loses the dismissal,
and neither leaves a trace at the call site. A chip that both navigates and
dismisses is two controls, and two controls need a box around them — which is
something a call site can write and not something this component can be, since
[the control is the badge](#the-control-is-the-badge) and there is only ever one
element here.

## Overriding styles

The badge's geometry and type are written at zero specificity, so your own
classes win without `!important`. A badge takes `rounded-shape` like the rest
of the library; `rounded-full` makes it a pill. Nothing else in the badge
assumes square corners, and the radius reaches nothing else — the state glyph
is a flex child and `inset` is a margin.

The side padding is left where it was, which keeps the label the same distance
from the cap as it was from a straight edge. Widen it by a step if you want the
ends looser:

@docs('preview', name: 'badge-pill')

To round every badge at once, say so in your own stylesheet rather than in the
radius token. `--radius-shape` is one decision for the whole library, so moving
it there would round the cards and the inputs with it:

```css
[data-shape-badge] { border-radius: 9999px; }
```

Unlayered CSS beats anything in a layer, so that wins without a specificity
fight. It also beats a `rounded-shape` passed at a call site, which is the
trade for setting the corners in one place.

The tone colours are the exception. `variant` emits its `bg-*` and `text-*`
as plain classes rather than at `[:where(&)]:`, so a colour of your own only
ties with them, and wins or loses on Tailwind's emit order. Reach for a
[tone](../theming.md) first; when the colour is not one the system has, make
it important:

@docs('preview', name: 'badge-tone-override')

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `variant` | `subtle` | `subtle`, `solid`, `outline` |
| `size` | `base` | `xs`, `sm`, `base`, `lg` |
| `icon` | resolved from `tone` | any [icon](icon.md) name, or `false` to omit |
| `icon-trailing` | — | any [icon](icon.md) name, rendered after the label |
| `icon-size` | `xs` | `xs`, `sm`, `base` |
| `inset` | `false` | `true` to cancel the vertical padding with a negative margin, for a badge inline in text |
| `dismissible` | `false` | adds a close button; cannot be combined with `as` or `href` |
| `selected` | — | `true` or `false` makes the badge a toggle and paints the on state; needs `as` or an `href`, and cannot be combined with `dismissible` |
| `as` | `span` | `button`, `a`, `div` — an `href` implies `a` |
| `type` | `button` | any button type; only reaches an `as="button"` badge |

There is no slot: Blaze memoizes a component only when it has none and is called
self-closing, and a badge — one per row, every row, every page — is the best
candidate for memoization in the library.

## Folding

Tier B — `@blaze(fold: true, memo: true, safe: ['label'])`.

`label` is interpolated and nothing more, so a badge folds even though its text
differs on every row. `tone` branches to resolve the state icon, so it cannot
be `safe`, and neither can `as`, which decides the element and the chrome that
comes with it, or `dismissible`. Both of those are words written at a call site
rather than bound, so a badge that is a control and a badge that is a chip fold
like any other.

`selected` is the exception, because it is a state rather than a word and comes
bound per chip. A filter bar therefore does not fold — which is the [tab
strip](tabs.md#folding)'s position, and worth knowing rather than worth
avoiding: a bar is a handful of chips where a table is two hundred rows.

```blade
{{-- Folds. --}}
<x-shape::badge label="Paid" tone="success" />

{{-- Folds. The label is safe. --}}
<x-shape::badge :label="$invoice->reference" tone="success" />

{{-- Does not fold. Memoizes instead. --}}
<x-shape::badge :label="$invoice->state" :tone="$invoice->tone" />
```

Memoization pays off on a cache hit, so the last form is cheap while labels
repeat and expensive when they don't. Measured over 200 rows:

| Call site | Cost | Memo entries |
| --- | --- | --- |
| Static tone, any label | 0.26 ms | 0 — it folds |
| Dynamic tone, ~5 repeated labels | 0.77 ms | 5 |
| Dynamic tone, label unique per row | 17.0 ms | 200 |

Keep the tone static wherever you can. Deriving it at the call site costs
nothing and folds every branch:

```blade
@foreach ($invoices as $invoice)
    @if ($invoice->isPaid())
        <x-shape::badge :label="$invoice->reference" tone="success" />
    @else
        <x-shape::badge :label="$invoice->reference" tone="danger" />
    @endif
@endforeach
```

See [Folding](../folding.md).
