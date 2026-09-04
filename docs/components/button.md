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

## Overriding styles

Every default Shape sets carries zero specificity, so your own classes win
without `!important`:

```blade
<x-shape::button variant="primary" class="w-full rounded-full">Continue</x-shape::button>
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `variant` | `outline` | `primary`, `outline`, `subtle`, `ghost` |
| `tone` | `neutral` | `neutral`, `accent`, `danger`, `info`, `success`, `warning` |
| `size` | `base` | `sm`, `base`, `lg` |
| `icon` | — | any [icon](icon.md) name, rendered before the label |
| `icon-trailing` | — | any [icon](icon.md) name, rendered after the label |
| `icon-size` | `sm` | `xs`, `sm`, `base` |
| `square` | `false` | drops the horizontal padding, for icon-only buttons |
| `as` | `button` | `button`, `a`, `div` |
| `type` | `button` | any button type |

The default slot is the label. Every other attribute — `href`, `disabled`,
`wire:*`, `class` — passes through to the rendered element.

## Folding

Tier A — `@blaze(fold: true, safe: ['tone'])`.

`tone` is interpolated into an attribute and nothing more, so `:tone="$tone"`
still folds. Everything else is a static choice at the call site. See
[Folding](../folding.md).
