# Separator

A rule between two things. Reach for it after spacing, not instead of it.

@docs('preview', name: 'separator', layout: 'stack')

## With a label

The label sits in a gap in the rule, in muted type taken from the surface:

@docs('preview', name: 'separator-label', layout: 'stack')

## Sizes

`size` moves the label's type and the gap it sits in, and — at the top two steps
— the weight of the rule itself:

@docs('preview', name: 'separator-sizes', layout: 'stack')

The weight is the half of it with a floor. A hairline is one device pixel and
there is nothing under it, so `xs`, `sm` and `base` all draw one and only `lg`
and `xl` thicken. Worth knowing before reaching for `size="xs"` on a bare rule
and watching nothing happen.

## Vertical

A vertical separator stretches to its row, so it needs a flex parent:

@docs('preview', name: 'separator-vertical', layout: 'stack')

## Accessibility

A bare rule is decorative and hidden from assistive technology — the spacing and
the headings around it already convey the break. A labelled one is exposed as a
real `separator` with its label as the accessible name.

## Theming

The rule is `shape-200` — `shape-800` in dark mode — and the label beside it is
`text-xs font-medium` in `--shape-fg-muted`, so both follow the neutrals with
everything else the page draws in grey.

On a bare separator the colour is written at zero specificity and the attribute
bag lands on the rule itself, so a class is enough:

@docs('preview', name: 'separator-override', layout: 'stack')

A labelled one is a flex row, and the two rules inside it are spans a class on
the wrapper cannot reach. They carry `aria-hidden`, which is what a rule of your
own can name:

```css
[data-shape-separator] span[aria-hidden] {
    background-color: var(--color-shape-300);
}
```

The rule is drawn as a background on a one-pixel box rather than as a border, so
`border-dashed` has nothing to dash. A dashed separator is a border, and the
bare form is the one to say it on — it is the variant that carries `aria-hidden`
on the root:

```css
[data-shape-separator][aria-hidden='true'][data-shape-orientation='horizontal'] {
    height: 0;
    background: transparent;
    border-top: 1px dashed var(--color-shape-300);
}
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `orientation` | `horizontal` | `horizontal`, `vertical` |
| `label` | — | text to sit in the rule |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |

`label` is horizontal only.

## Folding

Tier B — `@blaze(fold: true, memo: true)`.

Slotless and always self-closing, so it memoizes where folding gives up.
`label` picks between two different pieces of markup, so it is not `safe`. See
[Folding](../folding.md).
