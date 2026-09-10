# Checkbox

A checkbox inside its own label.

@docs('preview', name: 'checkbox', layout: 'stack')

## Description

@docs('preview', name: 'checkbox-description', layout: 'stack')

## Sizes

The box, the tick inside it and the text beside it move together, so a checkbox
at any step is one control rather than a box that outgrew its label:

@docs('preview', name: 'checkbox-sizes', layout: 'stack')

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

## Theming

The box is neutral until it is checked: a `shape-300` border — `shape-600` in
dark mode — over `white` or `shape-900`, at a radius of its own rather than
`--radius-shape`, because a 16px box takes the library's 8px corner as a circle.
Checked, it fills with `--shape-tone` and draws the tick in `--shape-tone-fg`.
The ring is `--shape-ring` and the invalid border is the danger ramp's `500`, so
a checkbox follows a [retint](../theming.md) with every other control.

### A class at the call site

The attribute bag lands on the `<input>`, so a class reaches the box and nothing
else:

@docs('preview', name: 'checkbox-override', layout: 'stack')

The border, the background and the radius are written at zero specificity and
yield to that. The checked fill is not — `checked:bg-*` is emitted as a plain
class — so a colour of your own there has to be made important, and a
[tone](#tones) is the better answer wherever the colour means something.

The size is a third case, in between. `size-4` is a plain class like the fill,
so `size-5` does not beat it on specificity — the two tie, and the box grows
only because Tailwind emits its size utilities in ascending order and `size-5`
lands after `size-4`. That holds for every value above the default and for none
below it: `size-3` ties the same way and loses, and needs the flag.

The tick does not follow. It is drawn at `size-3.5` in a grid cell of its own, so
a bigger box centres the same glyph in more space rather than scaling it — a
20px box keeps the 14px tick above. Size the drawing yourself when the gap shows:

```css
[data-shape-checkbox] [data-shape-control] { width: 1.25rem; height: 1.25rem; }
[data-shape-checkbox] svg { width: 1rem; height: 1rem; }
```

### Every checkbox at once

The label and its description are spans inside the `<label>` that carries
`data-shape-checkbox`, so they are a rule's business rather than a call site's:

```css
[data-shape-checkbox] { align-items: center; }
[data-shape-checkbox] [data-shape-control] { border-width: 2px; }
```

The tick and the dash are the `shape-checked` and `shape-indeterminate`
[icon](icon.md) slots, drawn from whichever set you generated. To change the
drawing rather than its colour, point the slot at another glyph and regenerate —
see [Overriding one](icon.md#overriding-one).

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the box |
| `description` | — | a second line under the label |
| `value` | — | the submitted value; separates a group sharing one name |
| `tone` | `neutral` | `neutral`, `brand`, `accent`, `danger`, `info`, `success`, `warning` |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `id` | `{name}-{value}` | the element id |

`checked`, `disabled`, `required` and `wire:model` pass through to the
`<input>`.

The control sits inside its own `<label>`, so nothing needs a `for` and nothing
can drift out of sync. The `id` is still resolved and rendered, because
`aria-describedby` needs something to point at.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
