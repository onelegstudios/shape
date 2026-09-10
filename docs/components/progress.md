# Progress

A native `<progress>`, restyled.

@docs('preview', name: 'progress', layout: 'stack')

## Sizes

@docs('preview', name: 'progress-sizes', layout: 'stack')

## Tones

@docs('preview', name: 'progress-tones', layout: 'stack')

## Indeterminate

For work with no known end:

@docs('preview', name: 'progress-indeterminate', layout: 'stack')

It looks like a redundant flag next to "just leave `value` out". It isn't:
`@isset($value)` is a *branch on `value`*, and branching on the one prop that is
always dynamic would take every real call site off the fold path to express a
state most of them never use.

The indeterminate bar is drawn by Shape rather than by the browser — once
`appearance` is gone there is nothing left for the UA to animate, and an empty
track reads as zero percent rather than as unknown. It holds still under
`prefers-reduced-motion`.

## Max

`value` is read against `max`, so a step counter needs no percentages:

@docs('preview', name: 'progress-max', layout: 'stack')

## It prints no number

The browser computes the bar's width from `value` and `max`, so this component
never divides one by the other. Formatting "42%" is the call site's job, where
the value is already in hand:

```blade
<div class="flex items-center justify-between">
    <x-shape::text size="sm">Storage used</x-shape::text>
    <x-shape::text size="sm" variant="muted">{{ $percent }}%</x-shape::text>
</div>

<x-shape::progress :value="$percent" label="Storage used" />
```

## Give it a name

A progress bar with no accessible name announces a number and nothing about what
it measures. Pass `label`, or point at the text beside it:

```blade
<x-shape::progress :value="$percent" aria-labelledby="storage-label" />
```

`label` goes through the attribute bag rather than an `@if`, so a null one is
dropped without anything having to ask whether it is null — and so a caller's
own `aria-label` still wins.

## Theming

A `<progress>` is three boxes, and only one of them is the element you can put a
class on:

| Part | Drawn by | Painted |
| --- | --- | --- |
| The track | the element's own background | `shape-200` — `shape-800` in dark |
| The track again, in Chrome | `::-webkit-progress-bar`, over that background | the same |
| The bar | `::-webkit-progress-value`, `::-moz-progress-bar` | `--shape-tone` |

A class selects an element and never its pseudo-elements. So a `bg-*` here
reaches the track and stops — and in Chrome it does not even do that, because
`::-webkit-progress-bar` paints over the background it just set. That is what
makes this the one component in the library where a utility does not reach the
paint, and why both halves are painted in `shape.css` rather than in the class
string.

What does cross into a pseudo-element is inheritance. Custom properties are
inherited, so a variable set on the `<progress>` is readable by the pseudo-element
that draws the bar. That is the whole design: the bar is painted
`var(--shape-tone)` precisely so that the one thing a call site *can* reach —
the element — is enough to colour the one thing it cannot. Every route below
sets that variable rather than a colour.

`tone` is the way in, and covers every colour the system has a meaning
for:

```blade
<x-shape::progress :value="$percent" tone="success" label="Storage used" />
```

For a colour the system does not have, set that variable yourself. The utility is
an arbitrary property rather than a colour — `bg-violet-600` would paint the
track and leave the bar exactly where it was — so it hands the CSS a value and
paints nothing itself. It lands in `@layer utilities` while the tone blocks are
written in `components`, so it wins on layer order with no `!important` and no
selector:

@docs('preview', name: 'progress-tone-var', layout: 'stack')

Which is the mechanism a tone uses, aimed at one bar — and being a class rather
than an inline style, it takes variants. The second bar above is `violet-600` in
light and `violet-400` in dark, which is the lightening the tones themselves do
on a dark page.

An inline style does the same job and is the right one when the colour is not
known at build time — a per-team accent out of the database is a value Tailwind
never sees, so there is no class for it to compile:

```blade
<x-shape::progress :value="$percent" style="--shape-tone: {{ $team->colour }}" />
```

That one cannot carry a dark mode, which is the trade for taking a colour at
runtime. Where the colour means something rather than decorating one instance,
[a tone of your own](../theming.md#a-tone-of-your-own) is still the better
answer: one place, every component, both themes.

### The track

Recolouring the track means naming both of its boxes from the table above. A
rule that names only the element leaves Chrome painting the pseudo-element over
it, which is the same reason a `bg-*` appears to do nothing there:

```css
[data-shape-progress],
[data-shape-progress]::-webkit-progress-bar {
    background: var(--color-shape-100);
}
```

The radius is the exception: it is a class, written at zero specificity, and the
bar inherits it — so `class="rounded-none"` squares both ends of both halves.

The indeterminate bar reads `--shape-tone` through a gradient, so it follows the
tone and the variable above with the determinate one. Its animation sits behind
`prefers-reduced-motion: no-preference`, which anything written over it should
keep.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `label` | — | becomes `aria-label` |
| `tone` | `brand` | `brand`, `accent`, `info`, `success`, `warning`, `danger` |
| `value` | `0` | any number up to `max` |
| `max` | `100` | |
| `indeterminate` | `false` | `true` for work with no known end |

## Folding

Tier A — `@blaze(fold: true, safe: ['value', 'max', 'label'])`.

All three are interpolated into attributes and never branched on, so
`:value="$percent"` — the only call site a progress bar ever really has — still
folds. `size` and `indeterminate` select behaviour, so they belong at compile
time. See [Folding](../folding.md).
