# Theming

Shape's theme layer is deliberately small. Tailwind already ships an opinion
about spacing, type and shadow — the same opinion, from the same authors, as the
book the rest of this library follows — so redefining any of it here would only
create a second scale to keep in tune with the first.

What is left is what Tailwind has no opinion about: a neutral ramp with a
temperature, a brand and an accent, four state colours, one radius decision, and
the per-surface foreground contract that stops de-emphasized text turning to mud
on a coloured background. That is the whole of `resources/css/shape.css`, and it is
all overridable from your own stylesheet.

Customisation escalates in three steps. This page is the first one in full:

1. **Tokens.** Redeclare Shape's `@theme` values in your own stylesheet.
2. **Utilities.** Pass any Tailwind class to any component; Shape's defaults
   carry zero specificity and yield to it.
3. **[Eject](tooling.md#shapeeject).** Copy a component into your application
   and own it outright.

## Where overrides go

The tokens are imported from `vendor/`, which is what keeps your own
declarations authoritative and leaves nothing to rebuild on an upgrade. Anything
you declare after the import wins:

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape.css";

@theme {
    --radius-shape: 0.25rem;
}
```

`vendor:publish --tag="laravel-shape-css"` copies the file into
`resources/css/shape.css` if you would rather own the token layer outright — but
then you own it, including every change the package makes to it later. Overriding
from your own file is the arrangement to prefer.

## What Shape owns

| Token | Default | What reads it |
| --- | --- | --- |
| `--color-shape-50` … `-950` | Tailwind's `mist` | Every neutral surface, border and rule |
| `--color-shape-brand-50` … `-950` | Tailwind's `cyan` | The brand tone, the focus ring, the active tab and page |
| `--color-shape-accent-50` … `-950` | Tailwind's `fuchsia` | The accent tone |
| `--color-shape-danger-*` | Tailwind's `red` | The danger tone |
| `--color-shape-info-*` | Tailwind's `blue` | The info tone |
| `--color-shape-success-*` | Tailwind's `green` | The success tone |
| `--color-shape-warning-*` | Tailwind's `yellow` | The warning tone |
| `--color-shape-*-fg`, `-fg-muted` | Ends of each ramp | What goes on top when a tone is used as a fill |
| `--radius-shape`, `--radius-shape-lg` | `0.5rem`, `0.75rem` | `rounded-shape`, `rounded-shape-lg` |
| `--text-2xs` | `0.6875rem` | Badges, avatar initials, table headings |

And what it deliberately does not own: the spacing scale, the rest of the type
scale, the shadow scale (see [Elevation](elevation.md)), and the font family.
Shape sets no `font-family` at all, so the components inherit whatever your
application has already chosen.

## Colour

Every ramp is *aliased* to a Tailwind colour rather than copied out of it:

```css
--color-shape-brand-700: var(--color-cyan-700, oklch(52% 0.105 223.128));
```

Which means there are two places to change a colour, and they mean different
things. Retinting `--color-cyan-*` moves Shape and every `text-cyan-600` already
in your application together. Redeclaring `--color-shape-brand-*` moves Shape
alone and leaves the rest of your palette where it is. The literal fallback is
there for the application that clears Tailwind's default palette with
`--color-*: initial`.

```css
@theme {
    /* Shape's brand becomes violet; your own `cyan-*` utilities are untouched. */
    --color-shape-brand-50: oklch(96.9% 0.016 293.756);
    --color-shape-brand-100: oklch(94.3% 0.029 294.588);
    /* … through … */
    --color-shape-brand-950: oklch(28.3% 0.141 291.089);
}
```

### The two steps a hand-written ramp has to get right

A ramp written by hand only has to satisfy two constraints, but they are not the
ones the naming suggests:

- **700 is the fill.** The filled tones take their `700` step and put white on
  it, so `700` is the step that has to clear 4.5:1 against white. Tailwind's
  `600` does not: white on `cyan-600` is 3.6:1 and on `green-600` is 3.2:1, both
  under AA. That is the whole reason the tones fill with 700 and not the 600 the
  numbering implies.
- **800 is the ink.** The `subtle` and `ghost` variants put the `800` step on the
  `100`/`200` tint, and it has to stay readable on the hover tint as well as the
  resting one. `700` on a `200` background lands between 3.7:1 and 4.8:1
  depending on hue; `800` clears 5.7:1 on all of them.

Warning is the standing exception. Taking `700` there gives an olive that no
longer reads as a warning, and the bright yellow that does read as one cannot
carry white text at all — so warning fills with `500` and takes dark text from
the bottom of its own ramp. If you retint warning, keep that shape.

### Brand and accent are two colours, not one

The brand is what the product looks like: the primary button, the focus ring,
the active tab, the checked switch. It is everywhere — which is exactly why it
cannot also be the colour that means *look here*. A `New` badge in the brand's
own cyan, on a page already full of cyan, announces nothing.

That second colour is `accent`, and it defaults to Tailwind's `fuchsia`. It is
for the things that should interrupt the eye and nothing else: the new feature,
the recommended plan, the rule under a section heading. Used sparingly, or it
stops working — the same rule the brand is under, one level up.

It is not derived from the seed, and that is the point. An accent that followed
the brand around the hue wheel would land next to it and stop standing apart. It
is still yours to retint, unlike danger and success — a magenta product will
want it moved — but move it *away* from the brand, and mind the arc between
280° and 350°, which is the only stretch the four state colours leave free.

```blade
<x-shape::badge label="New" tone="accent" />
<x-shape::button variant="subtle" tone="accent">Try the beta</x-shape::button>
```

Like the brand, it resolves no glyph. Neither is a state, and a badge that draws
an icon is claiming to be one.

### The state colours are not yours to rebrand

Danger has to be red and success has to be green whatever the brand is. Retint
them to match a palette by all means; do not repurpose them. They are also never
the only signal — the alert, the badge and the toast each resolve a glyph from
the tone, so the message survives greyscale.

`info` is in that set, and it is the one worth spelling out. It is blue and not
the brand even though the default brand is a cyan that would pass for one,
because the brand is the ramp this page has just finished inviting you to move.
Share it, and an informational alert is violet in a violet product and orange in
an orange one — where it reads as a warning. `tone="brand"` still means
emphasis, and resolves no glyph; `tone="info"` means "worth knowing", and stays
blue however far the brand travels.

## One colour, both ramps

For the common case — a brand colour, and neutrals that agree with it — there is
an optional second stylesheet that derives the whole palette from a single value:

```css
@import "tailwindcss";
@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape.css";
@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape-seed.css";

:root {
    --shape-seed: oklch(52% 0.16 300);
}
```

The brand takes the seed's hue and a chroma scaled per step; the neutrals take
its hue at a fixed, very low chroma, so the greys carry a cast that is felt
rather than seen. Which is exactly how Tailwind's `mist` relates to its `cyan` —
the same relationship, generated instead of looked up. The state colours are
**not** derived, for the reason above, and neither is the accent, for the one
just above that.

`npm run preview && composer serve` serves the gallery both ways: `/` is
`shape.css` alone, `/seed` adds this file.

### Why the contrast guarantee survives a hue change

Every step pins its OKLCH lightness and lets only hue and chroma come from the
seed. Lightness is what carries contrast, so pinning it is what makes the
guarantee hue-independent: `700` becomes a promise about contrast rather than a
position in a list.

That was verified by rendering it — 24 hues at four chromas, 96 seeds,
screenshotted in Chrome with the contrast of all fourteen foreground/background
pairings measured from the pixels. Worst case 4.61:1.

Measured rather than modelled on purpose. Computing the same numbers from the CSS
assumes a browser gamut-maps by holding the lightness and reducing the chroma. It
does not: asked for `oklch(71.5% 0.259 25)`, Chrome renders `L=65.7%` and
`C=0.229` — it trades lightness for chroma, which is the one thing a pinned
lightness cannot survive. Hence the chroma caps in the file, and hence the one
real constraint on the value you pass:

> **The seed must be inside sRGB.** A seed that is not — `oklch(52% 0.30 195)`,
> say — is gamut-mapped before any of this runs, and every step then derives from
> a colour other than the one you asked for. Those seeds measure as low as
> 3.15:1. There is no way to detect it in CSS, so it is a precondition rather
> than something the file can enforce.

### A section with its own brand

`[data-shape-seed]` re-derives the palette from whatever seed is in scope:

```blade
<section data-shape-seed style="--shape-seed: oklch(52% 0.19 25)">
    <x-shape::button variant="primary" tone="brand">Upgrade</x-shape::button>
</section>
```

Nothing inside it needs to know. Every component already reads the tone
variables, and the tone variables read the palette.

### Support

The seed layer is wrapped in `@supports (color: oklch(from red l c h))` —
relative colour syntax, which is Chrome 119, Safari 16.4 and Firefox 128. Below
that the block does not apply and the aliased palette in `shape.css` simply
stands, so nothing breaks; the brand is just cyan.

Importing the file without setting a seed leaves you close to where you started,
but not identical. A Tailwind ramp drifts its hue as it darkens — cyan runs 201°
at the `50` step to 230° at the `950` — and one seed carries one hue. The
neutrals land within 2/255 of `mist`, the brand within 17/255 at its most
saturated steps.

## The surface contract

Components never reach for a global grey. They read two variables, and every
surface exports the pair that belongs on it:

| Variable | Meaning |
| --- | --- |
| `--shape-fg` | The foreground for this surface |
| `--shape-fg-muted` | The de-emphasized foreground for this surface |
| `--shape-ring` | The focus ring |

`<x-shape::text variant="muted">` reads `--shape-fg-muted` rather than
`text-shape-500`, which is what makes "don't use grey text on a coloured
background" structurally impossible rather than merely documented. On a tinted
surface the muted foreground is the same hue dialled down, not grey.

`data-shape-surface` republishes the pair. The values are `brand`, `accent`,
`danger`, `info`, `success`, `warning` — the six filled surfaces — and `tint`,
which is the pale wash whose foreground is the tone's own ink rather than
white.

A surface of your own is two declarations:

```css
@layer base {
    [data-shape-surface='promo'] {
        --shape-fg: var(--color-white);
        --shape-fg-muted: color-mix(in oklch, var(--color-white) 76%, transparent);
    }
}
```

```blade
<x-shape::card data-shape-surface="promo" class="bg-violet-700">
    <x-shape::heading size="lg">Upgrade</x-shape::heading>
    {{-- Muted, and still legible, because it isn't grey. --}}
    <x-shape::text variant="muted">Cancel any time.</x-shape::text>
</x-shape::card>
```

## Tones

`variant` is hierarchy — where an action sits in the pyramid of importance.
`tone` is semantics. Keeping them apart in the markup would normally multiply
into a variant × tone class matrix, so the tone half lives in CSS instead: a
tone sets the variables, every variant reads them.

```css
[data-shape-tone='brand'] {
    --shape-tone: var(--color-shape-brand-700);
    /* … */
}
```

| Variable | Used by |
| --- | --- |
| `--shape-tone` | The `primary` fill; the progress bar; a checked control |
| `--shape-tone-hover` | That fill, hovered |
| `--shape-tone-fg` | What goes on the fill |
| `--shape-tone-ink` | The `subtle` and `ghost` label, and the tint surface's foreground |
| `--shape-tone-tint` | The `subtle` background, and the alert's |
| `--shape-tone-tint-hover` | That tint, hovered |
| `--shape-tone-surface`, `-surface-hover` | The `outline` variant's background |
| `--shape-tone-border` | The `outline` variant's border |

The tones are `neutral` (the default, and the bare `[data-shape-tone]` block),
`brand`, `accent`, `danger`, `info`, `success` and `warning`. Retinting a ramp
retones everything that reads it — a button, a badge, a checkbox and a progress bar all
move together, because there is one set of variables and not four component
palettes.

### A tone of your own

Declare the block in the components layer and pass its name as `tone`:

```css
@layer components {
    [data-shape-tone='brand'] {
        --shape-tone: var(--color-brand-700);
        --shape-tone-hover: var(--color-brand-800);
        --shape-tone-fg: white;
        --shape-tone-ink: var(--color-brand-800);
        --shape-tone-tint: var(--color-brand-100);
        --shape-tone-tint-hover: var(--color-brand-200);
    }
}
```

```blade
<x-shape::button variant="primary" tone="brand">Upgrade</x-shape::button>
```

The `neutral` block sets all nine variables; a tone that only overrides some of
them inherits the rest, which is why the five shipped tones are six lines each
and not nine.

One caveat, and it is small: the alert, the badge and the toast branch on `tone`
to resolve their glyph, so an unknown tone gets no icon. Pass `icon="…"`
explicitly on those three. Everything else only ever interpolates `tone` into
the attribute, which is also what keeps `:tone="$destructive ? 'danger' : null"`
on the fold path — see [Folding](folding.md).

## Dark mode

Shape ships a dark treatment and it follows the operating system. Three things
flip under `prefers-color-scheme: dark` in `shape.css` — the surface contract,
the whole tone block, and the progress track — and the components carry `dark:`
utilities for their own backgrounds and borders.

Elevation is the one thing that does not flip by token. Black at 10% opacity does
almost nothing against a dark surface, so elevation there reads through surface
colour and a ring instead; and because Tailwind resolves shadow values at build
time rather than referencing the custom property, redefining `--shadow-sm` under
a dark media query has no effect on the `shadow-sm` utility. See
[Elevation](elevation.md#dark-mode).

### If your application has a manual toggle

A class or attribute toggle needs both halves, and only one of them is yours to
configure.

The utilities half is a Tailwind setting — teach `dark:` to mean your selector,
and every `dark:` in Shape's Blade compiles against it, because your build is
already scanning the package's views:

```css
@custom-variant dark (&:where([data-theme="dark"], [data-theme="dark"] *));
```

The token half is not reachable that way: a media query cannot be re-pointed at a
selector from outside. Restate the blocks you want under your own selector, after
the import:

```css
@layer base {
    [data-theme='dark'] {
        --shape-fg: var(--color-shape-50);
        --shape-fg-muted: var(--color-shape-400);
        --shape-ring: var(--color-shape-brand-500);
    }
}
```

…and the same for the tone block and the progress track, which can both be
lifted verbatim out of `shape.css`.

Restate each block in the layer its original is in, and note that the three are
not all in the same one. The surface contract above is `@layer base`; the tone
block and the progress track are `@layer components`. Layer order is consulted
before specificity, so a tone restatement written in `base` loses to the plain
`[data-shape-tone]` in `components` however many attributes you give it — the
symptom is a page that turns dark around buttons and badges that stay light.
In the right layer the extra attribute is enough to win on its own.

This is the one place where theming means restating something Shape already says
rather than overriding it. If you find yourself doing it, publishing the
stylesheet (`vendor:publish --tag="laravel-shape-css"`) and rewriting the three
media queries in place is the tidier trade — at the cost of owning the file.

`workbench/resources/css/theme.src.css` in this repository is the whole thing
written out, including the second half a toggle needs but the media query hides:
a reader on a dark machine who asks for light needs the light values restated
too, since `prefers-color-scheme: dark` is still matching.

## When tokens are not enough

Any Tailwind class you pass wins outright. Shape's own defaults are written at
zero specificity with `[:where(&)]:`, so there is no `!important` and no
class-merging utility involved:

```blade
<x-shape::button class="rounded-full w-full">Continue</x-shape::button>
<x-shape::card class="bg-brand-50 shadow-none">…</x-shape::card>
```

Past that, [`shape:eject`](tooling.md#shapeeject) hands you the file and
everything it composes.
