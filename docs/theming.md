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

If you are here to make it your colours and would rather read the recipe than
the reasoning, [Changing the colours, step by step](#changing-the-colours-step-by-step)
is the whole of step 1 as instructions.

## Where overrides go

The tokens are imported from `vendor/`, which is what keeps your own
declarations authoritative and leaves nothing to rebuild on an upgrade. Anything
you declare after the import wins:

```css
@import 'tailwindcss';
@import '../../vendor/onelegstudios/shape/resources/css/shape.css';

@theme {
    --radius-shape: 0.25rem;
}
```

`vendor:publish --tag='shape-css'` copies the file into
`resources/css/shape.css` if you would rather own the token layer outright — but
then you own it, including every change the package makes to it later. Overriding
from your own file is the arrangement to prefer.

## Changing the colours, step by step

Everything below this section explains why the token layer is shaped the way it
is. This one is the short version: what to type, in what order, to make Shape
your colours.

### 1. Write in your own stylesheet, under the import

```css
/* resources/css/app.css */
@import 'tailwindcss';
@import '../../vendor/onelegstudios/shape/resources/css/shape.css';

/* Everything from here down wins. */
```

Every step below goes in that file, under that import. You do not edit
`vendor/`, and publishing the stylesheet is not the way in: it hands you the
file and every future change to it along with it.

### 2. Know which ramps are yours to move

| Ramp                                   | Yours?                                                                                                                     |
| -------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| `--color-shape-brand-*`                | Yes. The product's colour, and what most retints are about.                                                                |
| `--color-shape-*` — the neutrals       | Yes, and they should follow the brand's hue.                                                                               |
| `--color-shape-accent-*`               | Yes — and you have to, if the brand lands near fuchsia. See [Adding another accent colour](#adding-another-accent-colour). |
| `danger`, `info`, `success`, `warning` | Retint to fit the palette; never repoint at another meaning.                                                               |

### 3. Write the brand ramp

Three ways to produce eleven steps, in increasing order of effort. All three go
in the file from step 1.

**A — one value, and the file derives both ramps.** The shortest path, and the
only one whose contrast was measured rather than assumed:

```css
@import '../../vendor/onelegstudios/shape/resources/css/shape-seed.css';

:root {
    --shape-seed: oklch(52% 0.16 300);
}
```

Note `:root` and not `@theme` — the seed is an input to a derivation, not a
theme token. It moves the neutrals too, so step 4 is already done. What it does
in full, and the one precondition it carries, is
[One colour, both ramps](#one-colour-both-ramps).

**B — alias another Tailwind ramp.** Shape's own ramps are aliases; a retint can
be one too. Eleven lines, no colour values to get right, and it inherits the
step-to-contrast relationship the tones depend on — every Tailwind `700` carries
white at AA, the tightest of them at 4.9:1:

```css
@theme {
    --color-shape-brand-50: var(--color-violet-50);
    --color-shape-brand-100: var(--color-violet-100);
    --color-shape-brand-200: var(--color-violet-200);
    --color-shape-brand-300: var(--color-violet-300);
    --color-shape-brand-400: var(--color-violet-400);
    --color-shape-brand-500: var(--color-violet-500);
    --color-shape-brand-600: var(--color-violet-600);
    --color-shape-brand-700: var(--color-violet-700);
    --color-shape-brand-800: var(--color-violet-800);
    --color-shape-brand-900: var(--color-violet-900);
    --color-shape-brand-950: var(--color-violet-950);
}
```

Which is not the same as retinting `--color-violet-*` itself. This moves Shape
and leaves your own `text-violet-600` where it is; that would move both. The
difference is [Colour](#colour), below. One precondition: an application that
clears Tailwind's palette with `--color-*: initial` has left nothing for these
to point at, and wants path C.

**C — literal values.** For a brand handed to you as one hex code with a ramp
built around it, in a generator or by hand. The same eleven declarations with
`oklch(…)` in place of each `var(…)`, and two steps you have to get right
yourself — see
[The two steps a hand-written ramp has to get right](#the-two-steps-a-hand-written-ramp-has-to-get-right).

### 4. Bring the neutrals with it

Skipping this is the most common way a retinted product still looks like it did
not take. Tailwind's `mist` is cyan-matched, so a violet brand on mist greys is
a violet product sitting on somebody else's page. Path A does this for you; for
B and C, take the seed file's approach directly and derive the greys from one
hue at a fixed, very low chroma:

```css
@theme {
    /* mist's own lightness and chroma, at the brand's hue. */
    --color-shape-50: oklch(98.7% 0.002 300);
    --color-shape-100: oklch(96.3% 0.002 300);
    --color-shape-200: oklch(92.5% 0.005 300);
    --color-shape-300: oklch(87.2% 0.007 300);
    --color-shape-400: oklch(72.3% 0.014 300);
    --color-shape-500: oklch(56% 0.021 300);
    --color-shape-600: oklch(45% 0.017 300);
    --color-shape-700: oklch(37.8% 0.015 300);
    --color-shape-800: oklch(27.5% 0.011 300);
    --color-shape-900: oklch(21.8% 0.008 300);
    --color-shape-950: oklch(14.8% 0.004 300);
}
```

The chroma is absolute rather than scaled from the brand's on purpose: a neon
brand should still get calm greys. The cast is meant to be felt, not seen.

### 5. Rebuild, and look at five things

Rebuild the stylesheet — `npm run dev` or `npm run build` — and check, in this
order:

1. A `variant='primary'` button. That is the `700` step carrying white.
2. A `variant='subtle'` button. That is the `800` label on the `100` tint, and
   its hover is `800` on `200`.
3. Tab to something focusable. The ring is the brand's `600`.
4. A card, a table, a divider. Those are the neutrals from step 4.
5. Switch the machine to dark. Nothing above should need a second decision —
   see [Dark mode](#dark-mode) for why, and for the one way a partial ramp
   fails there and nowhere else.

### What you do not have to touch

No component, no `dark:` variant, and no second palette. Components never name a
brand or accent step; they read `--shape-tone-*` and `--shape-fg*`, and the
tone blocks read the ramp. Which is why a retint moves a button, a badge, a
checkbox, an alert and a progress bar together, and why the ejected-component
escape hatch is not part of any of this.

## What Shape owns

| Token                                 | Default              | What reads it                                           |
| ------------------------------------- | -------------------- | ------------------------------------------------------- |
| `--color-shape-50` … `-950`           | Tailwind's `mist`    | Every neutral surface, border and rule                  |
| `--color-shape-brand-50` … `-950`     | Tailwind's `cyan`    | The brand tone, the focus ring, the active tab and page |
| `--color-shape-accent-50` … `-950`    | Tailwind's `fuchsia` | The accent tone                                         |
| `--color-shape-danger-*`              | Tailwind's `red`     | The danger tone                                         |
| `--color-shape-info-*`                | Tailwind's `blue`    | The info tone                                           |
| `--color-shape-success-*`             | Tailwind's `green`   | The success tone                                        |
| `--color-shape-warning-*`             | Tailwind's `yellow`  | The warning tone                                        |
| `--color-shape-*-fg`, `-fg-muted`     | Ends of each ramp    | What goes on top when a tone is used as a fill          |
| `--radius-shape`, `--radius-shape-lg` | `0.5rem`, `0.75rem`  | `rounded-shape`, `rounded-shape-lg`                     |
| `--text-2xs`                          | `0.6875rem`          | Badges, avatar initials, table headings                 |

And what it deliberately does not own: the spacing scale, the rest of the type
scale, the shadow scale (see [Elevation](elevation.md)), and the font family.
Shape sets no `font-family` at all, so the components inherit whatever your
application has already chosen.

## Colour

Every ramp is _aliased_ to a Tailwind colour rather than copied out of it:

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
cannot also be the colour that means _look here_. A `New` badge in the brand's
own cyan, on a page already full of cyan, announces nothing.

That second colour is `accent`, and it defaults to Tailwind's `fuchsia`. It is
for the things that should interrupt the eye and nothing else: the new feature,
the recommended plan, the rule under a section heading. Used sparingly, or it
stops working — the same rule the brand is under, one level up.

It is not derived from the seed, and that is the point. An accent that followed
the brand around the hue wheel would land next to it and stop standing apart. It
is still yours to retint, unlike danger and success — a magenta product will
want it moved — but move it _away_ from the brand, and mind the arc between
280° and 350°, which is the only stretch the four state colours leave free.

```blade
<x-shape::badge label='New' tone='accent' />
<x-shape::button variant='subtle' tone='accent'>Try the beta</x-shape::button>
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
an orange one — where it reads as a warning. `tone='brand'` still means
emphasis, and resolves no glyph; `tone='info'` means "worth knowing", and stays
blue however far the brand travels.

## One colour, both ramps

For the common case — a brand colour, and neutrals that agree with it — there is
an optional second stylesheet that derives the whole palette from a single value:

```css
@import 'tailwindcss';
@import '../../vendor/onelegstudios/shape/resources/css/shape.css';
@import '../../vendor/onelegstudios/shape/resources/css/shape-seed.css';

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
<section data-shape-seed style='--shape-seed: oklch(52% 0.19 25)'>
    <x-shape::button variant='primary' tone='brand'>Upgrade</x-shape::button>
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

| Variable           | Meaning                                       |
| ------------------ | --------------------------------------------- |
| `--shape-fg`       | The foreground for this surface               |
| `--shape-fg-muted` | The de-emphasized foreground for this surface |
| `--shape-ring`     | The focus ring                                |

`<x-shape::text variant='muted'>` reads `--shape-fg-muted` rather than
`text-shape-500`, which is what makes "don't use grey text on a coloured
background" structurally impossible rather than merely documented. On a tinted
surface the muted foreground is the same hue dialled down, not grey.

`data-shape-surface` republishes the pair. The values are `brand`, `accent`,
`danger`, `info`, `success`, `warning` — the six filled surfaces, each naming its
own palette steps — and two that read the tone instead: `tint`, the pale wash
whose foreground is the tone's own ink rather than white, and `solid`, its filled
counterpart, whose foreground is the tone's own `-fg`.

`data-shape-surface-hover` publishes the same pair for as long as the pointer is
on the element, and takes the same values. One component uses it: a ghost
[alert](components/alert.md#toning-the-text) paints no fill at rest, so it
publishes no foreground either, and hovering it paints a tint that the page's own
ink has no business sitting on. It is a separate attribute rather than a
`hover:text-` utility because what has to change is the pair every nested
component reads — a heading paints its own `--shape-fg`, and no utility on an
ancestor reaches it.

Reading the tone is what lets those two follow it into dark mode.
`[data-shape-surface='danger']` is red-50 in both, while the danger *tone* flips
from a 700 fill carrying white to a 500 fill carrying dark — so a component that
already has a tone reaches for `tint` or `solid`, and a page that paints a
surface by hand names one of the six.

`solid` publishes the same colour twice, dialling nothing back, because a fill
that saturated has nowhere to go: white on the 700 steps starts at 4.9:1 for
`success`, so any alpha that reads as recessed lands under AA. Hierarchy inside
one is carried by size and weight instead.

A ghost button is the single exception to all of this. It paints with
`--shape-tone-ink` and declares a tone of its own, so inside a filled surface it
would resolve the neutral ink; `@layer shape-surface` corrects it there, after
Tailwind's layers, because a utility outranks every rule in `@layer components`
whatever its specificity — the same arrangement the overlays use for placement.

A surface of your own is two declarations:

```css
@layer base {
    [data-shape-surface='promo'] {
        --shape-fg: var(--color-white);
        --shape-fg-muted: color-mix(
            in oklch,
            var(--color-white) 76%,
            transparent
        );
    }
}
```

```blade
<x-shape::card data-shape-surface='promo' class='bg-violet-700'>
    <x-shape::heading size='lg'>Upgrade</x-shape::heading>
    {{-- Muted, and still legible, because it isn't grey. --}}
    <x-shape::text variant='muted'>Cancel any time.</x-shape::text>
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

| Variable                                 | Used by                                                           |
| ---------------------------------------- | ----------------------------------------------------------------- |
| `--shape-tone`                           | The `primary` fill; the progress bar; a checked control; the alert's `solid` |
| `--shape-tone-hover`                     | That fill, hovered; the alert's `solid` border                              |
| `--shape-tone-fg`                        | What goes on the fill, and the `solid` surface's foreground                 |
| `--shape-tone-ink`                       | The `subtle` and `ghost` label, and the tint surface's foreground           |
| `--shape-tone-tint`                      | The `subtle` background, and the alert's                                    |
| `--shape-tone-tint-hover`                | That tint, hovered                                                          |
| `--shape-tone-surface`, `-surface-hover` | The `outline` variant's background                                          |
| `--shape-tone-border`                    | The `outline` variant's border, on the button, the badge and the alert      |
| `--shape-tone-border-strong`             | The alert's toned edge, under its `border` prop                             |

The two borders are a pair, and which one a component reads says what its edge is
for. `--shape-tone-border` is neutral at every tone and stays that way: an
outlined button is a control whatever it means, and toning its edge would make
every secondary action on a page a coloured box. `--shape-tone-border-strong` is
the tone's own, a step further along the ramp, for an edge that carries the
meaning rather than draws the control. Only the [alert](components/alert.md#borders)
reads it today.

The tones are `neutral` (the default, and the bare `[data-shape-tone]` block),
`brand`, `accent`, `danger`, `info`, `success` and `warning`. Retinting a ramp
retones everything that reads it — a button, a badge, a checkbox and a progress bar all
move together, because there is one set of variables and not four component
palettes.

### A tone of your own

Four blocks, and the fourth is optional. The example is `spotlight`, a second
highlight colour aliased to Tailwind's pink, but nothing below is specific to
what it means: a `premium` tone or an `archived` one is the same four blocks.

**1. The ramp.** All eleven steps. Light mode reads four of them and dark mode
reads five others, which is why a ramp declared down to the steps you can watch
go wrong in the browser fails later and elsewhere — the table under
[Dark mode](#what-a-retint-costs-you-here) is which is which:

```css
@theme {
    --color-shape-spotlight-50: var(--color-pink-50);
    --color-shape-spotlight-100: var(--color-pink-100);
    --color-shape-spotlight-200: var(--color-pink-200);
    --color-shape-spotlight-300: var(--color-pink-300);
    --color-shape-spotlight-400: var(--color-pink-400);
    --color-shape-spotlight-500: var(--color-pink-500);
    --color-shape-spotlight-600: var(--color-pink-600);
    --color-shape-spotlight-700: var(--color-pink-700);
    --color-shape-spotlight-800: var(--color-pink-800);
    --color-shape-spotlight-900: var(--color-pink-900);
    --color-shape-spotlight-950: var(--color-pink-950);
}
```

**2. The light tone block.** In `@layer components` — `components` and not
`base`, for the reason under
[Dark mode](#if-your-application-has-a-manual-toggle):

```css
@layer components {
    [data-shape-tone='spotlight'] {
        --shape-tone: var(--color-shape-spotlight-700);
        --shape-tone-hover: var(--color-shape-spotlight-800);
        --shape-tone-fg: white;
        --shape-tone-ink: var(--color-shape-spotlight-800);
        --shape-tone-tint: var(--color-shape-spotlight-100);
        --shape-tone-tint-hover: var(--color-shape-spotlight-200);
        --shape-tone-border-strong: var(--color-shape-spotlight-300);
    }
}
```

Seven lines and not ten, because the `neutral` block sets all ten variables and
a tone that overrides only some of them inherits the rest.
`--shape-tone-surface`, `-surface-hover` and `-border` are the three left
inherited, and the shipped tones leave them alone too: the `outline` variant is
neutral chrome with a coloured label, in every tone. `-border-strong` is not
among them — it is the toned edge, so a tone that leaves it inherited gets the
neutral grey where it asked for a colour.

**3. The dark tone block.** The same seven variables at the dark mode's steps —
the fill drops to `500` and brightens on hover instead of darkening, the tint
inverts to the bottom of the ramp, and the edge goes *up* it, because stronger on
a dark page is lighter:

```css
@layer components {
    @media (prefers-color-scheme: dark) {
        [data-shape-tone='spotlight'] {
            --shape-tone: var(--color-shape-spotlight-500);
            --shape-tone-hover: var(--color-shape-spotlight-400);
            --shape-tone-fg: var(--color-shape-950);
            --shape-tone-ink: var(--color-shape-spotlight-200);
            --shape-tone-tint: var(--color-shape-spotlight-950);
            --shape-tone-tint-hover: var(--color-shape-spotlight-900);
            --shape-tone-border-strong: var(--color-shape-spotlight-800);
        }
    }
}
```

Note `--shape-tone-fg` there: dark fills take the _neutral_ `950`, not the
tone's own. A `500` step is bright enough that dark text is the readable choice
on it, which is the same reason warning takes dark text in both modes. And if
your application drives dark mode from a class or attribute rather than the
media query, this block needs the treatment the
[toggle section](#if-your-application-has-a-manual-toggle) describes — still in
`@layer components`, because a tone written in `base` loses to the plain
`[data-shape-tone]` in `components` whatever specificity you give it.

**4. Optional — the filled surface.** Only if you will put
`data-shape-surface='spotlight'` on a container and let the text inside find its
own foreground:

```css
@theme {
    --color-shape-spotlight-fg: var(--color-shape-spotlight-50);
    --color-shape-spotlight-fg-muted: var(--color-shape-spotlight-100);
}

@layer base {
    [data-shape-surface='spotlight'] {
        --shape-fg: var(--color-shape-spotlight-fg);
        --shape-fg-muted: var(--color-shape-spotlight-fg-muted);
    }
}
```

Then it is a tone like any other, everywhere a tone goes:

```blade
<x-shape::badge label='Beta' tone='spotlight' />
<x-shape::button variant='subtle' tone='spotlight'>Join the beta</x-shape::button>
```

One caveat, and it is small: the alert, the badge and the toast branch on `tone`
to resolve their glyph, so an unknown tone gets no icon. Pass `icon='…'`
explicitly on those three. Everything else only ever interpolates `tone` into
the attribute, which is also what keeps `:tone="$destructive ? 'danger' : null"`
on the fold path — see [Folding](folding.md).

## Adding another accent colour

Two different things get asked for under that name, and they have different
answers.

**Moving the accent you have** is a retint: eleven declarations, no new tone,
and everything already passing `tone='accent'` follows. This is the common case
— it is what a product whose brand has landed near fuchsia needs.

**Adding a second accent** is a tone of your own and mechanically nothing more
— [A tone of your own](#a-tone-of-your-own) writes out the four blocks, using
this exact case as its example. What is accent-specific is not the CSS but the
decision: Shape ships one accent on purpose, because one colour meaning _look
here_ is about as much as an interface can carry before none of them does.
Before writing a second, be able to say what it means that the first does not.
"Beta" against "recommended" is two jobs. "Another nice colour" is not one. Then
the two questions below are the ones left.

### Moving the accent you have

[Step 3 of the recipe](#3-write-the-brand-ramp), on the accent's eleven steps
instead of the brand's. Aliasing another Tailwind ramp is the way in:

```css
@theme {
    --color-shape-accent-50: var(--color-rose-50);
    --color-shape-accent-100: var(--color-rose-100);
    /* … through … */
    --color-shape-accent-950: var(--color-rose-950);
}
```

Nothing else needs restating. `--color-shape-accent-fg` is declared as
`var(--color-shape-accent-50)`, so the pair that goes on the filled surface
moves with the ramp; the tone block names steps rather than colours, so it
follows; and dark mode is the same eleven steps read at different numbers.

### Where a second accent can go on the wheel

The arc is crowded, and that is the real constraint rather than the CSS. Danger
sits at 27°, warning at 86°, success at 150°, the brand at 223° until it moves,
info at 264°, and the accent at 322°. Two rules follow:

- **Away from the brand.** An accent that lands within a few tens of degrees of
  the brand stops being the thing that stands apart from it, which is its only
  job. This is the constraint the seed layer respects by not deriving the accent
  at all.
- **Away from the states**, or far enough that nothing reads as one. This is
  where the arc runs out rather than where it obliges. Tailwind's `pink` — the
  example in [A tone of your own](#a-tone-of-your-own) — is 354° at its `500`
  step but has drifted to about 4° by the `700` its light fill uses, some 23°
  from danger's red and just past the end of the 280-350° window
  `shape.css` names. It is the least-bad slot on a full wheel, not a clean
  one.

Where the arc leaves no perfect answer, one thing works in your favour: `brand`
and `accent` resolve no glyph, and the four states always do. A pink badge with
no icon beside a red one carrying a triangle has already told the reader which
of them is a state, before the hue is asked to. A tone of your own falls through
to no glyph as well, which is the right answer for an accent — and the reason
the glyph caveat in [A tone of your own](#a-tone-of-your-own) bites only for a
tone you meant as a state.

### Under a seed

`shape-seed.css` derives the brand and the neutrals and nothing else, so a
hand-written accent stays exactly where you put it under every seed — which is
what you want, and the reason the shipped accent is not derived either. The one
thing a seed changes is the arithmetic above: move the brand to 300° and the
fuchsia accent at 322° is no longer a second colour, and both it and any
`spotlight` of yours want re-checking against the new hue.

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

### What a retint costs you here

Nothing, which is worth spelling out because it is the first thing the section
above raises. The dark tone block is written against the same eleven steps
as the light one, read at different numbers: the fill moves from `700` to `500`,
its hover from `800` to `400`, the ink from `800` to `200`, the tint from `100`
to `950`. A complete ramp is therefore already a dark ramp, and moving one moves
both modes at once. No component names a brand or accent step either — the
`dark:` utilities the components carry are all neutrals — so there is no second
place to go and change.

What that does mean is that an _incomplete_ ramp fails in the dark and only in
the dark. Declare just the steps light mode reads, because those are the ones
you can watch go wrong, and the page is correct until the machine switches;
then the primary button falls back to whatever `--color-shape-brand-500` still
resolves to, which is Tailwind's cyan. Declare all eleven.

| Step         | What reads it                                                      |
| ------------ | ------------------------------------------------------------------ |
| `100`, `200` | The `subtle` background and its hover; the `ghost` hover — light   |
| `600`        | The focus ring — light; brand only                                 |
| `700`, `800` | The `primary` fill and its hover, and the `subtle` label — light   |
| `400`, `500` | The `primary` fill and its hover, and the focus ring — dark        |
| `200`        | The `subtle` label — dark                                          |
| `900`, `950` | The same two, at the dark end — dark                               |
| `50`, `100`  | The `-fg` pair: what goes on this tone as a filled surface         |
| `300`        | Nothing in Shape. Yours, through `bg-shape-brand-300` and the like |

The neutrals carry one more job than the table shows: dark fills take their
foreground from `--color-shape-950`, so the neutral ramp's dark end is the text
colour on every coloured button on a dark page.

### If your application has a manual toggle

A class or attribute toggle needs both halves, and only one of them is yours to
configure.

The utilities half is a Tailwind setting — teach `dark:` to mean your selector,
and every `dark:` in Shape's Blade compiles against it, because your build is
already scanning the package's views:

```css
@custom-variant dark (&:where([data-theme='dark'], [data-theme='dark'] *));
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
stylesheet (`vendor:publish --tag='shape-css'`) and rewriting the three
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
<x-shape::button class='rounded-full w-full'>Continue</x-shape::button>
<x-shape::card class='bg-brand-50 shadow-none'>…</x-shape::card>
```

Past that, [`shape:eject`](tooling.md#shapeeject) hands you the file and
everything it composes.
