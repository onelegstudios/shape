# Separator

A rule between two things. Reach for it after spacing, not instead of it.

@docs('preview', name: 'separator', layout: 'stack')

## With a label

The label sits in a gap in the rule, in muted type taken from the surface:

@docs('preview', name: 'separator-label', layout: 'stack')

## Vertical

A vertical separator stretches to its row, so it needs a flex parent:

@docs('preview', name: 'separator-vertical', layout: 'stack')

## Accessibility

A bare rule is decorative and hidden from assistive technology — the spacing and
the headings around it already convey the break. A labelled one is exposed as a
real `separator` with its label as the accessible name.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `orientation` | `horizontal` | `horizontal`, `vertical` |
| `label` | — | text to sit in the rule |

`label` is horizontal only.

## Folding

Tier B — `@blaze(fold: true, memo: true)`.

Slotless and always self-closing, so it memoizes where folding gives up.
`label` picks between two different pieces of markup, so it is not `safe`. See
[Folding](../folding.md).
