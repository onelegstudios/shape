# Release Notes

## [Unreleased](https://github.com/onelegstudios/shape/compare/v0.1.0...HEAD)

## [v0.1.0](https://github.com/onelegstudios/shape/compare/v0.1.0...v0.1.0) - 2026-09-06

First public pre-release of Shape.

Shape is a Blade component library for Livewire applications. Every component is
an anonymous Blade file, styled with Tailwind, and annotated so that
[Blaze](https://github.com/livewire/blaze) can fold it into its parent template at
compile time. There is no compiled CSS, no config to publish before the package
works, and no asset to build.

```bash
composer require onelegstudios/shape
php artisan shape:install

```
### Components

Thirty components, each documented with a rendered example of every visual prop:

- **Foundation** — button, icon, heading, text, card, separator
- **Forms** — field, input, textarea, select, checkbox, radio, switch, with the
  field name stated once and carried as context
- **Data display** — table, list, stat, badge, avatar, progress, pagination,
  empty
- **Navigation** — tabs
- **Overlays** — modal, drawer, dropdown, popover, tooltip
- **Feedback** — alert, toast, confirm

`variant` is hierarchy and `tone` is semantics, kept as separate props so a
destructive action can stay quiet until the moment it matters. Every default
carries zero specificity, so an application's own classes win without
`!important`.

### Overlays on the platform's own primitives

Modal and drawer are a `<dialog>`; dropdown, popover and tooltip use the
`popover` attribute. The focus trap, the top layer, Escape, light dismiss and the
scrim are the browser's. What is left for a script is one file that imports
nothing and depends on nothing — not Alpine, and not Livewire.

### Feedback without a Livewire component

`Shape::toast()` and `Shape::confirm()` dispatch a browser event through Livewire
when there is a Livewire request to ride on, and flash to the session otherwise —
both arriving as the same event, so one code path builds the toast either way.
`then()` names a window event, which is what `#[On]` already listens for. Livewire
is not a dependency.

### Theming

A neutral ramp with a temperature, a brand and an accent, four state colours, one
radius decision, and a per-surface foreground contract — that is the whole theme
layer, because Tailwind's spacing, type and shadow scales are already the right
ones. Every ramp is aliased rather than copied, so retinting Tailwind's colours
retints Shape with them.

An optional seed stylesheet derives the brand and the neutrals from a single
colour. Each step pins its own OKLCH lightness and takes only hue and chroma from
the seed, which keeps the contrast guarantee hue-independent — verified by
rendering 96 seeds and measuring the result, not by modelling it.

### Commands

```bash
php artisan shape:install          # the tokens, the script, and which set
php artisan shape:doctor           # the mistake that costs a fold, silently

php artisan shape:eject modal      # a component, and everything it composes
php artisan shape:eject:all        # the whole library at once
php artisan shape:eject:status     # what has moved since you copied it

php artisan shape:icon bell trash  # the icons you ask for, by name
php artisan shape:icon:all         # every icon the set draws
php artisan shape:icon:replace     # the fourteen Shape draws itself
php artisan shape:icon:status      # what has been redrawn upstream

```
`shape:eject` follows a dependency graph and records what the package held at the
moment it copied each file, so `shape:eject:status` can tell a component you
edited from one the package has changed underneath you.

`shape:icon` generates icon components from a set of SVGs, writing the resolved
upstream commit into every file. Heroicons, Lucide, Tabler, Phosphor, Bootstrap
Icons, Remix Icon and Material Symbols ship as sets, and any directory of SVGs is
one. The fourteen icons Shape draws itself are named for the role they play —
`shape-close`, `shape-warning` — so `shape:icon:replace --set=lucide` redraws the
library in another set without a call site changing.

`shape:doctor` checks ejected components for request-scoped state. A folded
component is pre-rendered while Blade compiles, so `auth()`, `session()`,
`config()` or a translation inside one is resolved once and then served to
everybody — a failure with no stack trace and no symptom in development. It exits
non-zero, so it can sit in CI.

### Documentation

The docs live in [docs/](https://github.com/onelegstudios/shape/tree/main/docs) as
markdown, committed alongside the code they describe, and the workbench serves
them next to a gallery of every component. Each example is a single file that both
renders and prints, and a test asserts every one of them still compiles — so a
renamed prop cannot leave a page describing the old one.

### Requirements

PHP 8.3, 8.4 or 8.5 and Laravel 12 or 13, tested on Ubuntu and Windows across the
matrix on both prefer-stable and prefer-lowest. Blaze is optional: without it the
components render normally and the annotations compile away.

### Pre-release

This is 0.x. The component APIs may still change before 1.0.
