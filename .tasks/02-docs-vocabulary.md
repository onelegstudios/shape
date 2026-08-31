# Stop the docs promising a catalogue

## Why

> **Partly closed by 01.** The mixed-set hole this task opened with is shut by
> renaming rather than by reframing: the three are `shape-arrow-right`,
> `shape-plus` and `shape-trash` now, so `icon="trash"` no longer resolves to
> anything and the bare names are free for whatever an application generates.
> They are still not slots and `--replace` still leaves them alone — they are not
> the library's. What remained here was the framing work, which 01 started.

`docs/components/icon.md:83` was headed **"Available icons"** and listed all
sixteen names flat, with `docs/previews/icon-gallery.blade.php` rendering them
side by side underneath. That read as inventory to choose from.

It was not inventory, and the catalogue reading put a user in the mixed-set state
*by following the documentation*. They read the list, wrote `icon="trash"`, and
after `shape:icon --replace --set=lucide` got a Heroicons trash can beside
fourteen Lucide slots. `shape:doctor` did not mention it, because coverage is a
slot question and `trash` was not a slot.

01 closed that with the prefix: `trash` no longer resolves at all, so the
documentation cannot lead anybody into it, and a reader who wants a trash can is
told to generate one. The three still ship — the README's own examples need real
files — under names nobody would reach for by accident.

## The change

*01 did most of this. What is written below is what it left, and what 02 did
with it.*

**What Shape draws for you** — the fourteen `shape-*` slots, listed with the
component that resolves each. *Done in 01.*

**How to override one** — publish the config, change the slot's entry,
regenerate with `--force`; the hand-written escape hatch and its silent
fold-loss. *Done in 01.*

**Everything else you generate** — lead with the command rather than the
inventory. *Done in 01.*

**The page's own first sentence.** It still said "One component per icon, from
Heroicons. Call the one you want by name" — the catalogue reading, in the
position a reader trusts most, above everything 01 had rewritten. It now leads
with generation: every icon is a component generated from a set, Shape ships the
fourteen its own components draw, and `shape:icon` writes the rest. *Done.*

**The gallery is gone.** `docs/previews/icon-gallery.blade.php` is deleted along
with the `@docs('preview')` call in `icon.md`. A preview on this site is printed
underneath itself as the example — that is the point of previews being Blade
files — so fourteen `<x-shape::icon.shape-* />` lines are inventory rendered
*and* inventory quoted, directly above a table that names each slot with what
resolves it. The table says strictly more. The picture of the fourteen belongs in
the workbench gallery, where a wall of glyphs is the thing being served, and it
is there, labelled and read out of `shape.icon_slots`. *Settled.*

**Why the previews say `shape-*` at all.** The honest answer is now written down
twice — once in `icon.md`, once on the button page, which is where a reader meets
an icon prop first. The site has to render for somebody who has generated
nothing; that is a fact about the documentation, not a recommendation for a call
site. Without it, every preview on the site quietly teaches the opposite of what
`icon.md` says. *Done.*

## Files

- ~~`docs/components/icon.md`~~ — intro rewritten; the gallery call removed; the
  "not a catalogue" paragraph now says why the previews name `shape-*` anyway.
- ~~`docs/previews/icon-gallery.blade.php`~~ — deleted.
- ~~`docs/components/button.md`~~ — the `## Icons` section says where icon names
  come from, with the `shape:icon plus arrow-right` that produces them.
- ~~`docs/components/*.md`~~ — every `any icon name` in a reference table links
  to `icon.md`: `badge`, `alert`, `toast`, `dropdown`, `tabs`, `empty`, `button`,
  and `list`/`table`, whose `empty-icon` rows said only "icon name for it".
- ~~`docs/previews/tooltip.blade.php`~~ — a trash can labelled "Archive" is now a
  delete button labelled "Delete".
- ~~`docs/previews/tabs-icons.blade.php`~~ — a plus on a "Plan" tab and an arrow
  on "Invoices" are now "New" and "Sent", which those two drawings mean.
- ~~`docs/components/drawer.md`~~ — the shared-trigger example carried
  `icon="shape-plus"` on a "Cart" trigger, illustrating nothing. Dropped.
- ~~`docs/_index.md`~~ — the icon row says "generated from your set".
- ~~`README.md`~~ — 01 moved the names; 02 added the paragraph that was missing
  entirely: fourteen of the generated components are the slots, `--replace`
  swaps them as a unit, everything else is yours.
- ~~`docs/tooling.md`~~ — two things 01 left. The `shape:eject modal` transcript
  showed `icon/check.blade.php`, a file that no longer exists under a name that
  no longer resolves; it now shows what the command actually prints, including
  that ejecting `icon` takes the whole directory, and the prose says why.
  "which there is the designed answer" was a sentence nobody finished.
- ~~`workbench/resources/views/preview.blade.php`~~ — the icon wall still looped
  over `check`, `check-circle`, `x-mark`, `exclamation-triangle`, `chevron-down`
  and `loading`, and a button still asked for `check-circle`. Six of those names
  had been gone since 01, and the page 500s on the first of them. It reads
  `shape.icon_slots` now, so it cannot rot again.
- ~~`resources/boost/skills/shape-development/SKILL.md`~~ — regenerated. Its
  examples named `shape-plus` in exactly the position a consuming agent copies
  from; they name `bell` now, and an anti-pattern says not to put a `shape-*`
  name in an application's own markup.

## Already settled

- The three keep shipping, now as `shape-*` slots. This task changed no
  `.blade.php` under `resources/views/shape/icon/`.
- `--replace` covers the slots. Revised in 01: skipping them was the mixed-set
  hole, not correct behaviour.

## Decisions this task closed

1. ~~**Whether `shape:doctor` should mention extras at all.**~~ Settled in 01.
2. ~~**Whether the three should shrink.**~~ No. `resources/views/shape/icon/`
   holds `index` plus fourteen slots plus exactly those three, nothing has crept
   in, and `RegistryTest` asserts the list. All three are load-bearing in the
   README alone — the button examples use `shape-plus` and `shape-trash`, the
   link button uses `shape-arrow-right` — so shrinking the set means deleting an
   example, not deleting an icon.

## Tests

- The docs build resolves every component named in a preview:
  `DocsPreviewTest` renders each file and asserts both directions of the
  page↔preview mapping, so deleting `icon-gallery` had to be done in both places
  or the suite says so. It was already covered.
- The gap that mattered more was next door and had no test at all. The workbench
  gallery is the same kind of file — call sites that rot — and nothing rendered
  it, which is how it kept six renamed icon names for a whole task.
  `WorkbenchGalleryTest` renders `/` and `/seed` through `WithWorkbench`. Checked
  against the actual rot: restoring `icon="check-circle"` fails it with *Unable
  to locate a class or view for component [shape::icon.check-circle]*.
- ~~`IconTest` — an extra still resolves from the package when the ejected
  directory holds only slots.~~ Done in 01, and its converse matters more:
  `IconTest` asserts no *unprefixed* name resolves, so `icon="trash"` fails
  loudly instead of drawing a Heroicon beside thirteen Lucide slots.
