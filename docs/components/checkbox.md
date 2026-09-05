# Checkbox

A checkbox inside its own label.

@docs('preview', name: 'checkbox', layout: 'stack')

## Description

@docs('preview', name: 'checkbox-description', layout: 'stack')

## Tones

@docs('preview', name: 'checkbox-tones')

The fill reads the same tone variables the [button](button.md) and
[badge](badge.md) read, so a checkbox given a tone agrees with everything else
given the same one.

## Groups

One name, many values. Put them in a fieldset so the group has an accessible
name, and state the shared name once on the [field](field.md):

@docs('preview', name: 'checkbox-group', layout: 'stack')

Each checkbox gets `name="days"` and an id of `days-{value}`.

## Disabled

@docs('preview', name: 'checkbox-disabled', layout: 'stack')

The wrapper dims its own label with `group-has-disabled:`, and the field matches
direct children only — so one disabled checkbox never dims its siblings.

## Indeterminate

There is no `indeterminate` prop, because there could not be a working one:
indeterminate is a DOM property rather than an attribute, so no server-rendered
markup can set it. The glyph and its styling ship anyway, so the dash appears
the moment anything sets the property:

```blade
<x-shape::checkbox name="all" x-init="$el.indeterminate = @js($partial)" />
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the box |
| `description` | — | a second line under the label |
| `value` | — | the submitted value; separates a group sharing one name |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `id` | `{name}-{value}` | the element id |

`checked`, `disabled`, `required` and `wire:model` pass through to the
`<input>`.

The control sits inside its own `<label>`, so nothing needs a `for` and nothing
can drift out of sync. The `id` is still resolved and rendered, because
`aria-describedby` needs something to point at.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
