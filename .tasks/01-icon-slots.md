# Icon slots, so Shape's own names stop impersonating a vendor

## Why

Twelve icons are drawn by Shape's own components, and all twelve are spelled the
way Heroicons spells them:

| Name | Drawn by |
| --- | --- |
| `check` | `checkbox.blade.php:70` |
| `minus` | `checkbox.blade.php:71`, `stat.blade.php:45` |
| `chevron-left`, `chevron-right` | `pagination.blade.php:80-105` |
| `chevron-down` | `select/index.blade.php:90,103` |
| `x-mark` | `overlay/close.blade.php:35`, `alert.blade.php:84`, `toast.blade.php:77` |
| `check-circle`, `x-circle`, `exclamation-triangle`, `information-circle` | the tone maps in `alert.blade.php:44-47`, `badge/index.blade.php:36-39`, `toast.blade.php:39-42` |
| `arrow-trending-up`, `arrow-trending-down` | `stat.blade.php:43-44` |

`aliases` exists to sustain that spelling. It moves the *source* file and leaves
the name, so `shape:icon --replace --set=lucide` writes a Lucide drawing into
`icon/exclamation-triangle.blade.php`. The generated header records the truth —
`{{-- Lucide (https://lucide.dev), ISC licensed. --}}` — and the filename
contradicts it. Filenames are what people search.

The failure is concrete. A user who knows Lucide looks for `triangle-alert`,
does not find it, runs `shape:icon triangle-alert --set=lucide`, and now holds
two files with the same drawing and nothing to say which one the alert depends
on. They will download it again either way; the point is that afterwards it is
clear which is which.

Naming the twelve for their *role* removes the impersonation at the source.
`shape-warning` is a slot Shape fills; `triangle-alert` is an icon the user
places. They render the same paths today and are free to diverge — a user who
replaces their own `check` does not silently change what the checkbox draws.

## The change

Two tiers in `icon/`, and the name says which:

- **Slots** — `shape-*`, fourteen of them, what Shape resolves on the user's
  behalf. Generated as a unit by `--replace`, an error in `shape:doctor` when
  unfilled.
- **Extras** — `arrow-right`, `plus`, `trash`. Not framework. They ship so that
  `README.md:99-101` and `docs/previews/` render for someone who has installed
  the package and configured nothing. `--replace` skips them, by design: nothing
  in Shape resolves them, so a set that lacks them breaks nothing.

| Now | Slot | Role |
| --- | --- | --- |
| `check` | `shape-checked` | the checkbox tick |
| `minus` | `shape-indeterminate` | the checkbox dash |
| `minus` | `shape-trend-flat` | a stat that did not move |
| `chevron-left` | `shape-prev` | the pager going back |
| `chevron-right` | `shape-next` | the pager going forward |
| `chevron-down` | `shape-expand` | the select's arrow |
| `x-mark` | `shape-close` | dismiss, on alert, toast and overlay |
| `check-circle` | `shape-success` | the success tone |
| `x-circle` | `shape-danger` | the danger tone |
| `exclamation-triangle` | `shape-warning` | the warning tone |
| `information-circle` | `shape-info` | the accent tone |
| `arrow-trending-up` | `shape-trend-up` | a stat that rose |
| `arrow-trending-down` | `shape-trend-down` | a stat that fell |
| `loading` | `shape-loading` | a spinner — **packaged**, see below |

The prefix is belt-and-braces once the second half is a role — no set ships an
icon called `warning` or `expand`. It is uniform anyway so that the rule holds
by eye and by `shape:doctor`, and reviewing the next slot does not require
knowing whether Lucide happens to spell something the same way.

`minus` becomes two slots. They render an identical dash today and are free to
diverge: a set may well want `square-minus` for a checkbox and `equal` for a
flat trend. One duplicated file is the price, and it is the model working rather
than a wart in it.

`aliases` becomes `slots`, and every set declares one — Heroicons included:

