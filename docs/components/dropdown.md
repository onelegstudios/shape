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

`dropdown.item` takes an `icon` and a `color`, and passes everything else
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

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the popover's id; what a trigger points `for` at |
| `placement` | `bottom-start` | `bottom-start`, `bottom-end`, `bottom`, `top-start`, `top-end`, `top` |

`dropdown.item`:

| Prop | Default | Values |
| --- | --- | --- |
| `icon` | — | any icon name |
| `icon-variant` | `mini` | `micro`, `mini`, `solid`, `outline` |
| `color` | `neutral` | `neutral`, `accent`, `danger`, `success`, `warning` |
| `as` | resolved from `href` | `button`, `a`, `div` |

`dropdown.trigger` takes `for` and passes everything else to a
[button](button.md).

## Folding

Tier A — `@blaze(fold: true)`, trigger, menu and items alike. See
[Folding](../folding.md) and [Overlays](../overlays.md).
