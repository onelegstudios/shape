# Radio

@docs('preview', name: 'radio')

Structurally the [checkbox](checkbox.md), with a round box and a dot.

## A radio only means anything in a group

And a group only has an accessible name if it is a real `<fieldset>` with a
`<legend>`. That is what `as="fieldset"` on the [field](field.md) is for — and it
carries the shared `name`, so no radio repeats it. Every radio in the example
above gets `name="billing"` and an id of `billing-{value}`.

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the dot |
| `description` | — | a second line under the label |
| `value` | — | the submitted value |
| `color` | `neutral` | `neutral`, `accent`, `danger`, `success`, `warning` |
| `id` | `{name}-{value}` | the element id |

The dot is a plain span rather than an icon component. It is a filled circle, and
asking an icon for a filled circle would be more machinery than drawing it.

## Folding

Tier A — `@blaze(fold: true)`.
