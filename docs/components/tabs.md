# Tabs

@docs('preview', name: 'tabs')

## Two modes, and the difference is not cosmetic

A tab that has an `href` is a link: it renders an `<a>`, carries
`aria-current="page"`, and needs no JavaScript at all.

```blade
<x-shape::tabs as="nav" label="Settings">
    <x-shape::tabs.tab href="/settings/general" :selected="request()->is('settings/general')">General</x-shape::tabs.tab>
    <x-shape::tabs.tab href="/settings/billing" :selected="request()->is('settings/billing')">Billing</x-shape::tabs.tab>
</x-shape::tabs>
```

Note the `as="nav"` on the strip. A row of navigation links is not a tablist:
`role="tablist"` claims that arrow keys move between these, that exactly one is
selected, and that each controls a panel in this document — three claims that
are all false of a link. `as="nav"` renders a `<nav>` with no role, and that is
also what keeps the script from binding arrow keys to something that should
answer to Tab.

| Component | Prop | Default | Values |
| --- | --- | --- | --- |
| `tabs` | `as` | — | `nav` for a strip of links |
| | `label` | — | the accessible name of the strip |
| | `orientation` | `horizontal` | `horizontal`, `vertical` |
| `tabs.tab` | `for` | — | the panel's `name` |
| | `href` | — | makes it a link instead |
| | `selected` | `false` | |
| | `icon` | — | icon name |
| `tabs.panel` | `name` | — | matched by `for` |
| | `selected` | `false` | |

## Keyboard

| Key | |
| --- | --- |
| <kbd>←</kbd> <kbd>→</kbd> | move between tabs, cycling at both ends |
| <kbd>↑</kbd> <kbd>↓</kbd> | the same, when `orientation="vertical"` |
| <kbd>Home</kbd> <kbd>End</kbd> | first and last tab |
| <kbd>Tab</kbd> | leaves the strip |

Activation follows focus: moving to a tab selects it. That is the ARIA pattern's
default and the right one while a panel is markup the browser already has.

Exactly one tab may carry `selected`. It is the strip's single tab stop, and the
arrow keys move within it — a strip where every tab is a tab stop, or none is,
looks fine and is neither.

## Without the script

Every panel is visible and every tab is focusable. The page reads long rather
than broken, which is why tabs — unlike toasts — do not spend the library's one
"needs the script" promise.

`hidden` is the mechanism, and it is deliberately an attribute rather than a
class: the script toggles it, so a panel carrying its own `display` utility
would outrank it and never hide. That is the one thing to know before styling a
panel.

## What the suite does not cover

The keyboard behaviour above is not verified by the test suite. There is no
JavaScript test infrastructure in this package, so what the tests can check is
that menus and tabs still share one walker and that the behaviour is registered
— not that <kbd>→</kbd> moves. Arrow keys, roving focus, the panel swap and
activation-follows-focus are checked by hand in the workbench.

Said out loud here rather than left for a green suite to imply, and the same
sentence applies to focus trapping in the [overlays](../overlays.md).

## The active look lives on the tab

There is no `active` prop threaded from the strip to its children, and no
`:has()` rule reaching down. ARIA already requires the state to live on the tab,
so the styling is `aria-selected:` and `aria-[current=page]:` variants there —
and the siblings recede because muted is their resting state, not because
anything dims them.

## Folding

Tier A — `@blaze(fold: true)` on all three files, `safe: ['label']` on the strip
and `safe: ['for']` on the tab.

`selected` drives `aria-selected`, `tabindex` and `aria-current`, so it branches
and is not safe: a strip whose selection comes from the current route does not
fold. That is a handful of components on a page, and it is the cost of the state
living where ARIA needs it.

See [Folding](../folding.md) and [Data display](../data.md).
