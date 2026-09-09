# Tooling

Nine commands in four families, and none of them is required to use the
library. Shape is seventy Blade files and a stylesheet; what follows is what
turns that into something you can install, take pieces out of, and keep honest
afterwards.

```bash
php artisan shape:install     # the two lines, and which set the icons come from
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

### Which set the library is drawn in

It then asks one question:

```
 Which icon set should Shape be drawn in?
 › Heroicons (hero)
 ○ Lucide (lucide)
 ○ Tabler Icons (tabler)
 ○ Phosphor Icons (phosphor)
 ○ Bootstrap Icons (bootstrap)
 ○ Remix Icon (remix)
 ○ Material Symbols (material)
 Enter keeps [hero], which is the set the library is drawn in now.
```

Enter is Heroicons, which is what the fourteen
[slots](components/icon.md#what-shape-draws-for-you) already ship drawn in — so
the default answer generates nothing, publishes nothing, and leaves the paragraph
above true. Any other answer is
[`shape:icon:replace`](#slots-and-replacing-shapes-own-icons) against that set,
followed by `icon_set` written into a published `config/shape.php` so that no
later run has to be told again.

Those two happen in that order, and the order is the point. A set is fetched over
the network, so a run that cannot reach one leaves `icon_set` still naming the
set the drawings on disk actually came from — a config saying `lucide` over a
directory of Heroicons is the mixed-set page the slots exist to prevent, arrived
at by an installer instead of by a forgotten flag.

The vendor is read from that set's `notice`, the attribution every generated
icon already carries — a set's real name is a thing it has said once rather than
a second key to keep level with the first. The key in brackets is what `--icons`
and `--set` take.

`--icons` answers in advance, which is the scripted install:

```bash
php artisan shape:install --icons=lucide
```

`--no-interaction` skips the question and leaves the configured set alone, so a
deploy script that has always run this keeps running it unchanged. Answering with
the set you are already on does nothing. Answering with a different one overwrites
every slot in `components_path`, including any you had drawn by hand — which is
what switching sets means, and is why the question is asked on day one rather
than left to be discovered on day ninety.

## `shape:eject`

The third and last step of [customisation](_index.md#customising). Tokens cover
colour, radius and density; utilities cover the one-off; ejecting hands you the
file.

```bash
php artisan shape:eject modal   # a component, and everything it composes
php artisan shape:eject:all     # every component in the library
php artisan shape:eject:status  # what has moved since you copied it
```

The first of those prints what it took, and why each file is in the list:

```
heading ................................... required by modal
  heading/heading.blade.php ............................ ejected
icon ..................................... required by button
  icon/icon.blade.php .................................. ejected
  icon/shape-arrow-right.blade.php ..................... ejected
  …
  icon/shape-warning.blade.php ......................... ejected
button .................................. required by overlay
  button/button.blade.php .............................. ejected
  button/element.blade.php ............................. ejected
overlay ................................... required by modal
  overlay/close.blade.php .............................. ejected
  overlay/footer.blade.php ............................. ejected
  overlay/trigger.blade.php ............................ ejected
text ...................................... required by modal
  text/text.blade.php .................................. ejected
modal .............................................. requested
  modal/modal.blade.php ................................ ejected
