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

`as="a"` renders an anchor with the same styling. `href` goes through the
attribute bag, so `:href="$url"` costs nothing.

@docs('preview', name: 'button-link')

`as` also takes `div`, for a button that sits inside something already
clickable.

## Disabled

`disabled` is a plain attribute — Shape claims no prop for it — and dims the
button while removing pointer events. An anchor cannot be disabled, so use
`aria-disabled` there and Shape styles it the same way:

@docs('preview', name: 'button-disabled')

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
| `as` | `button` | `button`, `a`, `div` |
| `type` | `button` | any button type |

The default slot is the label. Every other attribute — `href`, `disabled`,
`wire:*`, `class` — passes through to the rendered element.

## Folding

Tier A — `@blaze(fold: true, safe: ['tone'])`.

`tone` is interpolated into an attribute and nothing more, so `:tone="$tone"`
still folds. Everything else is a static choice at the call site — including
[`border`](#borders), which branches to resolve the edge and so is read at
compile time like every other prop here. Written literally,
`<x-shape::button border>` costs nothing; `:border="$isDense"` is what would
drop the button to the compiled path. See [Folding](../folding.md).
