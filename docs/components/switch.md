# Switch

A setting that applies the moment it moves.

@docs('preview', name: 'switch', layout: 'stack')

## Description

@docs('preview', name: 'switch-description', layout: 'stack')

## Tones

`tone` defaults to `brand` here rather than `neutral`, which is the one place
in the library that differs — a switch is a live setting, and reading as "on" is
the whole point:

@docs('preview', name: 'switch-tones')

## Disabled

@docs('preview', name: 'switch-disabled', layout: 'stack')

## Switch or checkbox?

A switch applies immediately. A [checkbox](checkbox.md) waits for a submit.
Inside a form with a save button, reach for a checkbox.

## It is a real checkbox

`role="switch"` on a native `<input type="checkbox">` is the whole accessibility
story: a screen reader announces on and off instead of checked and unchecked,
and every native keyboard and form behaviour is kept.

The knob moves on `:checked`, which the browser handles — so this component
works before the script exists and keeps working if it never loads. It also
holds still for anyone who asked for reduced motion.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the switch |
| `description` | — | a second line under the label |
| `value` | — | the submitted value |
| `tone` | `brand` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `id` | the resolved name | the element id |

`checked`, `disabled` and `wire:model` pass through to the `<input>`.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
