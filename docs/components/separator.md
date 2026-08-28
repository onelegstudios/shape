# Separator

@docs('preview', name: 'separator')

## With a label

```blade
<x-shape::separator label="Archived" />
```

The label sits in a gap in the rule, in muted type taken from the surface.

| Prop | Default | Values |
| --- | --- | --- |
| `orientation` | `horizontal` | `horizontal`, `vertical` |
| `label` | — | text to sit in the rule |

A vertical separator stretches to its row, so it needs a flex parent:

```blade
<div class="flex items-center gap-3">
    <x-shape::text>Draft</x-shape::text>
    <x-shape::separator orientation="vertical" />
    <x-shape::text variant="muted">Edited 2 minutes ago</x-shape::text>
</div>
```

## Reach for it second

Separation from spacing is better than separation from a line. A separator is
the right call when spacing alone genuinely hasn't done the job — a labelled
break in a long list, a divider in a dropdown — and the wrong call as a reflex
between every two things on a page.

## Accessibility

A bare rule is decorative and is hidden from assistive technology: the spacing
and the headings around it already convey the break. A labelled one is exposed
as a real `separator` with its label as the accessible name.

## Folding

Tier B — `@blaze(fold: true, memo: true)`.

Slotless and always self-closing, so it memoizes on the call sites where folding
gives up. `label` picks between two different pieces of markup, so it is not
declared `safe`.

See [Folding](../folding.md).
