# Progress

A native `<progress>`, restyled.

@docs('preview', name: 'progress', layout: 'stack')

## Sizes

@docs('preview', name: 'progress-sizes', layout: 'stack')

## Colors

@docs('preview', name: 'progress-colors', layout: 'stack')

## Max

`value` is read against `max`, so a step counter needs no percentages:

@docs('preview', name: 'progress-max', layout: 'stack')

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

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `value` | `0` | any number up to `max` |
| `max` | `100` | |
| `indeterminate` | `false` | `true` for work with no known end |
| `size` | `base` | `sm`, `base`, `lg` |
| `color` | `accent` | `accent`, `success`, `warning`, `danger` |
| `label` | — | becomes `aria-label` |

## Folding

Tier A — `@blaze(fold: true, safe: ['value', 'max', 'label'])`.

All three are interpolated into attributes and never branched on, so
`:value="$percent"` — the only call site a progress bar ever really has — still
folds. `size` and `indeterminate` select behaviour, so they belong at compile
time. See [Folding](../folding.md).
