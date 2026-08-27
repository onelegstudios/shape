# Tooltip

```blade
<x-shape::tooltip name="tip-archive" text="Archive this project">
    <x-shape::button square variant="ghost" icon="check" aria-label="Archive" />
</x-shape::tooltip>
```

## The slot is the trigger

The opposite arrangement to every other component here, and it has to be: the
tooltip lives in the top layer as a sibling, and the thing it describes is what
you already have in hand.

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the tooltip's id |
| `text` | *required* | what it says |
| `placement` | `top` | any placement the popover takes |

## `popover="manual"`

An auto popover light-dismisses on any click anywhere — including on the control
it belongs to. Manual means `shape.js` decides, which is where the open delay,
the show-immediately-on-keyboard-focus rule and Escape live.

## It describes the control, not the wrapper

`aria-describedby` is set by `shape.js` on the first focusable element inside the
wrapper rather than rendered on the `<span>` around it. A tooltip describes a
control; the wrapper is not what a screen reader lands on.

## What a tooltip cannot be

It is unreachable on touch and it disappears the moment attention moves. Anything
someone must read to complete a task belongs in the interface — a
[description](field.md) under a field, a line of [text](text.md), an
[empty state](empty.md). A tooltip is for the label of an icon button and for
detail that is genuinely optional.

The icon button still needs its own `aria-label`. The tooltip is a description,
not a name.

## Folding

Tier A — `@blaze(fold: true)`.
