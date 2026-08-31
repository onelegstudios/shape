# Tasks

Planned work on `shape:icon`, in dependency order. Each file states what is true
now, what changes, what is already settled, and how it is tested.

| | Task | Depends on | State |
| --- | --- | --- | --- |
| 01 | [Icon slots, so Shape's own names stop impersonating a vendor](01-icon-slots.md) | — | **landed** |
| 02 | [Stop the docs promising a catalogue](02-docs-vocabulary.md) | 01 | **landed** |

01 renamed the twelve icons the library draws itself, promoted the spinner to a
fourteenth slot, and turned `aliases` into a per-set `slots` map under a declared
`icon_slots` list. 02 rewrote the documentation that presented those
icons, plus three example ones, as a single flat catalogue.

01 carried the docs far enough that nothing in them was false: every renamed
name was moved, `docs/tooling.md` describes slots rather than aliases, and
`docs/components/icon.md` gained the two-tier split and the override section.
02 did the editorial pass over the rest. `icon-gallery` is gone — a preview is
printed as its own example, so a strip of fourteen icons was the catalogue
picture quoted back as code, above a table that says more. The icon page now
opens on generation rather than on "call the one you want by name", and the two
places that had gone on naming icons 01 renamed — the `shape:eject` transcript in
`docs/tooling.md` and the workbench gallery, which 500s — are fixed and, in the
workbench's case, now tested.

## The problem, in one paragraph

Shape drew twelve icons in components of its own, and spelled all twelve the way
Heroicons spells them. `aliases` kept that spelling when another set was
substituted, so `--replace --set=lucide` wrote a Lucide drawing into
`exclamation-triangle.blade.php`. The name said one vendor, the drawing was
another's, and the only thing recording the truth was a comment nobody greps.
Slot names — `shape-warning`, not `exclamation-triangle` — say what the file is
*for* instead of guessing at what drew it, and leave the vendor's own vocabulary
free for the user.

## What is already settled

- **Slots fold.** A slot is still a literal component name, so
  `@blaze(fold: true, memo: true)` behaves exactly as it does today. A config
  role map would not have folded, which is why this is a rename and not a lookup.
- **Two tiers, not one.** Slots (`shape-*`, fourteen of them) are framework and
  `--replace` regenerates them. The three examples — `shape-arrow-right`,
  `shape-plus`, `shape-trash` — are there so the README and previews render, and
  `--replace` correctly ignores them: they are not the library's to keep level
  with your set. *Revised during 01 on one point only: they are prefixed now.
  Unprefixed, `icon="trash"` resolved, drew, and stayed a Heroicon beside
  thirteen Lucide slots, with `shape:doctor` silent because coverage is a slot
  question. Prefixed, the bare names are free for whatever an application
  generates, and reaching for one it never generated is an error.*
- **The slot list is declared, not derived.** `icon_slots` sits beside
  `icon_sizes` at library level, for the same reason that one was hoisted out of
  the sets. `Registry::icons()` inverts from source-of-truth to check: every
  `shape-*` drawn in a component must be declared.
- **Overriding a slot already works.** Publish the config, repoint the slot,
  regenerate — or hand-write the file, which `components_path` resolves first and
  the generator will not clobber without `--force`. This work makes the keys
  legible and the failure loud; it adds no mechanism.
- **The set stays a compile-time input.** Everything `slots` decides is a literal
  in the generated file. Nothing here reads `shape.icon_sets` at render time.
- **Free now, expensive later.** No tags exist; `v0.1.0` is unreleased.

## Decided

- `shape-checked`, not `shape-check`.
- `minus` becomes two slots, `shape-indeterminate` and `shape-trend-flat`.
- `shape-loading` is a slot, but a **packaged** one: the package keeps shipping
  its own hand-drawn spinner, a set may name a drawing to shadow it
  (`loader-circle`), and a set with nothing that reads as a loader says so and
  falls back. Heroicons falls back — `arrow-path` is a circular arrow, not a
  spinner. This is also what forced `icon_slots` to be declared rather than
  derived: nothing in the library draws `loading`, so no scan of the markup would
  ever find it.
- A user overrides a slot through the published config, not through a one-off
  command-line form: a slot overridden on the command line silently reverts on
  the next `--replace`.

What is left open is smaller, and marked in each file: whether `Registry` grows a
`slots()`/`extras()` split. `shape:doctor` does now report what it finds outside
the slots, which settles the other one.
