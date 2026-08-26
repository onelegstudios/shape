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
| [`field`](components/field.md) | fold | Label, description and error; the name stated once |
| [`input`](components/input.md) | fold | Composed primitives, and a shorthand for them |
| [`textarea`](components/textarea.md) | fold | The input's chrome, resizing vertically only |
| [`select`](components/select.md) | fold | Native, restyled; options are children |
| [`checkbox`](components/checkbox.md) | fold | Wrapped in its own label |
| [`radio`](components/radio.md) | fold | Grouped by a real fieldset |
| [`switch`](components/switch.md) | fold | A checkbox with `role="switch"`, and no JS |

Every page states the component's Blaze tier and the call sites that keep it on
the fold path. [Forms](forms.md) covers what is true across all of them: name
resolution, groups, and the one hole cut in the fold for validation messages.

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
