# Dropdown

A menu of actions, anchored to its trigger. Items are children.

@docs('preview', name: 'dropdown')

It is a popover with menu semantics, so light dismiss, Escape and the top layer
come from the platform. Being in the top layer fixes the oldest bug in this
category: a menu clipped by the `overflow: hidden` of a card three ancestors up,
or covered by a sticky header. There is no `z-index` in this component.

## Placement

`placement` decides which corner the menu opens from. It flips to the other side
when the viewport runs out, and clamps to stay in view:

@docs('preview', name: 'dropdown-placement')

`shape.js` finds the trigger by the menu's own id, measures both and applies the
placement on every scroll and resize while the menu is open. There is no anchor
name in the markup and nothing to keep in sync.

## Items

`dropdown.item` takes an `icon` and a `tone`, and passes everything else
through — `href`, `wire:click`, `disabled`. An item with `href` renders an `<a>`;
everything else renders a `<button>`:

@docs('preview', name: 'dropdown-items')

Compose freely: a [separator](separator.md) between groups, an `@foreach`, your
own markup. An `:items` array would need a convention for labels, icons,
destructive styling and `wire:click`, all of which children already have.

## Keyboard

`aria-haspopup="menu"` is a promise that arrow keys will work, and `shape.js`
keeps it: <kbd>↓</kbd> and <kbd>↑</kbd> move between items, <kbd>Home</kbd> and
<kbd>End</kbd> jump to the ends, and opening the menu focuses the first item.
Items keep their natural tab order underneath that, so a browser that never runs
the script still leaves every item reachable.

Choosing an item closes the menu — an action that leaves its own menu standing
looks like it didn't fire. Opt out per item with `data-shape-keep-open`.

## Theming

The menu is a [popover](popover.md#theming) and paints exactly as one does — the
same white panel, hairline, radius and `shadow-lg` — so everything said there
applies here, including that the panel's padding is a prop rather than a class.

The items are the dropdown's own. An item paints `--shape-tone-ink` and hovers
to `--shape-tone-tint`, which is what makes `tone="danger"` a red item that
still belongs to the menu rather than a red rectangle in it. Its radius is
`--radius-shape` less a step, so a rounded item sits inside a rounded menu
without the corners fighting.

The hover and the ink are emitted as plain classes, so a colour of your own ties
with them and has to be made important. A [tone](../theming.md#tones) is the
better answer wherever the colour means something:

```blade
<x-shape::dropdown.item tone="danger" wire:click="delete">
    Delete
</x-shape::dropdown.item>
```

### Every menu at once

```css
[data-shape-menu-item] { border-radius: 0; padding-inline: 0.75rem; }
[data-shape-menu-item][data-shape-tone='danger']:hover { font-weight: 500; }
```

`data-shape-menu` is on the panel and `data-shape-menu-item` on each item, so a
rule can reach the menus without touching the popovers that are not menus:

```css
[data-shape-menu] { min-width: 14rem; }
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the popover's id; what a trigger points `for` at |
| `placement` | `bottom-start` | `bottom-start`, `bottom-end`, `bottom`, `top-start`, `top-end`, `top` |

`dropdown.item`:

| Prop | Default | Values |
| --- | --- | --- |
| `icon` | — | any [icon](icon.md) name |
| `icon-size` | `sm` | `xs`, `sm`, `base`, `lg`, `xl` |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `as` | resolved from `href` | `button`, `a`, `div` |

`dropdown.trigger` takes `for` and passes everything else to a
[button](button.md).

## Folding

Tier A — `@blaze(fold: true)`, trigger, menu and items alike. See
[Folding](../folding.md) and [Overlays](../overlays.md).