```

A modal is a `<dialog>` that composes a heading, a text, and a close button that
is itself a button wrapping an icon. Ejecting the modal alone would leave you
owning the outer shell and none of the parts you probably wanted to change,
which is the worst of both arrangements — so the command follows the dependency
graph. Icons come as a directory: a button resolves whichever name it was given,
so there is no subset of them the command could know to take. `--bare` opts out
of the whole walk, if the outer file really is all you want.

Ejected components land in `shape.components_path`, which Shape registers ahead
of its own views. Nothing else has to change: `<x-shape::modal>` is now your
file. A file that is already there is kept and reported, unless you pass
`--force`.

A run that ejects anything clears the compiled views, and says so — an ejected
component resolves ahead of the packaged one, so every compiled view that inlined
the packaged version is now an answer about a file that is no longer the one
being asked about.

`shape:eject:all` takes the whole library, on the same terms: the same
`--force`, the same report, and the same record written beside the files.
`vendor:publish --tag="shape-components"` still copies all of it too,
and is the one way in that writes no record — so what it hands you is a
directory `shape:eject:status` can say nothing about afterwards.

### `shape:eject:status`

The command for after an upgrade. Ejecting writes a `shape-eject.json` next to
the components, recording what the package held at the moment each file was
copied. That record is the only thing that can tell two situations apart later:

```bash
php artisan shape:eject:status
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

It also reports how many of Shape's icon slots a replaced set covers, which is a
different mistake with the same shape — it has no symptom either:

```
  icon set ............................ lucide, 12 of 14 slots
  shape-loading ..... packaged — Shape draws this one, your set need not
  shape-info ............... missing — falls back to Heroicons
  outside the slots ........................... bell, compass
```

Thirteen of fourteen renders perfectly. The fourteenth resolves to the Heroicon
this package ships and draws correctly, in the wrong set, on a page now wearing
two. Three states rather than two, and only the last is a finding: a slot
**generated** from your set, a slot **packaged** by Shape because no set is
asked to fill it, and a slot **missing**. The slots are declared in
`shape.icon_slots`; the coverage is read out of `components_path`; and the
attribution comes from the notice each generated file carries, so an icon
directory holding two sets says so.

Icons outside the slots get a line and never a finding. Those are the ones you
generated yourself: `shape:icon:replace` does not touch them and should not,
but they are sitting in the same directory in whatever set drew them, and
silence about that reads as approval.

The check is silent until something has actually replaced a slot: an application
on the packaged icons is not partially covered, and a directory of icons that
are none of Shape's own is a supplementary set, which is the other supported
thing to do.

It exits non-zero on a finding, so it can sit in CI. `--path=` points it
somewhere else, and `--package` runs it over the components Shape itself
ships — which the suite in this repository does on every run, so the library is
held to the rule it publishes.

## `shape:icon`

Every icon in Shape is generated. The header on each file says
*Regenerate; don't hand-edit*, and these four are what regenerate them.

```bash
php artisan shape:icon check arrow-right  # the names you ask for
php artisan shape:icon:all                # every icon the set draws
php artisan shape:icon:replace            # the icons Shape draws itself
php artisan shape:icon:status             # what has been redrawn upstream
```

Which names a run writes is the whole difference between them, so it is the
argument each one is named for rather than a flag it takes. Everything else —
which set, where the drawings come from, where the components land — is shared,
and is the rest of this section:

```bash
php artisan shape:icon bell --set=lucide
php artisan shape:icon lucide.bell
php artisan shape:icon:all --from=./resources/svg
```

