# Switch

@docs('preview', name: 'switch')

## It is a real checkbox

`role="switch"` on a native `<input type="checkbox">` is the whole accessibility
story: a screen reader announces on and off instead of checked and unchecked, and
every native keyboard and form behaviour is kept. Rebuilding this out of a button
and some JavaScript would trade all of that for a visual.

## No JavaScript

The knob moves on `:checked`, which the browser handles. This component works
before the Alpine layer exists and keeps working if it never loads. It also holds
still for anyone who asked for reduced motion.

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the switch |
| `description` | — | a second line under the label |
| `value` | — | the submitted value |
| `color` | `accent` | `neutral`, `accent`, `danger`, `success`, `warning` |
| `id` | the resolved name | the element id |

`color` defaults to `accent` here rather than `neutral`, which is the one place
in the library that differs. A switch is a live setting rather than an annotation
— it takes effect the moment it moves — and reading as "on" is the whole point.

## Switch or checkbox?

A switch applies immediately. A checkbox waits for a submit. Inside a form with a
save button, reach for a [checkbox](checkbox.md).

## Folding

Tier A — `@blaze(fold: true)`.
