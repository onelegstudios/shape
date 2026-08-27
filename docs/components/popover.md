# Popover

```blade
<x-shape::popover.trigger for="usage" variant="subtle">Usage</x-shape::popover.trigger>

<x-shape::popover name="usage" placement="bottom-end">
    <x-shape::heading :level="3" size="sm">This month</x-shape::heading>
    <x-shape::text size="sm" variant="muted">4,210 of 10,000 requests.</x-shape::text>
</x-shape::popover>
```

## The dropdown without the menu

Same platform primitive, same anchoring, no `role="menu"` and no arrow keys —
because its content is arbitrary rather than a list of actions. Reach for the
[dropdown](dropdown.md) when the content is a list of things to do, and this when
it is something to read or a small form.

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the popover's id |
| `placement` | `bottom-start` | `bottom-start`, `bottom-end`, `bottom`, `top-start`, `top-end`, `top` |
| `padding` | `base` | `tight` is what the dropdown uses |
| `role` | — | set it if the content warrants one |

## Padding is a prop, not a class to override

The dropdown is the popover with tighter padding, and it says so with
`padding="tight"` rather than by passing a class. Two package defaults for one
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

## Folding

Tier A — `@blaze(fold: true)`.
