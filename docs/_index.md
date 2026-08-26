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
[Folding](folding.md) for the one rule that matters.

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
