# Checkbox

```blade
<x-shape::checkbox name="terms" value="1" label="I agree to the terms" />
```

## The label wraps the control

A control inside its own `<label>` needs no `for`, so nothing can drift out of
sync and there is nothing for a caller to remember. The `id` is still resolved
and rendered, because `aria-describedby` needs something to point at.

| Prop | Default | Values |
| --- | --- | --- |
| `label` | — | the text beside the box |
| `description` | — | a second line under the label |
| `value` | — | the submitted value; separates a group sharing one name |
| `color` | `neutral` | `neutral`, `accent`, `danger`, `success`, `warning` |
| `id` | `{name}-{value}` | the element id |

## Groups

One name, many values. Put them in a fieldset so the group has an accessible
name, and the shared name is stated once:

```blade
<x-shape::field as="fieldset" field-name="days">
    <x-shape::label as="legend">Send reminders on</x-shape::label>
    <x-shape::checkbox value="mon" label="Monday" />
    <x-shape::checkbox value="tue" label="Tuesday" />
</x-shape::field>
```

## The box is drawn, not native

`appearance-none` plus a grid that stacks the input and its glyph in one cell, so
the tick sits on top of the box without absolute positioning or a
background-image SVG. The fill reads `--shape-tone`, the same variable the button
and badge read, so a checkbox given a colour agrees with everything else given
the same one.

## Indeterminate

There is no `indeterminate` prop, because there could not be a working one:
indeterminate is a DOM property rather than an attribute, so no server-rendered
markup can set it. A prop would be a knob that quietly does nothing.

The glyph and its styling ship anyway, so the dash appears the moment anything
sets the property:

```blade
<x-shape::checkbox name="all" x-init="$el.indeterminate = @js($partial)" />
```

## Disabled

The wrapper dims its own label with `group-has-disabled:`, not `peer-`: the input
sits a level down, and a peer has to be a previous sibling. The
[field](field.md#disabled-state-without-prop-plumbing) matches direct children
only, so one disabled checkbox never dims its siblings.

## Folding

Tier A — `@blaze(fold: true)`.
