# Tooling

Four commands, and none of them is required to use the library. Shape is
seventy Blade files and a stylesheet; what follows is what turns that into
something you can install, take pieces out of, and keep honest afterwards.

```bash
php artisan shape:install     # the two lines this package needs
php artisan shape:eject modal # a component, and everything it composes
php artisan shape:doctor      # the mistake that costs a fold and says nothing
php artisan shape:icon        # SVGs in, components out
```

## `shape:install`

```bash
php artisan shape:install
```

Imports the tokens into `resources/css/app.css` after Tailwind's own import, and
registers the script in `resources/js/app.js`. That is all there is to install —
there is no config to publish before the package works and no asset to build,
because the token file is imported from `vendor/` and declares
`@source "../views"`, so your Tailwind build scans the package's Blade directly.

Both files are options, for a project that keeps them somewhere else:

```bash
php artisan shape:install --css=resources/css/theme.css --js=resources/js/site.js
```

If it can't find a file, it prints the line and where it goes rather than
creating an entry point you didn't ask for. Run it twice and the second run
changes nothing.

## `shape:eject`

The third and last step of [customisation](_index.md#customising). Tokens cover
colour, radius and density; utilities cover the one-off; ejecting hands you the
file.

```bash
php artisan shape:eject modal
```

```
modal .............................................. requested
  modal/index.blade.php ................................ ejected
heading ................................... required by modal
  heading.blade.php .................................... ejected
overlay ................................... required by modal
  overlay/close.blade.php .............................. ejected
button .................................. required by overlay
  button/index.blade.php ............................... ejected
icon ..................................... required by button
  icon/check.blade.php ................................. ejected
```

A modal is a `<dialog>` that composes a heading, a text, and a close button that
is itself a button wrapping an icon. Ejecting the modal alone would leave you
owning the outer shell and none of the parts you probably wanted to change,
which is the worst of both arrangements — so the command follows the dependency
graph. `--bare` opts out of that, if the outer file really is all you want.

Ejected components land in `shape.components_path`, which Shape registers ahead
of its own views. Nothing else has to change: `<x-shape::modal>` is now your
file. A file that is already there is kept and reported, unless you pass
`--force`.

`vendor:publish --tag="laravel-shape-components"` still does the whole library
at once.

### After an upgrade

Ejecting writes a `shape-eject.json` next to the components, recording what the
package held at the moment each file was copied. That record is the only thing
that can tell two situations apart later:

```bash
php artisan shape:eject --status
```

| | |
| --- | --- |
| `unchanged` | Level with the package. |
| `edited here` | You changed it. The package hasn't moved. |
| `the package moved` | You didn't touch it, and the packaged version has changed since — the upgrade has something in it for you. |
| `edited here, and the package moved` | Both. Read the diff before deciding. |
| `gone` | Ejected once, deleted since. |

Without the record, every one of those looks the same from outside: a local file
that differs from the packaged one.

## `shape:doctor`

```bash
php artisan shape:doctor
```

Checks your ejected components for the one mistake that has no symptom.

A folded component is pre-rendered while Blade compiles, and the result is baked
into the parent template. Anything request-scoped inside it is therefore
resolved once and then served to everybody — the first visitor's session, the
first visitor's locale, a CSRF token minted at deploy time. In development, with
one user and a warm cache, all of that looks like it works.

```
greeting.blade.php:3 auth(   the authenticated user, resolved once at compile time
```

The list is Blaze's own global-state checklist plus the translation helpers,
which belong on it for exactly the same reason. Anything inside an `@unblaze`
block is exempt — that block is the sanctioned hole, and
[the error component](components/field.md#error) is why it exists. Blade
comments are exempt too, since a comment never runs.

Components that only memoize are left alone: memoization caches a rendered
component per prop set at run time, so it sees the request it was rendered in.

It also reports how much of the library a replaced icon set covers, which is a
different mistake with the same shape — it has no symptom either:

```
  icon set ............................ lucide, 11 of 12 names
  information-circle ....... missing — falls back to Heroicons
```

Eleven of twelve renders perfectly. The twelfth resolves to the Heroicon this
package ships and draws correctly, in the wrong set, on a page now wearing two.
Both halves are derived — the names out of the components that draw them, the
coverage out of `components_path` — and the attribution is read from the notice
each generated file carries, so an icon directory holding two sets says so.

The check is silent until something has actually replaced one of those names: an
application on the packaged icons is not partially covered, and a directory of
icons that are none of Shape's own is a supplementary set, which is the other
supported thing to do.

It exits non-zero on a finding, so it can sit in CI. `--path=` points it
somewhere else, and `--package` runs it over the components Shape itself
ships — which the suite in this repository does on every run, so the library is
held to the rule it publishes.

## `shape:icon`

Every icon in Shape is generated. The header on each file says
*Regenerate; don't hand-edit*, and this is what regenerates them.

```bash
php artisan shape:icon check arrow-right
php artisan shape:icon --all
php artisan shape:icon bell --set=lucide
php artisan shape:icon --replace --set=lucide
php artisan shape:icon --all --from=./resources/svg
php artisan shape:icon --status
```

### Where the drawings come from

A set says which repository draws it, and `shape:icon` fetches it:

```php
'heroicons' => [
    'repo' => 'tailwindlabs/heroicons',
    'ref' => 'master',
    'path' => 'optimized',
    'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
    'styles' => [/* … */],
],
```

Heroicons is not a dependency of this package, and asking you to clone it before
the header's instruction could be followed was not a workflow. So the set is
fetched once, cached under `storage/framework/shape/icons`, and read from there
afterwards. Nothing about it is read at run time; a generated component is bytes
on disk.

Two ways in, chosen by the shape of the run. `--all` and `--replace` take the
whole set as one tarball, which is also the only form that can list a directory.
A handful of names on the command line takes one raw file per drawing instead,
because downloading a repository to answer `shape:icon bell` is the wrong trade.

```bash
php artisan shape:icon bell --set=lucide --ref=v0.544.0
php artisan shape:icon --all --offline
php artisan shape:icon --all --from=./resources/svg
```

`--ref` reads a different branch, tag or commit. `--offline` works from what has
already been fetched and fails, loudly and by name, rather than reaching for the
network — which is how CI generates without depending on GitHub being up.

`--from` still wins whenever it is given, and stays the way to read a local
checkout, a folder a designer handed over, or a set with no upstream at all.

### Knowing what a file was drawn from

Every generated component carries its licence notice and, when it was fetched,
the commit it was read at:

```blade
{{-- Heroicons (https://heroicons.com), MIT licensed. tailwindlabs/heroicons@a7a54a5f8e0b. Regenerate; don't hand-edit. --}}
```

A ref is usually a branch, so recording `master` would say nothing about which
drawing is actually in the file. The resolved commit comes out of the archive
itself and costs no extra request.

`shape-icons.json` is written beside the components — the same trade
`shape:eject` makes with `shape-eject.json`, one layer further out. It records
the set, the ref, the commit and a digest per icon, which is what lets
`--status` tell the interesting case from the ordinary one:

```bash
php artisan shape:icon --status
```

```
heroicons                       tailwindlabs/heroicons@a7a54a5f8e0b
  check                                                   unchanged
  chevron-down                                     redrawn upstream
```

Without the record, a component that differs from the current drawing might have
been hand-edited or might have been overtaken upstream, and only the second is a
reason to regenerate.

A licence travels with the drawings it covers. Font Awesome Free is CC BY 4.0
and Material is Apache 2.0, and generated components carry an attribution
requirement you inherit by redistributing them — so a fetched set keeps the
repository's own `LICENSE` beside its cache rather than a paraphrase of it.

### What a set is

A matrix of styles against sizes, and most of them are sparse. Lucide is one
style at one size. Phosphor is six styles at one size. Heroicons is two styles
at three sizes with three cells empty, because it draws no outline at 16px or
20px — a 1.5px stroke does not read that small.

Modelling the matrix covers all three without a special case for any of them.
One axis of it is the library's and the other is the set's.

The scale lives in `shape.icon_sizes`, once, for every set:

```php
'icon_sizes' => [
    'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
    'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
    'base' => ['class' => 'size-6', 'prefer' => 'outline'],
],
```

Smallest first; the last is the default. It is declared outside the sets so that
a call site reads the same whichever set is behind it — `size="sm"` is 20px for
an icon from your supplementary set as much as for one of Shape's, which is not
something a per-set scale could promise. Every generated icon emits the same
size `match`, so mixing sets cannot mix scales.

`prefer` is what a size reaches for when the call site names no style. It is the
reason eleven of the twelve places this library draws an icon can ask for a size
and nothing else, and still get a crisp solid glyph rather than a stroke shrunk
until it disappears. It is also what makes those call sites portable: a set that
has no such style ignores the preference and answers with the one style it has.

Sets live in `shape.icon_sets`, and say only which cells they draw. Heroicons is
one entry in it:

```php
'heroicons' => [
    'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
    'styles' => [
        'solid' => [
            'xs' => '16/solid/{name}.svg',
            'sm' => '20/solid/{name}.svg',
            'base' => '24/solid/{name}.svg',
        ],
        'outline' => [
            'base' => '24/outline/{name}.svg',
        ],
    ],
],
```

`styles` says where each cell is drawn, as a path with `{name}` in it — a
directory layout and a filename convention are the same declaration. A cell a
style doesn't draw borrows that style's largest drawing and is sized down by the
class; never up. A size the scale doesn't declare is refused rather than
ignored, because in a hand-written set that is a typo every time.

Publish the config to add your own:

```bash
php artisan vendor:publish --tag="laravel-shape-config"
```

A flat directory of SVGs is not a special case — it is a set with one style
whose only pattern is `{name}.svg`, which is what the shipped `lucide` entry is.

### Names, and replacing Shape's own icons

Writing an icon into `components_path` replaces the packaged one *everywhere*,
including inside Shape's own components — the service provider registers that
path first, so `resources/views/shape/icon/check.blade.php` is what a checkbox
renders. No config, no registration, and no cost to folding.

What stood in the way of using that was never the mechanism. It was spelling.
Shape's components ask for Heroicons' names, and Lucide has `x` rather than
`x-mark`, `info` rather than `information-circle`, `circle-check` rather than
`check-circle`. The matrix generalised across sets; the vocabulary did not.

So a set can also declare what it calls things:

```php
'lucide' => [
    'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
    'styles' => [
        'outline' => ['base' => '{name}.svg'],
    ],
    'aliases' => [
        'x-mark' => 'x',
        'information-circle' => 'info',
        // …
    ],
],
```

An alias moves the *source file* and nothing else. `shape:icon x-mark
--set=lucide` reads `x.svg` and writes `x-mark.blade.php`: the close button goes
on asking for the name it has always asked for, and the drawing behind it
changes. One direction only, and a name a set spells the same way needs no
entry — most of them.

`--replace` is that applied to the whole library at once:

```bash
php artisan shape:icon --replace --set=lucide --from=./vendor/lucide/icons
```

It generates exactly the twelve names Shape draws in components of its own — the
checkbox's `check` and `minus`, the pager's chevrons, the select's
`chevron-down`, the close button's `x-mark`, the four glyphs the tones resolve
to, and the stat's two trends — and nothing else. That list is read out of the
markup rather than written down, so a component that gains an icon cannot leave
a hole in it. On a fresh application `components_path` is empty, so there is
nothing to `--force` and nothing to eject first.

Nothing above changes what a call site looks like, because none of it reaches
the component's name. `<x-shape::icon.x-mark />` is `x-mark` whichever set drew
it.

`--all` is the one place the translation runs backwards: it walks files, and
files carry the set's names, so each is written under every Shape name that
aliases to it. A file nothing aliases to is written under its own name, which is
how a supplementary set adds icons rather than replacing them.

### Two sets at once

Sets generated into `icon/` share one namespace. Names that don't collide
coexist there, which is the ordinary supplementary case and wants nothing:

```
check .. 4 drawing(s)      # Heroicons
bell .. 1 drawing(s)       # a second set
check .. exists, kept      # a collision, refused
```

Refusing is the right default — the alternative is a set silently overwriting
another set's drawings — but it leaves no way to keep both. Give the set a
`namespace`, and it gets a subdirectory and a namespace of its own:

```php
'lucide' => [
    'namespace' => 'lucide',
    'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
    'styles' => [
        'outline' => ['base' => '{name}.svg'],
    ],
],
```

```bash
php artisan shape:icon --all --set=lucide --from=./vendor/lucide/icons
```

```
resources/views/shape/icon/lucide/bell.blade.php
→ <x-shape::icon.lucide.bell />
→ <x-shape::icon name="lucide.bell" />
→ <x-shape::button icon="lucide.bell">
```

It belongs in the config rather than only on the command line because where a
set lives is true of the set, not of the run that generated it. A flag is
remembered for one run: the next `shape:icon bell --set=lucide` without it would
write a second copy flat, into the namespace the first was moved out of to avoid
a collision, and pin it in a second lockfile — silently, because the "exists,
kept" check only looks in the directory the run resolved to.

`--namespace` is still there as the override for a one-off run, and
`--namespace=` with nothing after it is the way to say a run is flat about a set
that normally isn't — which is what `--replace` needs, since it writes over
names that are flat by definition. One lower-case segment either way:
`../..` is refused rather than allowed to write outside the components path,
whether it comes from the flag or from the set.

`--status` reports every directory that holds a lockfile, not just the one the
run resolved to, so a namespaced set is not a blind spot:

```bash
php artisan shape:icon --status
```

Nesting is a path and nothing else: a namespaced icon folds exactly as a
top-level one does, including into the fold of a button it sits inside, because
every generated file carries its own `@blaze` front matter and that is what
Blaze reads. `shape:eject`, `shape:doctor` and Tailwind's `@source` all recurse
already.

Reach for a namespace when two sets genuinely collide, and not before. Flat is
better for the ordinary case of Heroicons plus the gaps: one spelling at every
call site, and no collisions by construction — which is why neither shipped set
declares one. Note that a bare set name is a component that doesn't exist —
`<x-shape::icon name="lucide" />` raises Blade's usual "unable to locate" error
rather than rendering nothing, because Blade resolves a directory to an `index`
view.

### What it writes

`width`, `height`, `class`, `aria-hidden` and `data-slot` are dropped: those
describe how a drawing is *used*, and they arrive through the attribute bag.
What describes the drawing itself is kept exactly as the source states it.

Everything the config decides — the size classes, the preferred style, which
cell gets which drawing — is written into the component as a literal. Nothing
reads `shape.icon_sets` or `shape.icon_sizes` at run time, which is what keeps a generated icon at
[Tier B](folding.md): both props are static at almost every call site, so what
reaches the compiled template is one `<svg>` and none of the machinery that
chose it.

The alternative — a Composer package per icon set, which is WireUI's approach —
is a version matrix to maintain for what is fundamentally a code generator, and
it puts the set you actually want furthest out of reach. This reads whatever
directory you point it at, in whatever layout you describe.

## The documentation site

The workbench is the docs site. Testbench already boots a real Laravel
application to run this package's tests in, so `composer serve` gives the
markdown in `docs/` at `/docs`, rendered by
[laradocs](https://laradocs.dev), beside a gallery of every component at `/`.

Examples on the component pages are written once:

```markdown
@docs('preview', name: 'button')
```

That renders `docs/previews/button.blade.php` — a real Blade file, with the real
components in it — and prints the same file underneath as the example. The suite
asserts that every preview still compiles and that every page and file have each
other, so an example cannot quietly describe a prop that was renamed.

Six pages keep a fenced code block instead, and for a reason worth stating: the
table, the list, the pager and the progress bar take runtime data, and the
toaster and the confirm dialog render nothing you can see until something sends
them a message. An example that has to invent a paginator in order to look at
itself is no longer the example.

## The registry

`resources/registry.json` is the manifest all of the above reads: what files
each component is made of, what it composes, which page documents it, and which
Blaze tier it belongs to.

```json
{
  "modal": {
    "files": ["modal/index.blade.php"],
    "requires": ["overlay", "heading", "text"],
    "docs": "components/modal.md",
    "tier": "fold"
  }
}
```

It is hand-maintained rather than generated at boot — scanning seventy views for
their `<x-shape::…>` tags on every request, to discover a graph that only changes
when a component does, would be the wrong trade. The scan lives in the test suite
instead, where it fails the build the moment the manifest and the markup
disagree, along with the assertion that every entry names a documentation page
that exists.
