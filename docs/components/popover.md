# Popover

Anchored content in the top layer. The [dropdown](dropdown.md) without the menu
semantics — reach for that one when the content is a list of things to do, and
this when it is something to read or a small form.

@docs('preview', name: 'popover')

## Placement

@docs('preview', name: 'popover-placement')

It flips and clamps the same way the dropdown does, and for the same reason:
`shape.js` places every anchored overlay from one path.

## Padding

`tight` is what the dropdown uses:

@docs('preview', name: 'popover-padding')

Padding is a prop rather than a class you override. Two package defaults for one
property both carry zero specificity, so which of them wins would be decided by
Tailwind's ordering of the utilities rather than by which component meant it. A
`match` emits one class and the question never comes up.

The rule generalises: `[:where(&)]:` makes *your* classes win over the package's,
not one of the package's classes win over another.

## The trigger

`popovertarget` opens and closes it, with `aria-haspopup="dialog"`,
`aria-controls` and an `aria-expanded` that `shape.js` keeps honest from the
popover's own `toggle` event — state and announcement from one source, so they
cannot drift.

## Theming

The panel is white — `shape-900` in dark mode — with a `shape-200` hairline,
`--radius-shape`, `shadow-lg` and `--shape-fg` in it. Every one of those is
written at zero specificity, so a class at the call site wins:

@docs('preview', name: 'popover-override')

Padding also has a prop, and [Padding](#padding) says why: the
[dropdown](dropdown.md) is a popover with a tighter default, and two package
defaults for one property both carry zero specificity, so which of them won
would be Tailwind's ordering rather than which component meant it. Your own
`p-6` still beats whichever it emitted — `[:where(&)]:` makes *your* classes win
over the package's, not one of the package's win over another.

### Position is written, not declared

`shape.js` measures the trigger and writes `top` and `left` on the element on
every open, scroll and resize. Those are inline styles, so a `top-*` or `left-*`
class is overwritten the moment the popover opens — and it writes `margin: 0`
alongside them, which `@layer shape-overlay` says again for the browsers that
open one before the script runs. Both are deliberate: a margin on an anchored
element is an offset from the place it was just put. Nudge it with something the
script does not write:

```css
[data-shape-popover] { translate: 0 0.25rem; }
```

### Every popover at once

`[data-shape-popover]` is on the popover, the [dropdown](dropdown.md) and the
[tooltip](tooltip.md), since all three are the same primitive. Name the
narrower attribute when you mean one of them:

```css
[data-shape-popover]:not([data-shape-tooltip]) { border-radius: 1rem; }
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the popover's id |
| `placement` | `bottom-start` | `bottom-start`, `bottom-end`, `bottom`, `top-start`, `top-end`, `top` |
| `padding` | `base` | `base`, `tight` |
| `role` | — | set it if the content warrants one |

`popover.trigger` takes `for` and `haspopup` (`dialog` by default) and passes
everything else to a [button](button.md). The default slot is the content.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md) and
[Overlays](../overlays.md).
