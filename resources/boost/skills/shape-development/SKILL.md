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

- apply the `onelegstudios/shape` package's public API in the smallest correct way
- write call sites that stay on the fold path, because that is a property of the
  application's templates and not only of the package

## Workflow

### 1. Inspect the Laravel app context

- confirm the app is a Laravel project with `onelegstudios/shape` installed
- check whether `livewire/blaze` is installed; Shape works without it, and folds with it
- check `resources/css/app.css` for the token import and `resources/js/app.js` for the script

### 2. Install, if it is not installed

```bash
composer require onelegstudios/shape
php artisan shape:install
```

`shape:install` adds the two lines the package needs, then asks which icon set
the library should be drawn in. Enter is `hero`, which is what the fourteen icons
Shape resolves already ship drawn in; answer `--icons=lucide` (or any set in
`shape.icon_sets`) to generate those fourteen from another set and record the
choice in a published config. Pass `--no-interaction` to skip the question.

The two lines:

```css
@import 'tailwindcss';
@import '../../vendor/onelegstudios/shape/resources/css/shape.css';
```

```js
import shape from '../../vendor/onelegstudios/shape/resources/js/shape.js'

shape()   // or, with Alpine: Alpine.plugin(shape)
```

There is no config to publish before the package works — the icon question is
the one thing that publishes one, and only when it is answered with a set other
than the packaged `hero`. No compiled CSS, no migrations, no routes and no
translations either. The token file declares `@source "../views"`, so the app's
own Tailwind build scans the package's Blade.

### 3. Use the components

Every component is `<x-shape::name>`. Props take scale keys, never raw values:
`size="lg"`, not `size="18px"`; `tone="danger"`, not `tone="#b91c1c"`.

`tone` is one of `neutral`, `brand`, `accent`, `info`, `success`, `warning`,
`danger`. The last four are the states, and the alert, the badge and the toast
each resolve a glyph from them so colour is never the only signal. `brand` and
`accent` are emphasis rather than states and draw no glyph: `brand` is the
product's own ramp, the one an application retints and the one the primary
button, the focus ring and the active tab already use; `accent` is the second
colour, kept for what is worth noticing — a `New` badge, the recommended plan —
and used sparingly. An informational message wants neither: it wants `info`,
which is blue whatever the brand becomes.

| Component | Tier | Key props |
| --- | --- | --- |
| `button` | fold | `variant` (outline\|primary\|subtle\|ghost), `tone`, `size`, `icon`, `icon-trailing`, `icon-size`, `square`, `as` |
| `button.element` | fold | `as`, `type` — the element a button renders |
| `icon.<name>` | fold + memo | `size` (xs\|sm\|base), `variant` (outline\|solid — chosen by `size` if unset). Shape's own are the `shape-*` slots |
| `icon` | — | `name` — resolves at runtime, so it cannot fold |
| `heading` | fold | `level` (document hierarchy), `size` (visual hierarchy) |
| `text` | fold | `size`, `variant` (base\|muted\|strong), `as` |
| `card` / `card.header` / `card.footer` | fold | `padding`, `border` |
| `separator` | fold + memo | `orientation`, `label` |
| `badge` | fold + memo | `label`, `tone`, `variant`, `size`, `icon` |
| `empty` | fold | `heading`, `description`, `icon` |
| `field` | fold | `field-name`, `as` — wraps a control with its label, description and error |
| `label` / `description` / `error` | fold | `for` / `for` / `name`, `bag` |
| `input` | fold | `label`, `description`, `type`, `size`, `id` |
| `textarea` | fold | `label`, `description`, `rows`, `size` |
| `select` / `select.option` | fold | `label`, `placeholder`, `size` / `label`, `value` |
| `checkbox` / `radio` / `switch` | fold | `label`, `description`, `value`, `tone` |
| `modal` | fold | `name` (required), `heading`, `description`, `size`, `dismissible` |
| `drawer` | fold | `name` (required), `side`, `heading`, `description`, `size` |
| `dropdown` / `dropdown.trigger` / `dropdown.item` | fold | `name`, `placement` / `for` / `icon`, `tone`, `as` |
| `popover` / `popover.trigger` | fold | `name`, `placement`, `padding` / `for` |
| `tooltip` | fold | `name`, `text`, `placement` |
| `overlay.trigger` / `overlay.close` / `overlay.footer` | fold | `for` |
| `alert` | fold | `tone`, `variant` (subtle\|outline\|solid\|ghost), `toned`, `border`, `shadow`, `bar` (left\|right\|top\|bottom), `bar-square`, `heading`, `icon`, `icon-size`, `icon-variant`, `icon-placement` (gutter\|inline), `dismissible`, `actions-placement` (sm\|md\|lg\|xl\|2xl\|below\|side — Tailwind container sizes, not viewport breakpoints; default `lg`); `actions` slot |
| `toast` | fold | `tone`, `heading`, `description`, `dismissible` |
| `toaster` | compile | `position` — put one in the layout |
| `confirm` | fold | `name`, `heading`, `message`, `accept`, `cancel` — put one in the layout |
| `progress` | fold | `value`, `max`, `indeterminate`, `size`, `tone`, `label` |
| `table` (+ `head`, `body`, `row`, `heading`, `cell`) | fold | `empty*` on the table; `value`, `align` on the cell |
| `list` / `list.item` | fold | `as`, `empty*` |
| `pagination` | compile | `paginator`, `simple` |
| `stat` | fold + memo | `value`, `label`, `description`, `delta`, `trend` |
| `avatar` / `avatar.group` | fold + memo | `src`, `icon`, `icon-variant` (default `solid`), `initials`, `alt`, `size`, `tone`, `variant` (subtle\|solid\|outline), `square`, `badge` (bare for a dot, otherwise its text), `badge-tone`, `badge-position` (bottom-right\|bottom-left\|top-right\|top-left) |
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

