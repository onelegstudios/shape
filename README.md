<div align="center">
    <h1>Shape</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/onelegstudios/laravel-shape"><img src="https://img.shields.io/packagist/v/onelegstudios/laravel-shape.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/onelegstudios/laravel-shape"><img src="https://img.shields.io/packagist/php-v/onelegstudios/laravel-shape.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/onelegstudios/laravel-shape"><img src="https://badge.laravel.cloud/badge/onelegstudios/laravel-shape?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/onelegstudios/laravel-shape/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/onelegstudios/laravel-shape/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/onelegstudios/laravel-shape"><img src="https://img.shields.io/packagist/dt/onelegstudios/laravel-shape.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A Blade component library for Livewire applications. Components are anonymous
Blade files, styled with Tailwind, and annotated so that
[Blaze](https://github.com/livewire/blaze) folds them into their parent
templates at compile time.

## Installation

You can install the package via Composer:

```bash
composer require onelegstudios/laravel-shape
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="laravel-shape"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="laravel-shape-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="laravel-shape-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="laravel-shape-views"
```

### Publishing the JavaScript

```bash
php artisan vendor:publish --tag="laravel-shape-js"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="laravel-shape-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="laravel-shape-assets"
```

### Publishing the Components

Ejects every component into `resources/views/shape`, where they resolve ahead of
the packaged ones:

```bash
php artisan vendor:publish --tag="laravel-shape-components"
```

## Usage

Import the design tokens after Tailwind in your application stylesheet:

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape.css";
```

The token file declares `@source "../views"`, so your Tailwind build scans the
package's components without further configuration. Shape ships no compiled CSS.

```blade
<x-shape::button variant="primary" icon="check">Save changes</x-shape::button>

<x-shape::button variant="subtle" color="danger" icon="trash">Delete</x-shape::button>

<x-shape::button as="a" href="/settings" icon-trailing="arrow-right">Settings</x-shape::button>

<x-shape::icon.check-circle variant="mini" />
```

`variant` is hierarchy — where an action sits in the pyramid of importance.
`color` is semantics. They are separate props so that a destructive action can
stay quiet until the moment it matters.

Every default Shape sets carries zero specificity, so your own classes win
without `!important`:

```blade
<x-shape::button class="rounded-full w-full">Continue</x-shape::button>
```

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
import shape from '../../vendor/onelegstudios/laravel-shape/resources/js/shape.js'

shape()   // or, if your application uses Alpine: Alpine.plugin(shape)
```

It imports nothing and depends on nothing — not Alpine, and not Livewire. Modal
and drawer are a `<dialog>`; dropdown, popover and tooltip use the `popover`
attribute. The focus trap, the top layer, Escape, light dismiss and the scrim are
the platform's, so what is left for a script is small. Tabs are the one component
outside the overlays that reaches for it, and only in their panel-switching mode.
See [docs/overlays.md](docs/overlays.md).

```blade
<x-shape::overlay.trigger for="delete-project" color="danger" variant="subtle">
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

Shape::confirm('Delete project?')->accept('Delete')->color('danger')->then('deleteProject')->send();
```

`send()` dispatches a browser event through Livewire when there is a Livewire
request to ride on, and flashes to the session otherwise — both arriving as the
same event, so one code path builds the toast either way. There is no Livewire
component here and no Livewire in `composer.json`; `then()` names a window event,
which is what `#[On]` already listens for. See
[docs/feedback.md](docs/feedback.md).

## Documentation

Documentation lives in [docs/](docs) as markdown, committed alongside the code it
describes.

## Previewing components

```bash
npm install && npm run preview
composer serve
```

The workbench serves a gallery of every component at `/`. `npm run preview`
compiles the Tailwind stylesheet the gallery inlines; the package itself ships
no CSS.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Shape! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Credits

- [Oneleggedswede](https://github.com/onelegstudios)
- [All Contributors](../../contributors)

## License

Shape is open-sourced software licensed under the [MIT license](LICENSE.md).
