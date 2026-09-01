---
name: shape-development
description: >
    Configure and apply the Shape package in Laravel applications.
license: MIT
metadata:
    author: Oneleggedswede
---

# Shape

Use this skill when a Laravel application needs to integrate the Shape package —
a Blade component library whose components are anonymous Blade files, annotated
so that [Blaze](https://github.com/livewire/blaze) folds them into the calling
template at compile time.

## Primary Goal

- apply the `onelegstudios/laravel-shape` package's public API in the smallest correct way
- write call sites that stay on the fold path, because that is a property of the
  application's templates and not only of the package

## Workflow

### 1. Inspect the Laravel app context

- confirm the app is a Laravel project with `onelegstudios/laravel-shape` installed
- check whether `livewire/blaze` is installed; Shape works without it, and folds with it
- check `resources/css/app.css` for the token import and `resources/js/app.js` for the script

### 2. Install, if it is not installed

```bash
composer require onelegstudios/laravel-shape
php artisan shape:install
```

`shape:install` adds the two lines the package needs:

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape.css";
```

```js
import shape from '../../vendor/onelegstudios/laravel-shape/resources/js/shape.js'

shape()   // or, with Alpine: Alpine.plugin(shape)
```

There is no config to publish before the package works, no compiled CSS, no
migrations, no routes and no translations. The token file declares
`@source "../views"`, so the app's own Tailwind build scans the package's Blade.

### 3. Use the components

Every component is `<x-shape::name>`. Props take scale keys, never raw values:
`size="lg"`, not `size="18px"`; `color="danger"`, not `color="#b91c1c"`.

| Component | Tier | Key props |
| --- | --- | --- |
| `button` | fold | `variant` (outline\|primary\|subtle\|ghost), `color`, `size`, `icon`, `icon-trailing`, `icon-size`, `square`, `as` |
| `button.element` | fold | `as`, `type` — the element a button renders |
| `icon.<name>` | fold + memo | `size` (xs\|sm\|base), `variant` (outline\|solid — chosen by `size` if unset). Shape's own are the `shape-*` slots |
| `icon` | — | `name` — resolves at runtime, so it cannot fold |
| `heading` | fold | `level` (document hierarchy), `size` (visual hierarchy) |
| `text` | fold | `size`, `variant` (base\|muted\|strong), `as` |
| `card` / `card.header` / `card.footer` | fold | `padding`, `border` |
| `separator` | fold + memo | `orientation`, `label` |
| `badge` | fold + memo | `label`, `color`, `variant`, `size`, `icon` |
| `empty` | fold | `heading`, `description`, `icon` |
| `field` | fold | `field-name`, `as` — wraps a control with its label, description and error |
| `label` / `description` / `error` | fold | `for` / `for` / `name`, `bag` |
| `input` | fold | `label`, `description`, `type`, `size`, `id` |
| `textarea` | fold | `label`, `description`, `rows`, `size` |
| `select` / `select.option` | fold | `label`, `placeholder`, `size` / `label`, `value` |
| `checkbox` / `radio` / `switch` | fold | `label`, `description`, `value`, `color` |
| `modal` | fold | `name` (required), `heading`, `description`, `size`, `dismissible` |
| `drawer` | fold | `name` (required), `side`, `heading`, `description`, `size` |
| `dropdown` / `dropdown.trigger` / `dropdown.item` | fold | `name`, `placement` / `for` / `icon`, `color`, `as` |
| `popover` / `popover.trigger` | fold | `name`, `placement`, `padding` / `for` |
| `tooltip` | fold | `name`, `text`, `placement` |
| `overlay.trigger` / `overlay.close` / `overlay.footer` | fold | `for` |
| `alert` | fold | `color`, `heading`, `icon`, `dismissible` |
| `toast` | fold | `color`, `heading`, `description`, `dismissible` |
| `toaster` | compile | `position` — put one in the layout |
| `confirm` | fold | `name`, `heading`, `message`, `accept`, `cancel` — put one in the layout |
| `progress` | fold | `value`, `max`, `indeterminate`, `size`, `color`, `label` |
| `table` (+ `head`, `body`, `row`, `heading`, `cell`) | fold | `empty*` on the table; `value`, `align` on the cell |
| `list` / `list.item` | fold | `as`, `empty*` |
| `pagination` | compile | `paginator`, `simple` |
| `stat` | fold + memo | `value`, `label`, `description`, `delta`, `trend` |
| `avatar` / `avatar.group` | fold + memo | `src`, `initials`, `alt`, `size` |
| `tabs` / `tabs.tab` / `tabs.panel` | fold | `as`, `orientation` / `for`, `selected`, `icon` / `name` |

### 4. Keep the call site foldable

A prop that drives a `match` inside a component must be **static** at the call
site for that component to fold. A dynamically bound one still renders — it just
falls back to the compiled path.

```blade
{{-- Folds. --}}
<x-shape::button variant="primary">Save</x-shape::button>

{{-- Does not fold: `variant` selects a match arm. --}}
<x-shape::button :variant="$isPrimary ? 'primary' : 'outline'">Save</x-shape::button>

{{-- Folds: `color` is declared safe on the button, so it may be dynamic. --}}
<x-shape::button variant="primary" :color="$destructive ? 'danger' : null">Delete</x-shape::button>
```

Inside a loop this is the difference that matters. Prefer the prop-first,
self-closing form for anything that repeats per row — a self-closing call site is
also the only form Blaze will memoize:

```blade
<x-shape::table.cell :value="$invoice->number" />   {{-- folds --}}
<x-shape::badge label="Paid" color="success" />     {{-- folds and memoizes --}}
<x-shape::icon.bell />                              {{-- folds; <x-shape::icon :name="$n" /> cannot --}}
```

### 5. Feedback from the server

```blade
{{-- once, in the layout --}}
<x-shape::toaster />
<x-shape::confirm />
```

```php
use Onelegstudios\Shape\Facades\Shape;

Shape::toast()->success('Invoice sent')->description('A copy went to billing.')->send();

Shape::confirm('Delete project?')->accept('Delete')->color('danger')->then('deleteProject')->send();
```

`send()` dispatches a browser event through Livewire when there is a Livewire
request to ride on, and flashes to the session otherwise. `then()` names a window
event, which `#[On]` already listens for — there is no Livewire component to
register and no Livewire dependency in the package.

### 6. Customise, in this order

1. **Tokens** — redeclare Shape's `@theme` values in the app's own stylesheet.
2. **Utilities** — pass any Tailwind class; Shape's defaults carry zero
   specificity and yield without `!important`.
3. **Eject** — `php artisan shape:eject modal` copies the component and
   everything it composes into `shape.components_path`, where it resolves ahead
   of the packaged one.

```bash
php artisan shape:eject modal      # + heading, text, overlay, button, icon
php artisan shape:eject --status   # what has drifted since an upgrade
php artisan shape:doctor           # fold safety of ejected components
php artisan shape:icon bell        # one icon from the configured set
php artisan shape:icon --all
php artisan shape:icon --replace   # regenerate Shape's own icons in that set
php artisan shape:icon --all --from=./resources/svg
```

A run of `shape:icon` or `shape:eject` that writes anything clears the compiled
views and says so, because a generated component is a file and a compiled view is
a cached answer about a file — leaving the second behind serves stale markup, or
markup inlined from a component that no longer exists, without raising an error.

`shape.icon_set` names which of the sets a run reads when `--set` says nothing.
Set it once when the application moves the library onto another set:

```php
'icon_set' => 'lucide',
```

Which set is yours is true of the application, not of a run: a `--set` forgotten
on one run writes a Heroicon into a directory of Lucide drawings, under a slot
name that says nothing about who drew it. `--set` stays the override for a
one-off, such as reading a supplementary set.

Each set in `shape.icon_sets` declares the repository that draws it (`repo`,
`ref`, `path`), so `shape:icon` fetches it and caches it under
`storage/framework/shape/icons`. Nothing needs cloning first, and nothing is read
at run time — a generated component is a Blade file with the drawing baked in.

Five sets ship, each answering every slot: `heroicons` (the default),
`lucide`, `tabler`, `phosphor` and `bootstrap-icons`. Publish the config to add
another; the four beside `heroicons` are the layouts to copy from — one flat
directory, a directory per style, a style named in both the directory and the
filename, and a style marked inside one flat directory.

A set's `styles` gives each cell a path with the name in it, so a directory
layout and a filename convention are one declaration. `{name}` is the whole name.
`{head}` and `{tail}` are the name split at its last hyphen and only appear
together, for a set that puts its marker inside a name rather than on the end. A
cell may hold a list instead of one path, tried in order, and the first with a
file behind it wins:

```php
'bootstrap-icons' => [
    'styles' => [
        'outline' => ['base' => '{name}.svg'],
        // `check-circle-fill.svg`, but `person-fill-x.svg` — Bootstrap hangs a
        // badge off a glyph and fills the glyph, not the badge.
        'solid' => ['base' => ['{name}-fill.svg', '{head}-fill-{tail}.svg']],
    ],
],
```

`--all` reads those patterns backwards to decide what a listed file is called, so
`person-fill-x.svg` lands in the solid cell of `person-x` instead of arriving as
an icon named for its own marker. A file no pattern accounts for is skipped.

- `--from` reads a local directory instead, and still wins when given; use it for
  a designer's folder or a set with no upstream.
- `--ref` reads a different branch, tag or commit than the set declares.
- `--offline` works only from what has already been fetched and fails by name
  rather than reaching for the network — use it in CI.
- `--status` reports which icons have been redrawn upstream since they were
  generated, from the `shape-icons.json` written beside them.

`--replace` generates Shape's icon **slots** — the icons it resolves in
components of its own. A slot is named for its role, not for a vendor's
spelling: `shape-close` is the dismiss glyph, `shape-warning` is what a `warning`
tone reaches for. The list is declared in `shape.icon_slots`, and every set says
which of its drawings fills each one, in `slots`:

```php
'lucide' => [
    'slots' => [
        'shape-close' => 'x',
        'shape-warning' => 'triangle-alert',
        'shape-loading' => 'loader-circle',
    ],
],
```

Call sites never change: an icon is `<x-shape::icon.shape-close />` whichever set
drew it, and `triangle-alert` stays free to generate under its own name. To
repoint one slot, publish the config, change its entry, and run
`php artisan shape:icon shape-warning --force`. To draw one by hand,
write `resources/views/shape/icon/shape-warning.blade.php` — `components_path`
resolves first and the generator will not overwrite it without `--force`, but
copy the `@blaze(fold: true, memo: true)` front matter off a generated file or it
silently stops folding.

`shape-loading` is **packaged**: Shape ships its own spinner, a set may name a
drawing to shadow it, and a set with nothing that reads as a loader says `null`.
Heroicons says `null` — `arrow-path` is a circular arrow, not a loader.

The slots are not a catalogue to pick from — they are what Shape keeps level with
your set. The package's own README and previews name `shape-*` icons because
those are the drawings it ships and its documentation has to render for a reader
who has generated nothing; an application's call sites should not copy that.
Generate your own icons for everything else, under their own names:

```bash
php artisan shape:icon bell trash
```

Those names are free. The package ships `shape-arrow-right`, `shape-plus` and
`shape-trash` for its own README and previews, prefixed precisely so `trash` and
`plus` are not taken; nothing resolves them and `--replace` leaves them alone.
`--replace` does not touch what you generate either, so regenerate it yourself
when you swap sets — `shape:doctor` lists what it finds outside the slots for
that reason.

A set that declares a `namespace` in `shape.icon_sets` is written into a
subdirectory instead, for when two sets spell the same name:
`icon/lucide/bell.blade.php` is `<x-shape::icon.lucide.bell />`, and it folds
exactly as a top-level icon does. It belongs on the set rather than on the
command line — where a set lives is true of the set, and a `--namespace` flag is
remembered only for the run it is typed on, so the next run without it writes a
second copy flat. The flag survives as a per-run override, and `--namespace=`
says a run is flat about a set that normally isn't, which is what `--replace`
needs. Keep the primary set flat so a call site has one spelling for an icon
whichever set drew it.

Run `shape:doctor` in CI once anything has been ejected: it exits non-zero when a
folded component reads `auth()`, `session()`, `request()`, `config()`, `$errors`,
`now()`, `@csrf` or a translation helper — each of which is resolved once at
compile time and then served to every visitor. It also counts how many of
Shape's slots a replaced set fills, since a slot missed falls back to the
packaged Heroicon and renders in the wrong set without complaint. Three states,
and only the last is a finding: **generated** from your set, **packaged** by
Shape, **missing**.

## Rules, References, and Templates

Read before executing:

- `vendor/onelegstudios/laravel-shape/docs/_index.md` — every component, with its tier
- `vendor/onelegstudios/laravel-shape/docs/folding.md` — what keeps a call site on the fold path
- `vendor/onelegstudios/laravel-shape/docs/forms.md` — the field shorthand and name resolution
- `vendor/onelegstudios/laravel-shape/docs/tooling.md` — the four commands
- `vendor/onelegstudios/laravel-shape/resources/registry.json` — components, files, dependencies, tiers

## Examples

- A form field in one call site: `<x-shape::input label="Email" wire:model="email" />`
  renders the label, the control, the description and the validation error, with
  `for`, `id` and `aria-describedby` wired from the control's name.
- A table of rows: wrap `<x-shape::table>` around `table.head` / `table.body`, and
  write cells as `<x-shape::table.cell :value="$row->total" />` so each one folds.
- A destructive action: `<x-shape::overlay.trigger for="delete-project" color="danger">`
  beside `<x-shape::modal name="delete-project" heading="Delete project">`.

## Anti-patterns

- do not write a PHP class for a Shape component; Blaze does not compile
  class-based components, and a class-backed component forfeits folding entirely
- do not pass raw values where a scale key exists (`size="lg"`, not a pixel value)
- do not bind a variant dynamically inside a loop when a static one will do
- do not call `auth()`, `session()`, `config()`, `__()` or `now()` inside an
  ejected component that is annotated `fold: true`
- do not reach for `<x-shape::icon :name="$name" />` on a hot path; the direct
  `<x-shape::icon.bell />` form is the one that folds and memoizes
- do not put a `shape-*` name in an application's own markup; those fourteen are
  Shape's slots, and an icon of your own is one you generated
  (`php artisan shape:icon bell`), under the name its set uses
- name a `size` on an icon and leave `variant` alone unless the style is the
  point; the small sizes are drawn solid because a stroke does not read at 16px,
  and a call site that names only a size works with any icon set
- do not publish the stylesheet to change colours; redeclare the tokens instead
