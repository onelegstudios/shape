# Tasks

Planned work on `shape:icon`, in dependency order. Each file states what is true
now, what changes, what is already settled, and how it is tested.

| | Task | Depends on |
| --- | --- | --- |
| 01 | [Icon slots, so Shape's own names stop impersonating a vendor](01-icon-slots.md) | — |
| 02 | [Stop the docs promising a catalogue](02-docs-vocabulary.md) | 01 |

01 renames the twelve icons the library draws itself, promotes the spinner to a
fourteenth slot, and turns `aliases` into a per-set `slots` map under a declared
`icon_slots` list. 02 rewrites the documentation that currently presents those
icons, plus three example ones, as a single flat catalogue. 02 is small, but 01
without it ships docs describing a vocabulary that no longer exists.

## The problem, in one paragraph

Shape draws twelve icons in components of its own, and spells all twelve the way
Heroicons spells them. `aliases` keeps that spelling when another set is
substituted, so `--replace --set=lucide` writes a Lucide drawing into
`exclamation-triangle.blade.php`. The name says one vendor, the drawing is
another's, and the only thing recording the truth is a comment nobody greps. Slot
names — `shape-warning`, not `exclamation-triangle` — say what the file is *for*
instead of guessing at what drew it, and leave the vendor's own vocabulary free
for the user.

## What is already settled

- **Slots fold.** A slot is still a literal component name, so
  `@blaze(fold: true, memo: true)` behaves exactly as it does today. A config
  role map would not have folded, which is why this is a rename and not a lookup.
- **Two tiers, not one.** Slots (`shape-*`, fourteen of them) are framework and
  `--replace` regenerates them; extras (`arrow-right`, `plus`, `trash`) are there
  so the README and previews render, and `--replace` correctly ignores them.
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
`slots()`/`extras()` split, and whether `shape:doctor` says anything about
extras.