```php
'heroicons' => [
    'repo' => 'tailwindlabs/heroicons',
    'slots' => [
        'shape-checked' => 'check',
        'shape-indeterminate' => 'minus',
        'shape-trend-flat' => 'minus',
        'shape-prev' => 'chevron-left',
        'shape-close' => 'x-mark',
        'shape-success' => 'check-circle',
        'shape-warning' => 'exclamation-triangle',
        // no `shape-loading`: `arrow-path` does not read as a loader.
        // …
    ],
],
'lucide' => [
    'slots' => [
        'shape-checked' => 'check',
        'shape-indeterminate' => 'minus',
        'shape-trend-flat' => 'minus',
        'shape-prev' => 'chevron-left',
        'shape-close' => 'x',
        'shape-success' => 'circle-check',
        'shape-warning' => 'triangle-alert',
        'shape-loading' => 'loader-circle',
        // …
    ],
],
```

Heroicons losing its privileged status is the point, not a cost. The current
config reads "Shape's own components ask for Heroicons' spellings" — after this
they ask for Shape's, and every set answers the same question.

## Where the slot list lives

`shape-loading` forces a change that is right regardless of it.

`Registry::icons()` (`src/Registry.php:130`) derives the list by scanning markup
for names that appear in a component. Nothing in `resources/views/shape/` draws
`loading` — grep confirms it: the button has no loading state, and the file
exists purely to be used by an application. So a derived list can never contain
`shape-loading`, and `--replace` would never generate it.

So the list becomes declared, at library level, beside `icon_sizes`:

```php
'icon_slots' => [
    'shape-checked' => [],
    'shape-warning' => [],
    'shape-loading' => ['class' => 'animate-spin', 'packaged' => true],
    // …
],
```

This is the same move `icon_sizes` made when it was hoisted out of the sets: a
slot is a fact about the library, not about any one set. Every set answers the
same questions, and a set that cannot answer one is a coverage error rather than
a silently missing file.

`class` is there because the generator hardcodes
`Shape::classes('shrink-0')` (`src/Console/Commands/IconCommand.php:500`), and
`loading.blade.php:16` is `Shape::classes('shrink-0 animate-spin')`. The spin
belongs to the *slot* and not to the set — every set's loader spins — which is
why it is declared here once rather than repeated in every set map. It is the
only slot that needs it today; the key exists so the next one does not require a
generator change.

### A slot the package draws itself

`packaged` says Shape ships a drawing for this slot and no set is required to
supply one. It exists because `arrow-path` is a circular arrow, not a loader, and
generating `shape-loading` from Heroicons would be worse than what ships today.

The mechanism needs nothing new. The package keeps shipping
`shape-loading.blade.php` — the current hand-drawn spinner, renamed and still
hand-written — and `registerComponentPaths()` resolves the ejected directory
first. So:

- A set naming the slot (`'shape-loading' => 'loader-circle'`) generates a file
  into `components_path`, which shadows the packaged one. Lucide does this.
- A set omitting it, or naming it `null`, writes nothing, and the packaged
  spinner resolves. Heroicons does this.

`null` and omission are worth keeping distinct for every *other* slot: `null` is
a set saying "I know, I have no drawing for this" and omission is an oversight.
For a `packaged` slot both are fine, because falling back is the designed
outcome rather than a hole.

The reason this is clean for `shape-loading` and only for it: the packaged
spinner belongs to no set. Its own comment calls it "the one icon in this set
that is not from Heroicons, and the one that is not generated" — a circle and a
90° arc, geometric enough to sit beside anything. A `null` on `shape-trend-up`
would fall back to a *Heroicon*, which is the mixed-set bug this whole task is
about. So `null` on an ordinary slot stays a `shape:doctor` warning; `packaged`
is the only thing that makes silence correct.

That distinction has to reach the output. `DoctorCommand.php:90-94` currently
says a missing icon "falls back to the icon this package ships, so those
components render in Heroicons while the rest of the page renders in yours" —
true for thirteen slots and wrong for `shape-loading`, where the packaged drawing
is the right answer. Three states, not two: **generated** from your set,
**packaged** by design, **missing** and rendering a Heroicon by accident.

**`Registry::icons()` does not go away — it inverts.** It stops being the source
of truth for `--replace` and becomes the check on it: every `shape-*` name found
in the markup must be declared in `icon_slots`. That keeps the property its
docblock argues for — a component that starts drawing an undeclared slot fails
loudly instead of quietly resolving to the package — while letting a declared
slot exist that no component draws. Derived ⊆ declared, asserted in a test and
in `shape:doctor`.

## Overriding what Shape draws

Checked against the code as it stands. Three paths, and two of them already work
without anything new:

