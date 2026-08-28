# Shape

Shape is a Blade component library for Livewire applications. Components are
anonymous Blade files, styled with Tailwind, and annotated so that
[Blaze](https://github.com/livewire/blaze) can fold them into their parent
templates at compile time.

## Installation

```bash
composer require onelegstudios/laravel-shape
```

Import the design tokens after Tailwind in your application stylesheet:

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape.css";
```

The token file declares `@source "../views"`, so your Tailwind build scans the
package's components without any further configuration.

## Performance

Shape is designed to be folded. Install Blaze and the components are pre-rendered
into their parent templates during compilation:

```bash
composer require livewire/blaze
```

Nothing else is required — Shape registers its own views with Blaze, and every
component already declares the strategy it is safe to use. Without Blaze
installed the components render normally; the `@blaze` annotations compile away.

Folding is not free of obligations at the call site. See
[Folding](folding.md) for the rules that matter.

## Components

| Component | Tier | |
| --- | --- | --- |
| [`button`](components/button.md) | fold | Hierarchy and semantics as separate props |
| [`icon`](components/icon.md) | fold + memo | One drawing per size, never scaled |
| [`heading`](components/heading.md) | fold | Document hierarchy separate from visual hierarchy |
| [`text`](components/text.md) | fold | Muted reads the surface, not a global grey |
| [`card`](components/card.md) | fold | A surface, with no border by default |
| [`separator`](components/separator.md) | fold + memo | Reach for it after spacing |
| [`badge`](components/badge.md) | fold + memo | Prop-first, so it memoizes |
| [`empty`](components/empty.md) | fold | The state most applications forget |
| [`field`](components/field.md) | fold | What a control assembles when given a `label` |
| [`input`](components/input.md) | fold | Composed primitives, and a shorthand for them |
| [`textarea`](components/textarea.md) | fold | The input's chrome, resizing vertically only |
| [`select`](components/select.md) | fold | Native, restyled; options are children |
| [`checkbox`](components/checkbox.md) | fold | Wrapped in its own label |
| [`radio`](components/radio.md) | fold | Grouped by a real fieldset |
| [`switch`](components/switch.md) | fold | A checkbox with `role="switch"`, and no JS |
| [`modal`](components/modal.md) | fold | A `<dialog>`, so the focus trap is the platform's |
| [`drawer`](components/drawer.md) | fold | The modal, pinned to an edge |
| [`dropdown`](components/dropdown.md) | fold | A popover with menu semantics |
| [`popover`](components/popover.md) | fold | Anchored, in the top layer, no z-index |
| [`tooltip`](components/tooltip.md) | fold | For the label of an icon button, and nothing load-bearing |
| [`alert`](components/alert.md) | fold | A message that stays on the page |
| [`toast`](components/toast.md) | fold | Markup in Blade, cloned by the script |
| [`toaster`](components/toast.md) | compile | The top layer, so no z-index and no modal on top of it |
| [`confirm`](components/confirm.md) | fold | The modal, filled in from the server |
| [`progress`](components/progress.md) | fold | Native, so a dynamic value still folds |

Every page states the component's Blaze tier and the call sites that keep it on
the fold path. [Forms](forms.md) starts with the one call site that writes a
whole field, then covers groups, name resolution, and the single hole cut in the
fold for validation messages. [Overlays](overlays.md) covers what the five
overlays share: the script, the naming convention, what the platform supplies
and what it doesn't. [Feedback](feedback.md) covers the one direction the rest of
the library never travels — server to browser — and why that needs no Livewire
component to do it.

## JavaScript

One file, for the overlays and the feedback channel:

```js
import shape from '../../vendor/onelegstudios/laravel-shape/resources/js/shape.js'

shape()   // or, if you use Alpine: Alpine.plugin(shape)
```

It imports nothing and depends on nothing — not Alpine, and not Livewire.
Everything else in the library — including switches, checkboxes and the select —
is markup and CSS.

Overlays render and open without it, with three small exceptions listed in
[Overlays](overlays.md#the-script). Toasts are the one thing that does not work
without it, and [Feedback](feedback.md#toasts-need-the-script) says why that
trade was taken.

## Customising

Customisation escalates in three steps:

1. **Tokens.** Redeclare Shape's `@theme` values in your own stylesheet.
2. **Utilities.** Pass any Tailwind class to any component; Shape's own defaults
   carry zero specificity and yield to it.
3. **Eject.** Publish a component into your application and own it outright:

```bash
php artisan vendor:publish --tag="laravel-shape-components"
```

Ejected components resolve ahead of the packaged ones. The location is
configurable with `shape.components_path`.
