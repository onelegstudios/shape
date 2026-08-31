# Stop the docs promising a catalogue

## Why

> **Partly closed by 01.** The mixed-set hole this task opened with is shut by
> renaming rather than by reframing: the three are `shape-arrow-right`,
> `shape-plus` and `shape-trash` now, so `icon="trash"` no longer resolves to
> anything and the bare names are free for whatever an application generates.
> They are still not slots and `--replace` still leaves them alone — they are not
> the library's. What remains here is the framing work, which 01 started.

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

*01 did most of this. What is written below is what it left.*

**What Shape draws for you** — the fourteen `shape-*` slots, listed with the
component that resolves each. This is the list that matters when swapping sets,
and it is the list `shape:doctor` checks. Say that. `shape-loading` wants a
sentence rather than an empty cell: no component resolves it, Shape ships the
spinner, your set may replace it, and Heroicons deliberately does not.
*Done in 01.*

**How to override one** — the answer to "I want a different tick", which nothing
said existed: publish the config, change the slot's entry, regenerate with
`--force`. The hand-written escape hatch gets a mention and a warning that an
ejected slot missing `@blaze(fold: true, memo: true)` stops folding silently.
*Done in 01.*

**Everything else you generate** — lead with the command rather than the
inventory:

```bash
php artisan shape:icon bell --set=lucide
php artisan shape:icon bell --from=resources/icons
```

The three are no longer named as anything a reader should use. The page says
they exist, says why they are prefixed, and points anyone who wants icons at
`shape:icon`. *Done in 01.*

`icon-gallery` is a preview of the fourteen slots now, which is where a gallery
is genuinely informative — but it is worth looking at before keeping. *Open.*

What is still open is the rest of the prose: the previews, and whether the
component pages read as "here is the list" or "here is what Shape keeps level
with your set".

## Files

Every reference below was renamed in 01, so nothing here is now false. What is
listed is where the *framing* still reads as a catalogue:

- ~~`docs/components/icon.md:83-92`~~ — done: "Available icons" is gone, replaced
  by "What Shape ships", "Overriding one" and "Everything else you generate".
- `docs/previews/icon-gallery.blade.php` — is slots-only now; decide whether
  seventeen in a row is a useful picture or just a wall.
- ~~`docs/tooling.md`~~ — done: the alias section is now "Slots, and replacing
  Shape's own icons".
- `docs/components/badge.md`, `stat.md`, `button.md`, `empty.md`, `modal.md`,
  `drawer.md`, `confirm.md`, `feedback.md`, `overlays.md`, `_index.md` — the
  names moved; the surrounding prose has not been re-read. Note that example
  props can no longer teach "the prop takes any icon name" with a shipped icon,
  since every shipped icon is `shape-*` — `shape:icon bell trash` is where that
  lesson lives now, and deliberately so.
- `docs/previews/` — renamed, not re-read. Some read oddly:
  `tooltip.blade.php` labels a trash can "Archive", and `tabs-icons.blade.php`
  puts a plus on a "Plan" tab.
- ~~`README.md`~~ — done.
- `resources/boost/skills/shape-development/SKILL.md` — regenerated in 01;
  regenerate again via `package-generate-skill` once the docs settle.

## Already settled

- The three keep shipping, now as `shape-*` slots. This task changes no
  `.blade.php` under `resources/views/shape/icon/`.
- `--replace` covers them. Revised in 01: skipping them was the mixed-set hole,
  not correct behaviour.

## Open decisions

1. ~~**Whether `shape:doctor` should mention extras at all.**~~ Settled in 01:
   there are no extras, and doctor lists what it finds outside the slots, which
   is now only what the application generated for itself.
2. **Whether the three should shrink.** `shape-arrow-right`, `shape-plus` and
   `shape-trash` exist only because examples needed them — three is already
   restraint, but it is worth confirming nothing else has crept in as the
   previews grow. Each one is now a question every icon set has to answer.

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
- ~~`IconTest` — an extra still resolves from the package when the ejected
  directory holds only slots.~~ Done, and its converse matters more: `IconTest`
  asserts no *unprefixed* name resolves, so `icon="trash"` fails loudly instead
  of drawing a Heroicon beside thirteen Lucide slots.
