# Overlays

Modal, drawer, dropdown, popover and tooltip are one family. They share a
mechanism, a naming convention and a script, and this page is the part that
would otherwise be repeated on five component pages.

## The script

```bash
php artisan vendor:publish --tag="laravel-shape-js"
```

Or import it straight from the package, which is what the stylesheet does too:

```js
import shape from '../../vendor/onelegstudios/laravel-shape/resources/js/shape.js'

shape()
```

If your application uses Alpine, register it where you register everything else —
it installs as a plugin because that is where consumers look for it:

```js
Alpine.plugin(shape)
```

It does not use Alpine. `resources/js/shape.js` imports nothing, depends on
nothing, and installs a handful of delegated listeners on the document. A test
asserts it stays that way.

Everything renders and opens without it, with three exceptions: Escape closes a
`:dismissible="false"` dialog, tooltips never appear, and arrow keys don't move
between menu items. Nothing is invisible or unreachable without it.

## Naming: `name` on the overlay, `for` on the trigger

```blade
<x-shape::overlay.trigger for="cart">Cart</x-shape::overlay.trigger>
<x-shape::drawer name="cart"> … </x-shape::drawer>
```

`name` is required on every overlay and has no default. A folded component is
pre-rendered once at compile time, so an id generated inside one is generated
once as well and every instance shares it. It must also be a valid HTML id and
CSS identifier — letters, digits, `-` and `_` — because it becomes an anchor
name as well as an id.

Nothing is inherited through `@aware` here. The forms set carries its field
context that way and pays for it: a dynamic `:field-name` takes every child off
the fold path. An overlay's id is not worth that, and `@aware` walks the whole
ancestor stack, which is a hazard worth having exactly once in a library.

## Two triggers, because there are two mechanisms

| Trigger | Opens | How |
| --- | --- | --- |
| `overlay.trigger` | modal, drawer | `command="show-modal" commandfor="…"` |
| `popover.trigger` | popover | `popovertarget="…"` |
| `dropdown.trigger` | dropdown | `popovertarget="…"`, announcing a menu |

`overlay.close` closes a dialog with `command="close"`. All three render a
`button`, so every button prop works on them: variant, colour, size, icons.

## What the platform supplies

| Behaviour | Modal / drawer | Dropdown / popover | Tooltip |
| --- | --- | --- | --- |
| Top layer | `<dialog>` | `popover` | `popover` |
| Focus trap | `showModal()` | not applicable | not applicable |
| Escape | native `cancel` | native | `shape.js` |
| Dismiss on outside click | — | native light dismiss | `shape.js` |
| Scrim | `::backdrop` | — | — |
| Scroll lock | CSS `:has()` | — | — |
| Placement | CSS | CSS anchor positioning | CSS anchor positioning |
| Return focus to trigger | native | native | not applicable |

`shape.js` covers what is left: the invoker fallback, holding Escape off a
non-dismissible dialog, `aria-expanded`, arrow keys in menus, tooltips, the
anchor-positioning fallback, and the two window events below.

## Opening from the server

```php
$this->dispatch('shape:open', name: 'confirm-delete');
$this->dispatch('shape:close', name: 'confirm-delete');
```

Browser events rather than a Livewire dependency, so the package keeps working in
an application that has no Livewire in it.

## A parent's spacing outranks a component library

`space-y-*` on a parent sets `margin-block-end` on its children. A modal written
inside a spaced section is a child like any other, so it gets that margin — and a
dialog centres itself with auto margins, so it stops being centred:

```blade
<section class="space-y-4">
    <x-shape::button>Delete</x-shape::button>

    {{-- Gets `margin-block-end: 1rem`, and is no longer centred. --}}
    <x-shape::modal name="confirm"> … </x-shape::modal>
</section>
```

Specificity cannot fix this. Cascade layers are ordered *before* specificity is
consulted, so anything in `@layer utilities` beats everything in
`@layer components` no matter how the selector is written. Shape's placement
rules therefore live in a `shape-overlay` layer declared after Tailwind's, which
sorts last and wins.

Only placement lives there. Colour, padding, radius and max-width stay in the
class strings with their `[:where(&)]:` prefixes, where your utilities still win —
that part of the arrangement is unchanged.

If you eject an overlay and write your own placement, put it in that layer too,
or expect the same bug.

## Browser support, stated plainly

- `<dialog>`, `popover` and `::backdrop` are supported everywhere the rest of
  this library is.
- `command` / `commandfor` are recent. `shape.js` falls back for them; a page
  without the script and without invoker support has modal triggers that do
  nothing.
- CSS anchor positioning is not universal yet. `shape.js` positions overlays
  itself where it is missing, reading the same `data-shape-placement` attribute
  the stylesheet reads.
- `@starting-style` and `transition-behavior: allow-discrete` degrade to no
  entry animation.

## What is not covered by tests

Render tests assert the markup, the wiring attributes and the ARIA. Folding is
asserted per call site. What no test in this package covers yet is behaviour that
needs a real browser: focus trapping, focus returning to the trigger, tab
cycling, arrow-key movement, hover intent, and the anchor-positioning fallback.

They are verified by hand in the workbench preview (`composer serve`). Browser
tests are the honest fix and they are not written yet — this note is here so the
gap is recorded rather than assumed away.
