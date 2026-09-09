# Button

An action. `variant` sets how loud the button is, `tone` sets what it says,
and the two are independent — which is what lets a destructive action be quiet.

@docs('preview', name: 'button')

## Variants

`variant` places an action in the page's hierarchy. Most pages have one true
primary action, so reach for `primary` once.

@docs('preview', name: 'button-variants')

## Tones

`tone` is what a variant paints with. `neutral` is the default and the right
answer for most buttons; the rest carry meaning.

@docs('preview', name: 'button-tones')

Because hierarchy and meaning are separate props, a destructive action does not
have to shout:

@docs('preview', name: 'button-quiet-danger')

## Borders

`border` draws the button's edge in its own tone. It is off by default, because
three of the four variants already have a boundary — a fill, or in `outline`'s
case the neutral edge it is drawn with. Turn it on where a button has to hold
its own against a busy page, or sit beside something drawn with an edge of its
own:

@docs('preview', name: 'button-border')

It is the [alert](alert.md#borders)'s prop and it means the same thing, down to
which step of the tone each variant takes. `subtle` and `outline` draw the same
edge, from [`--shape-tone-border-strong`](../theming.md#tones) — the tone's
answer to the neutral `--shape-tone-border` that an outlined thing takes by
default. It sits a step further along the ramp than the tint pair does, which is
what makes it read as an edge someone chose rather than a definition line.
`outline` is the one variant the prop adds no border to — it has one already —
so there it only decides whether that border carries the tone or the grey.

`primary` cannot use that step: a pale edge on a saturated fill reads as a
highlight, so it takes the step *past* the fill instead — darker in light mode,
brighter in dark, which is the same move the tone's own hover makes and the
reason it is not a fixed darkening.

`ghost` shows its edge only under the pointer, arriving with the tint it already
paints there, so the button stays unpainted at rest and the hover draws the
whole control at once. The border is reserved as a transparent one, so the label
does not shift by a pixel when it lands.

Both colours are steps of `--shape-tone`, not a palette of their own, so a
border follows a [retheme](../theming.md) with everything else. To make every
toned edge in the library heavier or lighter at once, move
`--shape-tone-border-strong`.

## Sizes

@docs('preview', name: 'button-sizes')

`sm` and `base` share a type size and differ in height and padding. `lg` steps
the text up too.

## Icons

`icon` renders before the label, `icon-trailing` after, and both take any
[icon](icon.md) name. Use one or both.

@docs('preview', name: 'button-icons')

The names there are `shape-*` because those are the drawings this package ships,
and a preview has to render for a reader who has generated nothing. In an
application they would be names of your own: `php artisan shape:icon plus
arrow-right`, then `icon="plus"`. See [Icon](icon.md).

`icon-size` picks how big the drawing is: `xs` is 16px, `sm` — the default — is
20px, and `base` is 24px. The style is left to the icon, which draws the small
sizes solid because a stroke does not read at 16px.

@docs('preview', name: 'button-icon-sizes')

## Icon-only buttons

`square` drops the horizontal padding and makes the button as tall as it is
wide, at every size. There is no label to read, so pass an `aria-label`:

@docs('preview', name: 'button-square')

Pair one with a [tooltip](tooltip.md) when the glyph alone is not obvious.

## Links

An `href` renders an anchor with the same styling, without being asked:

@docs('preview', name: 'button-link')

This is the same resolution the [badge](badge.md), the [avatar](avatar.md), the
[tab](tabs.md) and the [menu item](dropdown.md) make. Middle-click, "open in new
tab" and the status bar all work for a link and none of them work for a button
pretending to be one — and an `href` on a `<button>` is that mistake with
nothing to show for it, since the attribute is simply ignored.

`href` goes through the attribute bag rather than a prop, so `:href="$url"`
costs nothing and still folds: the element is decided by the attribute being
there, not by what it says.

`as="a"` is still accepted and still folds — it is just no longer what stands
between a call site and a working link. `as` earns its keep elsewhere: `div`,
for a button that sits inside something already clickable, and beating an
inferred anchor on the rare call site that carries an `href` and means something
else by it.

## Disabled

`disabled` is a plain attribute — Shape claims no prop for it — and dims the
button while removing pointer events. An anchor cannot be disabled, so use
`aria-disabled` there and Shape styles it the same way:

@docs('preview', name: 'button-disabled')

## Groups

`button.group` joins related actions into one control. The children are laid out
in a row, the corners that face a neighbour are squared off, and every button
past the first is pulled back a pixel, so that two 1px borders meet as one seam
rather than stacking into a 2px rule:

@docs('preview', name: 'button-group')

The group paints nothing. A button inside one is the same button it is outside
one — which also means a group of `primary` buttons has no seam to show, because
a fill has no edge. Ask for [`border`](#borders) there, or leave the set
`outline`, and the seam is the tone's own edge.

The inner corners flatten on the cascade rather than on `!important`: the button
writes its radius at zero specificity, and the group's selector carries a class
and a pseudo-class. The group also never names a corner that faces outward, only
the ones that face a neighbour — so a `rounded-full` passed to the first and
last button still shapes the ends, and a pill-shaped group stays a class at a
call site instead of becoming a prop here.

### Split buttons

A [dropdown](dropdown.md) trigger is a button, so it groups like one:

@docs('preview', name: 'button-group-split')

Keep the menu outside the group, as it is above. A closed popover is
`display: none`, which is not the same as being absent — `:last-child` still
counts it, and the button that is actually last would lose the corner it needs.

### Vertical

`orientation="vertical"` stacks the buttons and squares the corners on the other
axis instead:

@docs('preview', name: 'button-group-vertical')

### The focus ring turns inward

A button draws its ring 2px outside itself, the neighbour begins a pixel away,
and later siblings paint over earlier ones. So inside a group, every button but
the last would have the ring along its trailing edge painted out by the button
beside it. The usual answer is a `z-index`, and this library has promised there
[isn't one anywhere](../theming.md) — so the ring moves inside the button
instead, where nothing can cover it and no stack has to be invented.

A group of one keeps the outward ring it wears everywhere else. There is no
neighbour to hide it, and the selector says so.

### Naming a group

The group is a `role="group"`, and `label` is its accessible name. Pass one when
the set means something its buttons don't say on their own — `View` over `Day`,
`Week` and `Month`. Without a `label` no name is emitted at all, because an empty
one is worse than none.

`role` is a default rather than a fixture, so a set you have wired arrow keys to
yourself can pass `role="toolbar"`. Shape doesn't bind those keys, which is why
it is not the default.

A row of buttons that are merely near each other is not a group. Two buttons at
the foot of a form are one `<div class="flex gap-2">`, and this component would
join them into a control they aren't.

## Livewire and Alpine

Anything Shape doesn't claim as a prop lands on the rendered element:

```blade
<x-shape::button variant="primary" wire:click="save" wire:loading.attr="disabled">
    Save
</x-shape::button>
```

```blade
<x-shape::button icon="shape-trash" tone="danger" x-on:click="open = true">Delete</x-shape::button>
```

## Theming

A button paints out of the tone variables and nothing else. `primary` fills with
`--shape-tone` and hovers to `--shape-tone-hover`, `subtle` and `ghost` take
`--shape-tone-tint` and `--shape-tone-ink`, `outline` takes
`--shape-tone-border` over `--shape-tone-surface`, [`border`](#borders) asks for
`--shape-tone-border-strong` — or `--shape-tone-hover` on `primary`, the step
past the fill — and every variant draws its focus ring in `--shape-ring`. So a
retint moves every button in the application at once, and the token layer is
where a colour change should start — see [Theming](../theming.md).

The button is also the one component that reads a *tone* rather than a surface,
which is what lets a ghost `danger` button stay red inside a block that is not.
[The surface contract](../theming.md#the-surface-contract) covers the one
correction that arrangement needs.

### A class at the call site

The radius, the font weight and the type size are written at zero specificity,
so your own classes win without `!important`:

@docs('preview', name: 'button-override')

`w-full` is the one of those that is not an override at all — a button sets no
width of its own, so a class naming one has nothing to beat. It is in the
picture because it is the pairing this comes up in: a pill that runs the full
width of a form.

The paint and the height are the exception. Both the variant's `bg-*` and
`text-*` and the height and padding that `size` picks are emitted as plain
classes, so a class of your own only ties with them and wins or loses on
Tailwind's emit order rather than on what you meant. So reach for a
[tone](../theming.md#tones) first — and when the colour is not one the system
has, the durable answer is to [add a tone](../theming.md#a-tone-of-your-own)
rather than to fight the paint at a call site. Below, `accent` beside the same
button made violet by hand:

@docs('preview', name: 'button-tone-override')

Everything the tone was doing, the call site now owes. The flag has to reach
every state the variant paints: `primary` paints a hover, so an important `bg-*`
on its own is a button that changes colour under the pointer to the one it was
overridden away from.

The ink is the harder half, because it flips and the fill does not. Light
`neutral` is a dark fill carrying near-white ink; dark `neutral` is a light fill
carrying dark ink. Override the background alone and dark mode puts that dark
ink on `violet-700` — 2.38:1, where the button you started from was 15.6:1.

Which is why the classes above are the shipped tones' own recipe, written out by
hand: the `700` fill and `800` hover under white in light, and in dark a lighter
fill that *brightens* on hover, carrying the neutral `950` as ink. One step
differs. The tones fill at `500` in dark, and `violet-500` under that ink is
4.48:1 — `accent`'s fuchsia clears the same step at 5.58:1, so it is this ramp
and not the recipe that misses, and the fill goes to `400` for 6.91:1. Reading a
ramp for the step that carries its own text is exactly the work
[a tone](../theming.md#a-tone-of-your-own) does once, in one place, for every
component that reads it.

### Every button at once

Say it in your own stylesheet rather than at three hundred call sites. Unlayered
CSS beats anything in a layer, so this needs no specificity fight and no
`!important`:

```css
[data-shape-button] { border-radius: 9999px; }
```

`data-shape-variant` and `data-shape-tone` are on the same element, so a rule
can be as narrow as one arm of one tone:

```css
[data-shape-button][data-shape-variant='primary'] { font-weight: 600; }
```

The trade is that it also beats a `rounded-shape` passed at a call site, which
is what setting the corners in one place means. Use `--radius-shape` instead
when the whole library should follow — it is one decision for the cards and the
inputs too.

Past that, [`shape:eject`](../tooling.md#shapeeject) hands you the file.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `variant` | `outline` | `primary`, `outline`, `subtle`, `ghost` |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `size` | `base` | `sm`, `base`, `lg` |
| `icon` | — | any [icon](icon.md) name, rendered before the label |
| `icon-trailing` | — | any [icon](icon.md) name, rendered after the label |
| `icon-size` | `sm` | `xs`, `sm`, `base` |
| `square` | `false` | drops the horizontal padding, for icon-only buttons |
| `border` | `false` | draws the edge in the tone; on `outline` recolours the border it already has, on `ghost` shows it on hover only |
| `as` | resolved from `href` | `button`, `a`, `div` — an `href` implies `a`, and `button` otherwise |
| `type` | `button` | any button type |

The default slot is the label. Every other attribute — `href`, `disabled`,
`wire:*`, `class` — passes through to the rendered element.

`button.group` takes two:

| Prop | Default | Values |
| --- | --- | --- |
| `orientation` | `horizontal` | `horizontal`, `vertical` |
| `label` | — | the group's accessible name; omitted entirely when not given |

Its default slot is the buttons. `role` defaults to `group` and can be
overridden, and every other attribute lands on the wrapping `<div>`.

## Folding

Tier A — `@blaze(fold: true, safe: ['tone'])`.

`tone` is interpolated into an attribute and nothing more, so `:tone="$tone"`
still folds. Everything else is a static choice at the call site — including
[`border`](#borders), which branches to resolve the edge and so is read at
compile time like every other prop here. Written literally,
`<x-shape::button border>` costs nothing; `:border="$isDense"` is what would
drop the button to the compiled path. See [Folding](../folding.md).

`button.group` is the same tier — `@blaze(fold: true, safe: ['label'])`.
`orientation` picks the class set and so is read at compile time; `label` is
only ever interpolated, so a group named from a variable still folds. The
buttons in its slot fold on their own, the way an [avatar
group](avatar.md#groups)'s faces do — a slot is rendered by its call site, so
nothing in one is baked into the group's fold.
