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

## Colors

@docs('preview', name: 'radio-colors')

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the dot |
| `description` | — | a second line under the label |
| `value` | — | the submitted value |
| `color` | `neutral` | `neutral`, `accent`, `danger`, `info`, `success`, `warning` |
| `id` | `{name}-{value}` | the element id |

`checked`, `disabled` and `wire:model` pass through to the `<input>`.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
