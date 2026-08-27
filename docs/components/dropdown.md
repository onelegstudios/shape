# Dropdown

```blade
<x-shape::dropdown.trigger for="row-actions" icon-trailing="chevron-down">Actions</x-shape::dropdown.trigger>

<x-shape::dropdown name="row-actions">
    <x-shape::dropdown.item icon="check">Approve</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="arrow-right" href="/invoices/1">Open invoice</x-shape::dropdown.item>
    <x-shape::separator class="my-1" />
    <x-shape::dropdown.item icon="trash" color="danger">Delete</x-shape::dropdown.item>
</x-shape::dropdown>
```

## It is a popover with menu semantics

The `popover` attribute supplies light dismiss, Escape and the top layer. Being
in the top layer is what fixes the oldest bug in this category: a menu clipped by
the `overflow: hidden` of a card three ancestors up, or covered by a sticky
header with a higher `z-index`. There is no `z-index` in this component.

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the popover's id; what a trigger points `for` at |
| `placement` | `bottom-start` | `bottom-start`, `bottom-end`, `bottom`, `top-start`, `top-end`, `top` |

`dropdown.item` takes `icon`, `icon-variant`, `color`, and anything else you pass
— `href`, `wire:click`, `disabled`.

## Anchoring

`shape.js` finds the trigger by the menu's own id, measures both, applies
`placement`, flips to the other side when the viewport runs out, and clamps to
stay in view. It repeats that on every scroll and resize while the menu is open.
There is no anchor name in the markup and nothing to keep in sync.

This was CSS anchor positioning with a script as a fallback, which is the better
mechanism and cost no JavaScript at all — until it turned out to ship in halves.
A browser can support `anchor-name` without `position-area`; `position-try-fallbacks`
takes try-tactics and `@position-try` names, not the bare position-area values
that read so naturally, so the fallbacks were dropped as invalid wherever the
rest worked. Two paths each gating on a different half of one feature is how a
menu opens upwards in one browser and nowhere in another. One path that always
runs is worth more than a declarative path that sometimes does.

## Keyboard

`aria-haspopup="menu"` is a promise that arrow keys will work, and `shape.js`
keeps it: <kbd>↓</kbd> and <kbd>↑</kbd> move between items, <kbd>Home</kbd> and
<kbd>End</kbd> jump to the ends, and opening the menu focuses the first item.
Items keep their natural tab order underneath that, so a browser that never runs
the script still leaves every item reachable.

Choosing an item closes the menu — the popover only light-dismisses on a click
*outside* itself, and an action that leaves its own menu standing looks like it
didn't fire. Opt out per item with `data-shape-keep-open`.

## Items are children, not an array

An `:items` array would need a convention for labels, icons, destructive styling
and `wire:click`. Children already compose with `@foreach`, with a separator, and
with your own markup. This is the same call `select` makes about its options.

## Link or button

An item with `href` renders an `<a>`; everything else renders a `<button>`.
Middle-click, open-in-new-tab and the status bar all work for a link and none of
them work for a button pretending to be one.

## Folding

Tier A — `@blaze(fold: true)`, trigger, menu and items alike.