{{-- Folds: `tone` is declared safe on the button, so it may be dynamic. --}}
<x-shape::button variant="primary" :tone="$destructive ? 'danger' : null">Delete</x-shape::button>
```

Inside a loop this is the difference that matters. Prefer the prop-first,
self-closing form for anything that repeats per row — a self-closing call site is
also the only form Blaze will memoize:

```blade
<x-shape::table.cell :value="$invoice->number" />   {{-- folds --}}
<x-shape::badge label="Paid" tone="success" />     {{-- folds and memoizes --}}
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

Shape::toast()->info('Export queued')->send();   // info() success() warning() danger() brand() accent()

Shape::confirm('Delete project?')->accept('Delete')->tone('danger')->then('deleteProject')->send();
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
php artisan shape:eject modal       # + heading, text, overlay, button, icon
php artisan shape:eject:all         # every component the package ships
php artisan shape:eject:status      # what has drifted since an upgrade
php artisan shape:doctor            # fold safety of ejected components
php artisan shape:icon bell         # the icons named, from the configured set
php artisan shape:icon lucide.bell  # a name spelled the way the component is
php artisan shape:icon:all          # every icon the set draws
php artisan shape:icon:replace      # regenerate Shape's own icons in that set
php artisan shape:icon:status       # which drawings have moved upstream
php artisan shape:icon:all --from=./resources/svg
```

Which names a run writes is what separates the siblings, so it is the command
rather than an option: no icon or eject command takes a mode flag standing for
*all*, *replace* or *status*, and inventing one is an error rather than a
no-op. The shared options — `--set`, `--from`, `--ref`, `--offline`, `--to`,
`--namespace`, `--force` — are on each icon command that can use them, and
`--bare` is on `shape:eject` alone.

An eject or icon run that writes anything clears the compiled views and says so,
because a generated component is a file and a compiled view is a cached answer
about a file — leaving the second behind serves stale markup, or markup inlined
from a component that no longer exists, without raising an error.

`shape.icon_set` names which of the sets a run reads when `--set` says nothing.
Set it once when the application moves the library onto another set:

```php
'icon_set' => 'lucide',
```

Which set is yours is true of the application, not of a run: a `--set` forgotten
on one run writes a Heroicon into a directory of Lucide drawings, under a slot
name that says nothing about who drew it. `--set` stays the override for a
one-off, such as reading a supplementary set.

A set other than the one `icon_set` names is that supplementary set, and is
written into a subdirectory named after it rather than flat — so its icons are
reached as `<x-shape::icon.lucide.bell />` and cannot land on top of the ones the
library is wearing. A set that wants a different subdirectory declares
`namespace`; `shape:icon:replace` writes flat whichever set it reads, because
the slot names it replaces are flat by definition.

Each set in `shape.icon_sets` declares where its drawings are had — a published
package (`npm`, `version`, `path`) or the repository that draws it (`repo`,
`ref`, `path`) — so `shape:icon` fetches it and caches it under
`storage/framework/shape/icons`. Six of the seven shipped sets read a package:
it holds the drawings rather than the project that produces them, a version is
immutable where a branch moves, and the registry's sha512 is verified. `lucide`
is the exception, its package being larger than its repository. Nothing needs cloning first, and nothing is read
at run time — a generated component is a Blade file with the drawing baked in.

Seven sets ship, each answering every slot: `heroicons` (the default),
`lucide`, `tabler`, `phosphor`, `bootstrap-icons`, `remix-icon` and
`material-symbols`. Publish the config to add another; the six beside
`heroicons` are the layouts to copy from — one flat directory, a directory per
style, a style named in both the directory and the filename, a style marked
inside one flat directory, a set filed by category, and a set read from a mirror.

`material-symbols` reads weight 400 outlined from the `@material-symbols` mirror
(`svg/400/outlined`), because Google files each symbol as a directory of 168
variants. Its names use underscores: `<x-shape::icon.check_circle />`.

It is read from a published package rather than a repository — the other source
a set can name, and the smaller one:

```php
'material-symbols' => [
    'npm' => '@material-symbols/svg-400',
    'version' => 'latest',   // resolved and pinned as the release behind it
    'path' => 'outlined',
],
```

Nothing runs npm; the registry is fetched over HTTP like any archive. Use it for
a set whose repository carries far more than its drawings — 1.8MB against 2.8GB
here, 1.2MB against 32MB for Tabler. Versions are pinned into generated
components instead of commits, and the registry's sha512 is verified.

`'archive' => false` is the other lever: read a set one drawing at a time when
its repository is too large to unpack. It costs `shape:icon:all`, which needs a
listing.

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

`shape:icon:all` reads those patterns backwards to decide what a listed file is
called, so `person-fill-x.svg` lands in the solid cell of `person-x` instead of
arriving as an icon named for its own marker. A file no pattern accounts for is
skipped.

A pattern needs the path to be a fact about the style and the size. Add
`'flatten' => true` for a set that files its drawings by something the name does
not say — Remix Icon nests by category, so `close-line.svg` is under `System`.
The set is then unpacked into one directory by filename and read as a flat set:

```php
'remix-icon' => [
    'path' => 'icons',
    'flatten' => true,
    'styles' => [
        // The bare spelling is the fallback: 151 editor glyphs carry no marker.
        'outline' => ['base' => ['{name}-line.svg', '{name}.svg']],
        'solid' => ['base' => '{name}-fill.svg'],
    ],
],
```

Such a set always fetches the whole of itself, even for one named icon, because
nothing can say where a single drawing is until the archive is in hand. Its
filenames must be unique across its directories; `shape:icon` names both files
and fails rather than letting one overwrite the other.

- `--from` reads a local directory instead, and still wins when given; use it for
  a designer's folder or a set with no upstream.
- `--ref` reads a different branch, tag or commit than the set declares.
- `--offline` works only from what has already been fetched and fails by name
  rather than reaching for the network — use it in CI.
- `shape:icon:status` reports which icons have been redrawn upstream since they
  were generated, from the `shape-icons.json` written beside them, and reports
  every directory holding one rather than only the directory a run resolved to.

`shape:icon:replace` generates Shape's icon **slots** — the icons it resolves in
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
`plus` are not taken; nothing resolves them and `shape:icon:replace` leaves them
alone. It does not touch what you generate either, so regenerate that yourself
when you swap sets — `shape:doctor` lists what it finds outside the slots for
that reason.

A set that declares a `namespace` in `shape.icon_sets` is written into a
subdirectory instead, for when two sets spell the same name:
`icon/lucide/bell.blade.php` is `<x-shape::icon.lucide.bell />`, and it folds
exactly as a top-level icon does. It belongs on the set rather than on the
command line — where a set lives is true of the set, and a `--namespace` flag is
remembered only for the run it is typed on, so the next run without it writes a
second copy flat. The flag survives as a per-run override on `shape:icon` and
`shape:icon:all`, and `--namespace=` says a run is flat about a set that normally
isn't — on `shape:icon:replace` that empty value is the only one it takes, and a
set declaring a `namespace` is refused outright. Keep the primary set flat so a
call site has one spelling for an icon whichever set drew it.

Asking for one of those is `php artisan shape:icon lucide.bell`: the name
spelled the way the component is, which is `--set=lucide` said on the name
rather than on the run, down to the directory it writes. It is the only form
that reads two sets in one run (`shape:icon lucide.bell tabler.compass trash`),
and `--set` and `--namespace` go on answering for the bare names beside it. The
prefix names a set and the set still says where it is written, so the two cases
where those differ resolve to the set's answer and say so: the set `icon_set`
names is written flat, and a set declaring a `namespace` is written under that
whichever of the two spellings reached it.

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

- [docs/_index.md](https://github.com/onelegstudios/shape/blob/main/docs/_index.md) — every component, with its tier
- [docs/folding.md](https://github.com/onelegstudios/shape/blob/main/docs/folding.md) — what keeps a call site on the fold path
- [docs/forms.md](https://github.com/onelegstudios/shape/blob/main/docs/forms.md) — the field shorthand and name resolution
- [docs/tooling.md](https://github.com/onelegstudios/shape/blob/main/docs/tooling.md) — the nine commands
- `vendor/onelegstudios/shape/resources/registry.json` — components, files, dependencies, tiers

## Examples

- A form field in one call site: `<x-shape::input label="Email" wire:model="email" />`
  renders the label, the control, the description and the validation error, with
  `for`, `id` and `aria-describedby` wired from the control's name.
- A table of rows: wrap `<x-shape::table>` around `table.head` / `table.body`, and
  write cells as `<x-shape::table.cell :value="$row->total" />` so each one folds.
- A destructive action: `<x-shape::overlay.trigger for="delete-project" tone="danger">`
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
  (`php artisan shape:icon bell`), under the name its set uses — `shape-user` on
  an avatar is a documentation example, not an exception to this
- name a `size` on an icon and leave `variant` alone unless the style is the
  point; the small sizes are drawn solid because a stroke does not read at 16px,
  and a call site that names only a size works with any icon set
- do not bind `badge` per row on an avatar when a bare `badge` will do; it
  branches, while `badge-tone` is safe, so a presence dot whose colour comes
  from the row still folds
- do not rely on an avatar's badge to say anything to a screen reader; it is
  `aria-hidden`, and the status belongs in `alt` ("Ada Lovelace, online")
- do not publish the stylesheet to change colours; redeclare the tokens instead