The middle one is the first one, typed the way the component is spelled. See
[Naming the set on the icon](#naming-the-set-on-the-icon).

### Which set a run reads

`shape.icon_set` names it, and every run without `--set` reads that one:

```php
'icon_set' => 'lucide',
```

```bash
php artisan shape:icon:replace
php artisan shape:icon bell trash
```

It belongs in the config for the reason a set's `namespace` does. Which set is
yours is true of the application; a flag is true of one run. An application on
Lucide that had to type `--set=lucide` forever only had to forget once, and the
run that forgot wrote a Heroicon into a directory of Lucide drawings — under a
slot name, which is a role and says nothing about who drew it. Set it, and
`shape:icon:replace` is a replacement rather than a re-mixing.

`--set` is still the override, and reading a supplementary set is exactly the
one-off it is for:

```bash
php artisan shape:icon bell --set=hero
```

A set read that way is written under its own name — `icon/hero/bell.blade.php`,
reached as `<x-shape::icon.hero.bell />` — rather than flat among the drawings
the library is wearing. [Two sets at once](#two-sets-at-once) is the whole of
that story.

### Naming the set on the icon

Which makes `--set` an answer about a run to a question about a component, and
the component already has somewhere to say it. So a name may be spelled the way
you would write it in a template:

```bash
php artisan shape:icon lucide.bell
```

```
resources/views/shape/icon/lucide/bell.blade.php
→ <x-shape::icon.lucide.bell />
```

That is the same run as `shape:icon bell --set=lucide`, and it is the spelling
already in your templates: copy the name out of the Blade you are about to
write, paste it after `shape:icon`, and what comes back resolves. Nothing to
translate in either direction, and nothing to remember about where a
supplementary set lands.

Being true of a name rather than of a run, it is also the only form that can ask
for two sets at once:

```bash
php artisan shape:icon lucide.bell tabler.compass trash
```

Each set is still read once — the names are grouped before anything is fetched —
and each group writes its own lockfile beside its own components. `trash` there
carries no namespace, so `--set` and `--namespace` answer for it, as they always
did; a name that answers for itself is not asking. A run that says both, as
`shape:icon lucide.bell --set=hero` does, has said the more specific thing on
the name, and the set it named is the one it reads.

The prefix names a set. Where that set is written is still the set's own to
declare, which is what keeps the two forms the same run:

```bash
php artisan shape:icon lucide.bell
php artisan shape:icon bell --set=lucide   # the same directory, either way
```

For the sets you will type it about, those are the same word and there is
nothing to know. Two of them resolve to a directory other than the one typed,
and both say so when they do:

- The set `shape.icon_set` names is written flat, so on a Heroicons application
  `hero.bell` is `icon/bell.blade.php` — `<x-shape::icon.bell />` — exactly
  where `--set=hero` puts it. Writing it under `icon/hero/` instead would be a
  second copy of a drawing already there, in a second lockfile, under a
  namespace nothing reaches for; that is the collision the whole subdirectory
  exists to avoid, not an instance of it.
- A set that declares a `namespace` is written under that, and answers to either
  spelling: with `'namespace' => 'lucide-icons'`, both `lucide.bell` and
  `lucide-icons.bell` write `icon/lucide-icons/bell.blade.php`.

One lower-case segment either way, checked the way `--namespace` is. Two sets
written into one namespace is the one case with no answer — one of them has
declared the other's name — and the run is told so rather than guessed at.

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

Two ways in, chosen by which command is running. `shape:icon:all` and
`shape:icon:replace` take the whole set as one tarball, which is also the only
form that can list a directory. `shape:icon` takes one raw file per drawing
instead, because downloading a repository to answer `shape:icon bell` is the
wrong trade.

```bash
php artisan shape:icon bell --set=lucide --ref=v0.544.0
php artisan shape:icon:all --offline
php artisan shape:icon:all --from=./resources/svg
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
`shape:eject:status` makes with `shape-eject.json`, one layer further out. It
records the set, the ref, the commit and a digest per icon, which is what lets
`shape:icon:status` tell the interesting case from the ordinary one:

```bash
php artisan shape:icon:status
```

```
heroicons                       tailwindlabs/heroicons@a7a54a5f8e0b
  shape-checked                                           unchanged
  shape-expand                                     redrawn upstream
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
    'base' => ['class' => 'size-6', 'prefer' => 'outline', 'default' => true],
    'lg' => ['class' => 'size-8', 'prefer' => 'outline'],
    'xl' => ['class' => 'size-10', 'prefer' => 'outline'],
],
```

Smallest first, and `default` marks the size a call site gets when it names
none. It is marked rather than inferred because the scale runs past its own
default: `xs` to `xl` like every other scale in the library, so the middle of it
is the answer and the end of it is not. A scale that marks none falls back to
the last declared, which is what a config published before this key existed
means.

Above `base` the drawings stop and only the box grows — no set draws above 24px,
so `lg` and `xl` render the 24px artwork larger, and `prefer` stays with the
stroked drawing up there because a stroke is what survives being scaled.

The scale is declared outside the sets so that a call site reads the same
whichever set is behind it — `size="sm"` is 20px for an icon from your
supplementary set as much as for one of Shape's, which is not something a
per-set scale could promise. Every generated icon emits the same size `match`,
so mixing sets cannot mix scales.

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
php artisan vendor:publish --tag="shape-config"
```

A flat directory of SVGs is not a special case — it is a set with one style
whose only pattern is `{name}.svg`, which is what the shipped `lucide` entry is.

A pattern is a path and not a directory because a filename is not always a name.
Phosphor puts the weight in both: `regular/heart.svg` and `fill/heart-fill.svg`
are one icon drawn twice. `shape:icon:all` reads the pattern backwards to see
that — `{name}-fill` against `heart-fill` is `heart` — so the two land in one
component instead of fifteen hundred icons arriving beside fifteen hundred
`-fill` ones whose other cell is never there. A file no pattern accounts for is
skipped.

Two spellings beyond `{name}`, both for sets that don't put the marker on the
end. `{head}` and `{tail}` are the name split at its last hyphen, and only ever
appear together: Bootstrap hangs a badge off a glyph and fills the glyph, so
`person-fill-x` is the fill of `person-x`, and `{head}-fill-{tail}.svg` says so
in both directions. And a cell may hold a list rather than one path, tried in
order, for a set that spells the same cell two ways:

```php
'solid' => [
    'base' => ['{name}-fill.svg', '{head}-fill-{tail}.svg'],
],
```

Which of them is the drawing is the source's answer, not the set's — the first
candidate with a file behind it is the cell, and a cell with none is empty. Order
matters where both exist: Bootstrap draws `person-check-fill` filled through and
`person-fill-check` as a filled person wearing an outline tick, so the suffix
goes first.

### A layout no pattern can place a name into

A pattern works because the path is a fact about the style and the size:
`24/solid/{name}.svg` is the same two directories for every drawing in
Heroicons. Some sets file their drawings by something the name says nothing
about. Remix Icon nests by category, so `close-line.svg` is under `System` and
`user-line.svg` under `User & Faces`, and no pattern knows which of the twenty
folders a name is in without being told one name at a time.

`flatten` answers it before the patterns ever see it. The set is fetched whole
and unpacked into a single directory by filename, so what is read is an ordinary
flat set:

```php
'remix-icon' => [
    'path' => 'icons',
    'flatten' => true,
    'styles' => [
        'outline' => ['base' => ['{name}-line.svg', '{name}.svg']],
        'solid' => ['base' => '{name}-fill.svg'],
    ],
],
```

Two things follow. Such a set always fetches the whole of itself, even for one
named icon, because nothing can say where a single drawing is until the archive
is in hand — which is a tarball rather than three thousand requests, and it is
cached like any other. And its filenames have to be unique across its own
directories: `shape:icon` checks that while unpacking and names both colliding
files rather than letting the second quietly overwrite the first.

### The sets that ship

Seven, each checked against the repository it names and each answering every
slot, so `shape:icon:replace --set=…` is a complete answer for any of them:

| | Styles | Grid | |
| --- | --- | --- | --- |
| `heroicons` | outline, solid | 24, with solid also drawn at 16 and 20 | The default, and what this package's own drawings came from; the package publishes them byte for byte |
| `lucide` | outline | 24 | One style, one flat directory, and the one set still read from its repository |
| `tabler` | outline, solid | 24 | Read from the `@tabler/icons` package; `solid` is upstream's `filled`, which draws about a fifth of what `outline` does, and a name it hasn't got falls back |
| `phosphor` | outline, solid | 256 | `regular` and `fill`, two of its six weights |
| `bootstrap-icons` | outline, solid | 16 | `solid` is the `-fill` half of a flat directory, suffixed or infixed; half its names have one |
| `remix-icon` | outline, solid | 24 | Filed by category, so it is flattened on the way in; 1,539 names drawn both ways, and 151 editor glyphs drawn neither |
| `material-symbols` | outline, solid | 24 | Weight 400 outlined, read from the `@material-symbols/svg-400` package; 3,903 names, spelled with underscores |

The grid is what the drawings are drawn on, not what they render at: every set is
measured against `icon_sizes`, and every generated icon emits the same size
`match`. Two of them are worth knowing about before you swap:
`bootstrap-icons` has no loader to spin — it says `null` for `shape-loading` and
Shape's own spinner stays — and neither its `solid` nor Tabler's covers the whole
set, so a name outside one renders its outline drawing at every size. For
Bootstrap that is mostly line art a fill would have drawn identically.

Anything else is an entry you write, and the six beside `heroicons` are worked
examples to write it from: one flat directory, a directory per style, a style
named in both the directory and the filename, a style that is a suffix inside
one directory, a set filed by category that is flattened before it is read, and
a set read from a mirror because its own repository is shaped for a font.

Six of the seven are read from published packages rather than from
repositories, which is the other source a set can name:

```php
'material-symbols' => [
    'npm' => '@material-symbols/svg-400',
    'version' => 'latest',
    'path' => 'outlined',
],
```

Nothing here runs npm. A registry is an HTTP endpoint that hands back a gzipped
tar, so this is the same download and the same unpacking, pointed somewhere
else. What differs is what is on the other end: a package holds the drawings,
where a repository holds the project that produces them.

| | Package | Repository |
| --- | --- | --- |
| `heroicons` | 0.2 MB | 0.6 MB |
| `tabler` | 1.2 MB | 31.7 MB |
| `phosphor` | 1.4 MB | 2.3 MB |
| `bootstrap-icons` | 0.8 MB | 2.0 MB |
| `remix-icon` | 3.7 MB | 3.9 MB |
| `material-symbols` | 1.8 MB | 2.8 GB |
| `lucide` | 6.2 MB | **5.0 MB** |

Material Symbols is why this exists at all: Google files every symbol as a
directory of 168 variants, and even the mirror of that cannot be unpacked in
memory. Lucide is the one that stayed — `lucide-static` ships fonts and a sprite
sheet beside the drawings, so it is the only package here larger than the
repository behind it, and there is nothing to move it for.

Two things follow beyond the bytes. A version is immutable where a branch moves,
so what is pinned into a generated component is `heroicons@2.2.0` — the release,
resolved from the `latest` tag rather than the word itself. And the registry
states an sha512 for every archive, which is checked before anything is
unpacked; a repository archive offers nothing to check against.

The cost is that a registry serves packages and nothing smaller: there is no
per-file endpoint, so every run pulls the package rather than the two drawings a
named icon needs. At these sizes that is cheaper than the raw fetches it
replaces. `--ref` names a version for a published set, the way it names a branch
for a repository.

A set that says `'archive' => false` is read one drawing at a time instead —
that is the flag for a repository too large to unpack, and it costs
`shape:icon:all`, which has no listing without an archive. Whatever a set says,
an archive larger than the memory left to open it in is reported with its size
and the limit rather than killing the process partway through.

### Slots, and replacing Shape's own icons

Writing an icon into `components_path` replaces the packaged one *everywhere*,
including inside Shape's own components — the service provider registers that
path first, so `resources/views/shape/icon/shape-checked.blade.php` is what a
checkbox renders. No config, no registration, and no cost to folding.

What stood in the way of using that was never the mechanism. It was spelling.
Every set decides its own names, and Lucide has `x` where Heroicons has
`x-mark`, `info` where it has `information-circle`, `circle-check` where it has
`check-circle`. The matrix generalised across sets; the vocabulary did not.

So Shape asks neither vocabulary. It names the icons it resolves itself for the
**role** they play — `shape-close` is the dismiss glyph, `shape-warning` is what
a warning tone reaches for — and asks each set which of its drawings fills each
one. Those names are **slots**, declared once in `shape.icon_slots`:

```php
'icon_slots' => [
    'shape-close' => [],
    'shape-warning' => [],
    'shape-loading' => ['class' => 'animate-spin', 'packaged' => true],
    // …
],
```

and answered by every set, Heroicons included:

```php
'lucide' => [
    'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
    'styles' => [
        'outline' => ['base' => '{name}.svg'],
    ],
    'slots' => [
        'shape-close' => 'x',
        'shape-warning' => 'triangle-alert',
        'shape-loading' => 'loader-circle',
        // …
    ],
],
```

A slot moves the *source file* and nothing else. `shape:icon shape-close
--set=lucide` reads `x.svg` and writes `shape-close.blade.php`: the close button
goes on asking for the role it has always asked for, and the drawing behind it
changes. The filename states the role and the generated header states the
vendor, so neither has to impersonate the other, and `triangle-alert` stays free
for you to generate under its own name.

A set with nothing for a slot says `null`. That is a different thing from
leaving the entry out — one is an answer and the other is an oversight, and
`shape:icon` reports them differently.

`shape:icon:replace` is the whole list at once:

```bash
php artisan shape:icon:replace --set=lucide --from=./vendor/lucide/icons
```

It generates exactly the declared slots — the checkbox's tick and dash, the
pager's chevrons, the select's arrow, the dismiss glyph, the four the tones
resolve to, the stat's three trends and the spinner — and nothing else. On a
fresh application `components_path` is empty, so there is nothing to `--force`
and nothing to eject first.

This package also ships `shape-arrow-right`, `shape-plus`, `shape-trash` and
`shape-user`, and `shape:icon:replace` leaves them alone. They are not part of
the library's vocabulary: nothing resolves them, and they exist so the README and
the previews render for somebody who has configured nothing. They carry the
prefix so that `arrow-right`, `plus`, `trash` and `user` stay free for whatever
you generate — reaching
for a bare `trash` you never generated is a "component not found" rather than a
Heroicon drawn beside thirteen Lucide slots.

One slot is **packaged**: Shape draws `shape-loading` itself, because Heroicons'
nearest drawing is `arrow-path` — a circular arrow rather than a loader, and it
does not read as one spinning. A set that has something better names it and the
generated file shadows the packaged spinner; a set that says nothing, or `null`,
leaves the spinner rendering, which is the designed answer there rather than a
gap. Look at whatever your set offers *spinning* before keeping it.

Nothing above changes what a call site looks like, because none of it reaches
the component's name. `<x-shape::icon.shape-close />` is `shape-close` whichever
set drew it.

To point one slot somewhere else, publish the config, change its entry, and
regenerate that one name:

```bash
php artisan vendor:publish --tag=shape-config
```

```php
'shape-warning' => 'octagon-alert',
```

```bash
php artisan shape:icon shape-warning --set=lucide --force
```

That is durable in a way a command-line override would not be: a slot repointed
on the command line reverts on the next `shape:icon:replace`. To draw one by hand
instead, write `resources/views/shape/icon/shape-warning.blade.php` yourself —
`components_path` resolves first and the generator reports `exists, kept` rather
than overwriting it without `--force`, so it survives regeneration. Copy the
`@blaze(fold: true, memo: true)` front matter and the `variant`/`size` props off
a generated file; a hand-written slot that omits the annotation stops folding
and says nothing.

`shape:icon:all` walks the set's own files and writes each under its own name,
flat. Slots live in a namespace no set uses, so there is nothing to reverse and
no file that ends up with no name to take — which is how a supplementary set
adds icons rather than replacing them.

### Two sets at once

The set `shape.icon_set` names is written flat, into `icon/`, so that a call
site has one spelling for an icon whichever set drew it. Every other set is a
supplementary one — read for what the library's set has not got — and is written
into a subdirectory named after it:

```bash
php artisan shape:icon lucide.bell
php artisan shape:icon bell --set=lucide   # the same run
```

```
resources/views/shape/icon/lucide/bell.blade.php
→ <x-shape::icon.lucide.bell />
→ <x-shape::icon name="lucide.bell" />
→ <x-shape::button icon="lucide.bell">
```

Flat is where two sets collide. Sharing `icon/` works for as long as the names
don't overlap, and the run that overlaps is refused rather than allowed to
overwrite a drawing somebody else's set put there:

```
bell .. 4 drawing(s)       # Heroicons
compass .. 1 drawing(s)    # a second set
bell .. exists, kept       # a collision, refused
```

Refusing is the right answer to a collision and a poor way to find out about
one, since the second set has nowhere to go and half of it is already written.
So the subdirectory is not something to remember: the set that is not yours is
kept out of the way by default, and `icon/bell.blade.php` stays the library's to
give.

A set that should be found under something other than its own name says so, and
it says so in the config rather than on the command line, because where a set
lives is true of the set and not of the run that generated it:

```php
'lucide' => [
    'namespace' => 'lucide-icons',
    'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
    'styles' => [
        'outline' => ['base' => '{name}.svg'],
    ],
],
```

A flag is remembered for one run: `shape:icon bell --set=lucide --namespace=x`
and then the same command without it wrote a second copy somewhere else, and
pinned it in a second lockfile — silently, because the "exists, kept" check only
looks in the directory the run resolved to.

`--namespace` is still there on `shape:icon` and `shape:icon:all` as the
override for a one-off run, and `--namespace=` with nothing after it is the way
to say a run is flat about a set that normally isn't. One lower-case segment
either way: `../..` is refused rather than allowed to write outside the
components path, whether it comes from the flag, from the set's `namespace`, or
from the set's name standing in for one.

`shape:icon:replace` is the exception, and needs none of this. It is one set
standing in for the library's own for the length of a run, and the names it
writes are flat by definition — `shape::icon.shape-close` is what the close
button asks for, and a file under `icon/lucide/` answers to something else. So
it writes flat whichever set it reads:

```bash
php artisan shape:icon:replace --set=lucide
```

Its `--namespace` therefore takes one value, the empty one, which is how a run
says *flat* about a set that normally isn't. A set that declares a `namespace`
is the one case that refuses outright, with a sentence saying so, since a
namespaced replacement would write fourteen files and change nothing.

`shape:icon:status` reports every directory that holds a lockfile, not just the
one the run resolved to, so a namespaced set is not a blind spot:

```bash
php artisan shape:icon:status
```

Nesting is a path and nothing else: a namespaced icon folds exactly as a
top-level one does, including into the fold of a button it sits inside, because
every generated file carries its own `@blaze` front matter and that is what
Blaze reads. `shape:eject`, `shape:doctor` and Tailwind's `@source` all recurse
already.

Reach for a `namespace` only to rename the subdirectory a supplementary set
already has, which is why no shipped set declares one. Flat belongs to the set
the library is on and to nothing else: one spelling at every call site for the
icons an application actually reaches for, and no collisions by construction.
Note that a bare set name is a component that doesn't exist —
`<x-shape::icon name="lucide" />` raises Blade's usual "unable to locate" error
rather than rendering nothing, because Blade resolves a directory to the view
inside it named after the directory, and a set folder has no such file.

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

A run that writes anything clears the compiled views, and says so. A Blade
component is a file and a compiled view is a cached answer about a file, so
generating a component while an answer about the old one is still cached is the
ordinary staleness problem — except that it raises no error. What reaches a page
is stale markup, and under a folding compiler it can be markup inlined from a
component that no longer exists. It costs one recompile, which is what editing
any Blade file by hand costs. `shape:eject` does the same.

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
    "files": ["modal/modal.blade.php"],
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
