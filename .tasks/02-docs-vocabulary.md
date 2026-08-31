# Stop the docs promising a catalogue

## Why

`docs/components/icon.md:83` is headed **"Available icons"** and lists all
sixteen names flat, with `docs/previews/icon-gallery.blade.php` rendering them
side by side underneath. That reads as inventory to choose from.

It is not inventory. Three of those sixteen — `arrow-right`, `plus` and `trash` —
are drawn by nothing in the library. They ship so that
`README.md:99`, `README.md:101` and the previews render for someone who has
installed the package and configured nothing. They are examples that had to be
real files.

The catalogue reading is what puts a user in the mixed-set state *by following
the documentation*. They read the list, write `icon="trash"`, and after
`shape:icon --replace --set=lucide` get a Heroicons trash can beside fourteen
Lucide slots. `shape:doctor` will not mention it, because coverage is a slot
question and `trash` is not a slot.

They cannot stop shipping — the README's own examples would fail to resolve, and
`<x-shape::icon :name="trash" />` is a hard "unable to locate component", not a
blank. So the fix is the framing, not the files.

## The change

Split the section along the tiers `01` establishes.

**What Shape draws for you** — the fourteen `shape-*` slots, listed with the
component that resolves each. This is the list that matters when swapping sets,
and it is the list `shape:doctor` checks. Say that. `shape-loading` is the odd
one twice over — no component resolves it, and the package draws it rather than
sourcing it from a set — so it wants a sentence, not an empty cell: Shape ships a
spinner, your set may replace it, and Heroicons deliberately does not.

**How to override one** — the section `01` establishes and this task has to
document, because it is the answer to "I want a different tick" and nothing
currently says it exists: publish the config, change the slot's entry, regenerate
with `--force`. The hand-written escape hatch gets a mention and a warning that
an ejected slot missing `@blaze(fold: true, memo: true)` stops folding silently.

**Everything else you generate** — lead with the command rather than the
inventory:

```bash
php artisan shape:icon bell --set=lucide
php artisan shape:icon bell --from=resources/icons
```

The three extras get named as what they are — the icons these examples happen to
use, generated the same way as anything else — rather than as a list with a
heading over it. `icon-gallery` either goes, or becomes a preview of the slots
alone, where a gallery is genuinely informative.

## Files

- `docs/components/icon.md:83-92` — the section above; drop "Available icons".
- `docs/previews/icon-gallery.blade.php` — slots only, or delete.
- `docs/tooling.md:426` — the paragraph arguing for "one spelling at every call
  site" is about `aliases` and describes the design being replaced.
- `docs/components/badge.md`, `stat.md`, `button.md`, `empty.md`, `modal.md`,
  `drawer.md`, `confirm.md`, `feedback.md`, `overlays.md`, `_index.md` — tone and
  slot references move to `shape-*`. Example props using extras (`icon="trash"`,
  `icon="plus"`, `icon="arrow-right"`) stay unprefixed and *should*: they teach
  that the prop takes any icon name, which is the whole lesson.
- `docs/previews/` — ~15 files, same split.
- `README.md:103` — `<x-shape::icon.check-circle size="sm" />` becomes a slot or
  an extra depending on what it is illustrating. It is illustrating the static
  form, so an extra is the better example.
- `resources/boost/skills/shape-development/SKILL.md` — regenerate last, via
  `package-generate-skill`, once the docs are settled.

## Already settled

- The extras keep shipping. This task changes no `.blade.php` under
  `resources/views/shape/icon/`.
- `--replace` continues to skip them. That is correct behaviour, not an
  oversight: nothing in the library resolves them.

## Open decisions

1. **Whether `shape:doctor` should mention extras at all.** A line saying "3
   packaged icons are not part of the replace set; generate them from your set if
   you use them" closes the last silent path. Against: doctor cannot know whether
   the app renders them, so it would be advice, not a finding.
2. **Whether the three extras should shrink.** `arrow-right`, `plus` and `trash`
   exist only because examples needed them — three is already restraint, but it
   is worth confirming nothing else has crept in as the previews grow.

## Risks

- Landing `01` without `02` leaves the documentation describing a vocabulary the
  code no longer has. Landing `02` without `01` is harmless but pointless.
- The `@docs('preview', …)` blocks compile through the registry, so a preview
  naming a renamed slot fails the docs build rather than drifting quietly. That
  is the good case — lean on it.

## Tests

- The docs build resolves every component named in a preview. Confirm the
  existing build covers this; if it does not, that gap matters more than this
  task.
- `IconTest` — an extra still resolves from the package when the ejected
  directory holds only slots. This is the state a user is in immediately after
  `--replace`, and it should render, not error.
