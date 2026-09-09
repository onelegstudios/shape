# Tooltip

A label for an icon button. The slot is the trigger.

@docs('preview', name: 'tooltip')

That is the opposite arrangement to every other component here, and it has to
be: the tooltip lives in the top layer as a sibling, and the thing it describes
is what you already have in hand.

## Placement

@docs('preview', name: 'tooltip-placement')

## It describes the control, not the wrapper

`aria-describedby` is set by `shape.js` on the first focusable element inside the
wrapper rather than on the `<span>` around it. A tooltip describes a control;
the wrapper is not what a screen reader lands on.

The icon button still needs its own `aria-label`. The tooltip is a description,
not a name.

## What a tooltip cannot be

It is unreachable on touch and it disappears the moment attention moves.
Anything someone must read to complete a task belongs in the interface — a
[description](field.md#description) under a field, a line of [text](text.md), an
[empty state](empty.md). A tooltip is for the label of an icon button and for
detail that is genuinely optional.

## `popover="manual"`

An auto popover light-dismisses on any click anywhere — including on the control
it belongs to. Manual means `shape.js` decides, which is where the open delay,
the show-immediately-on-keyboard-focus rule and Escape live.

## Theming

A tooltip is the one thing in the library painted against the page: `shape-900`
with `shape-50` on it, inverted in dark mode, at `--radius-shape` with
`shadow-md`. That is what makes it read as a layer over the interface rather
than a part of it, and it is written at zero specificity like everything else,
so a class at the call site wins:

```blade
<x-shape::tooltip name="archive-tip" text="Archive" class="bg-shape-800 text-sm">
    <x-shape::button square icon="shape-trash" aria-label="Archive" />
</x-shape::tooltip>
```

It is a [popover](popover.md#theming) underneath, so the position is written
inline by `shape.js` and nudging it is the same `translate` rule. Every tooltip
at once is its own attribute:

```css
[data-shape-tooltip] { border-radius: 0.25rem; font-weight: 400; }
```

There is no arrow, and no prop to add one. A tooltip is placed by measurement
rather than by anchor positioning, so an arrow would be a second thing to keep
pointing at the trigger through every flip and clamp. `data-shape-placement`
holds the placement that was *asked for* and is not rewritten when the script
flips a tooltip that would not fit, so a `::after` drawn from that attribute
points the right way most of the time and the wrong way at the bottom of the
viewport. Which is the trade, stated rather than hidden: draw one if the layout
makes the flip unlikely.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the tooltip's id |
| `text` | *required* | what it says |
| `placement` | `top` | any placement the [popover](popover.md) takes |

The default slot is the control the tooltip describes.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md) and
[Overlays](../overlays.md).
