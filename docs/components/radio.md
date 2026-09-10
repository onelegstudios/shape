# Radio

Structurally the [checkbox](checkbox.md), with a round box and a dot. A radio
only means anything in a group.

@docs('preview', name: 'radio', layout: 'stack')

The [field](field.md) in `fieldset` mode carries the shared `name`, so no radio
repeats it: each one here gets `name="billing"` and an id of `billing-{value}`.
A group only has an accessible name if it is a real `<fieldset>` with a
`<legend>`.

## Descriptions

@docs('preview', name: 'radio-description', layout: 'stack')

## Sizes

The [checkbox](checkbox.md#sizes)'s steps, box for box — the two are the same
control to anyone filling in the form they are in:

@docs('preview', name: 'radio-sizes', layout: 'stack')

## Tones

@docs('preview', name: 'radio-tones')

## Theming

Structurally the [checkbox](checkbox.md#theming)'s paint, with two differences:
the box is a circle at every size, and the checked state is a `--shape-tone`
fill with a dot of `--shape-tone-fg` over it rather than a glyph. Everything
else — the neutral border, the ring, the invalid red — is the same set of
tokens, and moves with them.

The bag lands on the `<input>`, so a class reaches the box:

@docs('preview', name: 'radio-override', layout: 'stack')

The dot is sized against the box in the component rather than derived from it,
so a box resized this way wants the dot moved with it, in a rule:

```css
[data-shape-radio] [data-shape-control] { width: 1.25rem; height: 1.25rem; }
[data-shape-radio] [data-shape-control] + span { width: 0.5rem; height: 0.5rem; }
```

Which is the point at which [`shape:eject`](../tooling.md#shapeeject) is the
cheaper answer: the radio is one small file, and a different control is a
different file rather than a stack of corrections to this one.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the dot |
| `description` | — | a second line under the label |
| `value` | — | the submitted value |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `id` | `{name}-{value}` | the element id |

`checked`, `disabled` and `wire:model` pass through to the `<input>`.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
