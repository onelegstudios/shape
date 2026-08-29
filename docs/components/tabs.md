# Tabs

A strip of tabs over panels, or a strip of navigation links. Which one you get
depends on whether a tab has an `href`.

@docs('preview', name: 'tabs-panels', layout: 'stack')

`for` on a tab matches `name` on a panel. Exactly one of each carries
`selected`.

## Icons

@docs('preview', name: 'tabs-icons', layout: 'stack')

## Vertical

`orientation="vertical"` stacks the strip and moves the arrow keys to
<kbd>↑</kbd> and <kbd>↓</kbd>:

@docs('preview', name: 'tabs-vertical', layout: 'stack')

## Links

A tab with an `href` renders an `<a>`, carries `aria-current="page"`, and needs
no JavaScript at all:

@docs('preview', name: 'tabs-links', layout: 'stack')

Note `as="nav"` on the strip. A row of navigation links is not a tablist:
`role="tablist"` claims that arrow keys move between these, that exactly one is
selected, and that each controls a panel in this document — three claims that
are all false of a link. `as="nav"` renders a `<nav>` with no role, which is
also what keeps the script from binding arrow keys to something that should
answer to <kbd>Tab</kbd>.

In a real application `selected` comes from the route:

```blade
<x-shape::tabs.tab href="/settings/billing" :selected="request()->is('settings/billing')">
    Billing
</x-shape::tabs.tab>
```

## Keyboard

| Key | |
| --- | --- |
| <kbd>←</kbd> <kbd>→</kbd> | move between tabs, cycling at both ends |
| <kbd>↑</kbd> <kbd>↓</kbd> | the same, when `orientation="vertical"` |
| <kbd>Home</kbd> <kbd>End</kbd> | first and last tab |
| <kbd>Tab</kbd> | leaves the strip |

Activation follows focus: moving to a tab selects it. Exactly one tab may carry
`selected` — it is the strip's single tab stop, and the arrow keys move within
it.

## Without the script

Every panel is visible and every tab is focusable. The page reads long rather
than broken, which is why tabs — unlike [toasts](toast.md) — do not spend the
library's one "needs the script" promise.

`hidden` is the mechanism, and it is deliberately an attribute rather than a
class: the script toggles it, so a panel carrying its own `display` utility
would outrank it and never hide. That is the one thing to know before styling a
panel.

## Reference

| Component | Prop | Default | Values |
| --- | --- | --- | --- |
| `tabs` | `as` | — | `nav` for a strip of links |
| | `label` | — | the accessible name of the strip |
| | `orientation` | `horizontal` | `horizontal`, `vertical` |
| `tabs.tab` | `for` | — | the panel's `name` |
| | `href` | — | makes it a link instead |
| | `selected` | `false` | |
| | `icon` | — | any icon name |
| | `icon-variant` | `mini` | `micro`, `mini`, `solid`, `outline` |
| | `as` | resolved from `href` | `button`, `a`, `div` |
| `tabs.panel` | `name` | — | matched by a tab's `for` |
| | `selected` | `false` | |

## The active look lives on the tab

There is no `active` prop threaded from the strip to its children, and no
`:has()` rule reaching down. ARIA already requires the state to live on the tab,
so the styling is `aria-selected:` and `aria-[current=page]:` variants there —
and the siblings recede because muted is their resting state, not because
anything dims them.

## What the suite does not cover

The keyboard behaviour above is not verified by the test suite. There is no
JavaScript test infrastructure in this package, so what the tests can check is
that menus and tabs still share one walker and that the behaviour is registered
— not that <kbd>→</kbd> moves. Arrow keys, roving focus, the panel swap and
activation-follows-focus are checked by hand in the workbench.

## Folding

Tier A — `@blaze(fold: true)` on all three files, `safe: ['label']` on the strip
and `safe: ['for']` on the tab.

`selected` drives `aria-selected`, `tabindex` and `aria-current`, so it branches
and is not safe: a strip whose selection comes from the current route does not
fold. That is a handful of components on a page, and it is the cost of the state
living where ARIA needs it.

See [Folding](../folding.md).