**Swap the whole set.** `shape:icon --replace --set=lucide`. Works today and is
what `--replace` is for.

**Point one slot at a different drawing.** This is what `slots` *is*, and it is
strictly better than `aliases` for the purpose. `config/shape.php` is publishable
under the `laravel-shape-config` tag (`ShapeServiceProvider.php:47-49`), so a
user publishes it, changes `'shape-warning' => 'octagon-alert'`, and runs
`shape:icon shape-warning --set=lucide --force`. Declarative, survives
regeneration, and the key says what is being overridden — today the same edit is
keyed `exclamation-triangle`, a vendor word that says nothing about where it is
used.

**Hand-write a slot.** Drop `resources/views/shape/icon/shape-warning.blade.php`
into `components_path`. Both halves already hold: `registerComponentPaths()`
(`ShapeServiceProvider.php:84-92`) registers the ejected path first, so it wins
everywhere including inside Shape's own components; and the generator reports
`exists, kept` rather than overwriting without `--force`
(`IconCommand.php:163-167`). A hand-written slot survives the next `--replace`.

**The gap.** Hand-writing means reproducing the generator's boilerplate by hand —
`@blaze` front matter, the `variant`/`size` props, the size `match`, the
`[:where(&)]:size-*` classes. That is a lot to get right for "I want a different
tick", and a hand-written file that omits `@blaze(fold: true, memo: true)` stops
folding with nothing to say so. Two things close it, both cheap:

- The error when a set has no entry for a slot should name the config key and
  the set, not just report `no SVG found` per name as it does now
  (`IconCommand.php:155-159`). Path two is the answer to most of these, and the
  error is where a user finds out it exists.
- `docs/components/icon.md` documents path two as *the* override, and path three
  as the escape hatch for a drawing no set has.

A one-off CLI form (`shape:icon shape-warning=octagon-alert`) is deliberately
not proposed. The config edit is durable and the one-off is not, and a slot
overridden on the command line is one that silently reverts on the next
`--replace`.

## What this deletes

`IconSet::canonicalNames()` (`src/IconSet.php:344`) exists to run the alias map
backwards for `--all`, and its third case is the hairiest thing in the file: *a
file whose name is itself an alias key is written under nothing*, because a
Heroicons-named `check-circle.svg` sitting beside Lucide's `circle-check.svg`
would shadow the alias with the wrong glyph.

With slots that case cannot arise. Slot names live in a namespace no set uses,
so `--all` writes every file under its own name, flat, with nothing to reverse.
The reverse map goes away and `--all` gets simpler and more predictable.

## Files

- `config/shape.php:41` — add `icon_slots` beside `icon_sizes`.
- `config/shape.php:131` — `aliases` → `slots` on both sets; Heroicons gains one.
  Rewrite the `aliases` paragraph in the header comment: it currently argues for
  translation-so-call-sites-can-stay, which is the thing being removed.
- `src/IconSet.php:321` — `sourceName()` reads `slots`.
- `src/IconSet.php:344` — `canonicalNames()` loses the alias reversal.
- `src/Console/Commands/IconCommand.php:500` — the hardcoded
  `Shape::classes('shrink-0')` takes the slot's declared `class`.
- `src/Console/Commands/IconCommand.php:738` — `--replace` reads declared slots
  rather than `$registry->icons()`.
- `src/Console/Commands/IconCommand.php:155` — the `no SVG found` line becomes a
  real error for a slot: name the set and the config key.
- `src/Console/Commands/IconCommand.php:115` — the `--replace` + `--namespace`
  guard still holds; slots are flat.
- `src/Registry.php:130` — `icons()` inverts to the derived-⊆-declared check.
- `src/Registry.php:166` — `vocabulary()` unchanged, but it now spans both tiers;
  a `slots()`/`extras()` split would serve doctor better.
- `src/Console/Commands/DoctorCommand.php:117` — coverage is a slot question.
  Extras, if present in the ejected directory, are worth a line of their own
  rather than silence.
- `resources/views/shape/` — 8 components, ~20 call sites. Both forms: the folded
  literals (`checkbox`, `pagination`, `select`) and the quoted tone-map arms
  (`alert`, `badge`, `toast`, `stat`, `overlay/close`).
- `resources/views/shape/icon/` — 12 renames; `loading.blade.php` renamed to
  `shape-loading.blade.php` and left hand-written; 3 extras untouched.
