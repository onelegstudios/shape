<div align="center">
    <h1>Shape</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/onelegstudios/shape"><img src="https://img.shields.io/packagist/v/onelegstudios/shape.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/onelegstudios/shape"><img src="https://img.shields.io/packagist/php-v/onelegstudios/shape.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/onelegstudios/shape"><img src="https://badge.laravel.cloud/badge/onelegstudios/shape?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/onelegstudios/shape/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/onelegstudios/shape/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/onelegstudios/shape"><img src="https://img.shields.io/packagist/dt/onelegstudios/shape.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Shape the interface. Predictable Blade UI components for Livewire applications.
Components are anonymous Blade files, styled with Tailwind, and annotated so that
[Blaze](https://github.com/livewire/blaze) can fold them into their parent templates
at compile time.

## Installation

You can install the package via Composer:

```bash
composer require onelegstudios/shape
php artisan shape:install
```

`shape:install` imports the design tokens into your stylesheet, registers the
script, and asks which icon set the library should be drawn in. Enter is
Heroicons, which the fourteen icons Shape resolves already ship drawn in, so the
default answer publishes nothing: that is the whole installation, with no config
to publish before the package works and no asset to build. Both lines are shown
below if you would rather write them yourself.

Naming another set instead generates those fourteen from it and records the
choice in a published `config/shape.php`, so that no later run has to be told
again. `--icons=lucide` answers in advance and `--no-interaction` skips the
question — see [docs/components/icon.md](docs/components/icon.md#using-a-different-set).

There are no migrations, no routes and no translations to publish either. A
folded component resolves a translation once at compile time and serves that one
locale to everybody, so the library has none to ship.

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="shape"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="shape-config"
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="shape-views"
```

### Publishing the Stylesheet

The tokens are normally imported from `vendor/`, which keeps your overrides
authoritative and leaves nothing to rebuild on an upgrade. Publish them only if
you intend to own the token layer outright:

```bash
php artisan vendor:publish --tag="shape-css"
```

### Publishing the JavaScript

```bash
php artisan vendor:publish --tag="shape-js"
```

### Publishing the Components

Ejects every component into `resources/views/shape`, where they resolve ahead of
the packaged ones:

```bash
php artisan vendor:publish --tag="shape-components"
```

`php artisan shape:eject modal` does the same for one component and everything it
composes, which is usually what you want. See [Commands](#commands).

## Usage

Import the design tokens after Tailwind in your application stylesheet:

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/shape/resources/css/shape.css";
```

The token file declares `@source "../views"`, so your Tailwind build scans the
package's components without further configuration. Shape ships no compiled CSS.

```blade
<x-shape::button variant="primary" icon="shape-plus">Save changes</x-shape::button>

<x-shape::button variant="subtle" tone="danger" icon="shape-trash">Delete</x-shape::button>

<x-shape::button as="a" href="/settings" icon-trailing="shape-arrow-right">Settings</x-shape::button>

<x-shape::icon.shape-plus size="sm" />
```

`variant` is hierarchy — where an action sits in the pyramid of importance.
`tone` is semantics. They are separate props so that a destructive action can
stay quiet until the moment it matters.

Every default Shape sets carries zero specificity, so your own classes win
without `!important`:

```blade
<x-shape::button class="rounded-full w-full">Continue</x-shape::button>
```

### Theming

Shape's theme layer is small on purpose. Tailwind's spacing, type and shadow
scales are already the right ones, so the package adds only what Tailwind has no
opinion about: a neutral ramp with a temperature, a brand and an accent, four
state colours, one radius decision, and a per-surface foreground contract. Every ramp is aliased
rather than copied, so retinting Tailwind's own colours retints Shape with them.

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/shape/resources/css/shape.css";

@theme {
    --color-shape-brand-700: oklch(45.7% 0.24 277.023);
    --radius-shape: 0.25rem;
}
```

Or derive the brand and the neutrals from one colour, with an optional second
stylesheet:

```css
@import "../../vendor/onelegstudios/shape/resources/css/shape-seed.css";

:root {
    --shape-seed: oklch(52% 0.16 300);
}
```

Each step pins its OKLCH lightness and takes only hue and chroma from the seed,
which is what keeps the contrast guarantee hue-independent — verified by
rendering 96 seeds and measuring the result, not by modelling it. See
[docs/theming.md](docs/theming.md).

### Performance

```bash
composer require livewire/blaze
```

That is the whole setup. Shape registers its own views with Blaze and every
component already declares the strategy it is safe to use. Without Blaze
installed the components render normally and the annotations compile away.

One rule applies at the call site: a prop that drives a `match` inside a
component has to be static for that component to fold.

```blade
{{-- Folds. --}}
<x-shape::button variant="primary">Save</x-shape::button>

{{-- Falls back to the compiled path — still fast, just not folded. --}}
<x-shape::button :variant="$isPrimary ? 'primary' : 'outline'">Save</x-shape::button>
```

See [docs/folding.md](docs/folding.md) for the full explanation.

### JavaScript

One file, for the overlays, the feedback channel, and the keyboard behaviour of
a tab strip:

```js
import shape from "../../vendor/onelegstudios/shape/resources/js/shape.js";

shape(); // or, if your application uses Alpine: Alpine.plugin(shape)
```

It imports nothing and depends on nothing — not Alpine, and not Livewire. Modal
and drawer are a `<dialog>`; dropdown, popover and tooltip use the `popover`
attribute. The focus trap, the top layer, Escape, light dismiss and the scrim are
the platform's, so what is left for a script is small. Tabs are the one component
outside the overlays that reaches for it, and only in their panel-switching mode.
See [docs/overlays.md](docs/overlays.md).

```blade
<x-shape::overlay.trigger for="delete-project" tone="danger" variant="subtle">
    Delete project
</x-shape::overlay.trigger>

<x-shape::modal name="delete-project" heading="Delete project">
    <x-shape::text size="sm">This cannot be undone.</x-shape::text>
</x-shape::modal>
```

### Feedback

Put a toaster and a confirm dialog in your layout, and the server can reach the
browser:

```blade
<x-shape::toaster />
<x-shape::confirm />
```

```php
Shape::toast()->success('Invoice sent')->send();

Shape::confirm('Delete project?')->accept('Delete')->tone('danger')->then('deleteProject')->send();
```

`send()` dispatches a browser event through Livewire when there is a Livewire
request to ride on, and flashes to the session otherwise — both arriving as the
same event, so one code path builds the toast either way. There is no Livewire
component here and no Livewire in `composer.json`; `then()` names a window event,
which is what `#[On]` already listens for. See
[docs/feedback.md](docs/feedback.md).

## Commands

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

Ejecting and generating each come in three or four, because which files a run
writes is what separates them — an argument the command is named for rather
than a mode flag it takes.

`shape:eject` follows a dependency graph — a modal arrives with the heading, the
text and the close button inside it — and records what the package held at the
moment it copied each file, so `shape:eject:status` can tell a component you
edited from one the package has changed underneath you.

`shape:icon` generates icon components from a set of SVGs. A set declares the
repository that draws it, so the command fetches it, caches it, and writes the
resolved commit into every file — which is what makes _Regenerate; don't
hand-edit_ an instruction you can follow rather than one that asks for a
checkout nobody mentioned. `--from` reads a local directory instead, and
`shape:icon:status` reports which drawings have moved upstream since.

A name may be spelled the way the component is — `php artisan shape:icon
lucide.bell` writes what `<x-shape::icon.lucide.bell />` resolves — which is
`--set=lucide` said on the name rather than on the run, and the only form that
asks two sets for something at once.

Fourteen of those components are the ones Shape's own components draw — the
_slots_, named `shape-close` and `shape-warning` for the role they play rather
than for whichever vendor drew them. `shape:icon:replace --set=lucide`
regenerates that fourteen from another set without a call site changing.
Heroicons, Lucide, Tabler, Phosphor, Bootstrap Icons, Remix Icon and Material
Symbols ship as sets, and any directory of SVGs is one. A set other than the one
`shape.icon_set` names is supplementary — read for what the library's set has not
got, and written into a subdirectory named after it, where it cannot land on top
of the icons the application is wearing: `<x-shape::icon.lucide.bell />`. Every
other icon your interface needs is one you generate, under whatever the set calls
it.

`shape:doctor` checks ejected components for request-scoped state. A folded
component is pre-rendered while Blade compiles, so `auth()`, `session()`,
`config()` or a translation inside one is resolved once and then served to
everybody — a failure with no stack trace and no symptom in development. It
exits non-zero, so it can sit in CI.

See [docs/tooling.md](docs/tooling.md).

## Documentation

Documentation lives in [docs/](docs) as markdown, committed alongside the code it
describes.

## Previewing components and reading the docs

```bash
npm install && npm run preview && npm run docs
composer serve
```

The workbench serves two things. A gallery of every component at `/`, and the
documentation at `/docs` — the markdown in [docs/](docs), rendered by
[laradocs](https://laradocs.dev), with the components rendering inside it.

Every `@docs('preview', name: '…')` in a page renders `docs/previews/<name>.blade.php`
and prints that same file underneath as the example. One file for the picture and
for the code, and a test asserts every one of them still compiles — so a prop
that gets renamed cannot leave a page describing the old one.

`npm run preview` and `npm run docs` compile the two Tailwind stylesheets those
pages use. The package itself ships no CSS.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Shape! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Credits

- [Oneleggedswede](https://github.com/onelegstudios)
- [All Contributors](../../contributors)

## License

Shape is open-sourced software licensed under the [MIT license](LICENSE.md).