- `resources/registry.json` — regenerate; 17 entries become 18.
- `resources/boost/skills/shape-development/SKILL.md` — regenerate via
  `package-generate-skill` after the docs land.
- Tests: `IconSetTest`, `IconCommandTest`, `DoctorCommandTest`, `IconTest`,
  `Blaze/NamespacedIconTest`, `Blaze/DynamicIconTest`, `Blaze/FoldingTest`, and
  the `tests/fixtures/views/` fixtures.

Docs are `02-docs-vocabulary.md`.

## Already settled

- **Slots fold.** `<x-shape::icon.shape-checked />` is a literal component name,
  so it folds and memoizes exactly as `icon.check` does now. This is why the
  rename beats the `icon_roles` config map considered first: a runtime role
  lookup cannot reach the folded call sites in `checkbox` and `pagination`.
- **The set stays a compile-time input.** `slots` and `icon_slots` are spent
  while `shape:icon` writes a file. Nothing added here may read
  `shape.icon_sets` at render time.
- **Overriding a slot already works two ways** — see above. This task makes the
  keys legible and the failure loud; it does not add a mechanism.
- **Free now, expensive later.** No tags exist and `v0.1.0` is unreleased in
  `CHANGELOG.md`. This is a rename now and a migration guide after the first tag.

## Risks

- **A stale generated slot keeps shadowing.** Generate `shape-loading` from
  Lucide, then change the set's entry to `null`: the ejected file stays and keeps
  winning, because `--replace` writes files and never removes them. Doctor can
  see this — an ejected slot the set no longer names — and it is worth a line
  there rather than machinery in the command.
- **A set's loader may not read as one.** `loader-circle` is a dashed ring and
  spins well; other sets ship a static hourglass or an arrow that looks wrong in
  motion. `packaged` is the escape hatch, so this is a documentation problem
  rather than a design one: say that a slot naming a set drawing should be looked
  at spinning before it is kept.
- **A published config breaks.** A consumer with `aliases` in a published
  `config/shape.php` gets no slots and a `--replace` that writes nothing useful.
  `IconSet::fromArray()` should reject `aliases` with a message naming `slots`,
  not ignore it. Same for a published config with no `icon_slots`.
- **A half-done rename is invisible.** A component still asking for
  `icon.check-circle` resolves to the package and draws perfectly — the same
  silence `shape:doctor` was built for. The regenerated `registry.json` plus a
  grep for the twelve old names is the gate.
- **Docs drift.** ~28 references across `docs/` and the previews. `02` covers it;
  landing `01` alone leaves the documentation describing a vocabulary that no
  longer exists.

## Tests

- `IconSetTest` — a slot resolves to the set's file; a set missing a slot is
  reported with the config key named, not guessed; `canonicalNames()` no longer
  reverses anything.
- `RegistryTest` — every `shape-*` drawn in a component is declared in
  `icon_slots`. This is the derived-⊆-declared assertion, and it is what keeps
  the declared list honest as components change.
- `IconCommandTest` — `--replace --set=lucide` writes fourteen `shape-*` files
  and touches none of the extras; `--replace --set=heroicons` writes thirteen and
  leaves `shape-loading` alone; a hand-written slot is kept without `--force` and
  overwritten with it; a generated `shape-loading` carries `animate-spin` and no
  other slot does; the generated header still carries Lucide's notice; `--all`
  writes a set's files flat under their own names with no shadowing case.
- `IconTest` — with an ejected directory holding every slot but `shape-loading`,
  `<x-shape::icon.shape-loading />` resolves to the packaged spinner and still
  spins. This is the state Heroicons users are in permanently.
- `DoctorCommandTest` — an ejected directory missing one slot errors and names
  it; a `packaged` slot with no ejected file is reported as packaged and does
  **not** error, and does not claim the page renders in Heroicons; a `null` slot
  warns; an ejected directory holding extras from another set is reported, not
  failed.
- `Blaze/FoldingTest` — a renamed slot still folds to one `<svg>`. This is the
  regression that would make the rename expensive rather than free.
- Regenerate the thirteen sourced slots and diff the drawings against the
  committed icons: paths should be byte-identical, only filenames move.
  `shape-loading` is not regenerated at all under Heroicons, so the committed
  spinner should come through the whole task untouched but for its name.
