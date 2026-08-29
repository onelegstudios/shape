# Field

The pieces a control assembles when you give it a `label`. Reach for them
directly when the shorthand can't express what you need.

@docs('preview', name: 'field', layout: 'stack')

Compose by hand when you need a control between the label and the description,
two controls in one field, or markup of your own between the pieces. Groups are
the other case — there is no single control for a shorthand to live on.

## The name is stated once

`field-name` is written on the field and nowhere else. The label takes its `for`
from it, the description its `id`, the control its `id` and `name`, the error
the key it looks up. Passing it down by hand means writing it four times and
getting it wrong once.

An explicit value on a child still wins:

```blade
<x-shape::label for="something-else">Email</x-shape::label>
```

## Fieldset mode

A group of radios or checkboxes is only a group if it is a `<fieldset>` with a
`<legend>` naming it:

@docs('preview', name: 'field-fieldset', layout: 'stack')

## Validation

`error` renders the first message for the field's name, and nothing at all when
the field is valid. Pair it with `aria-invalid` on the control, which is what
draws the red border:

@docs('preview', name: 'field-error', layout: 'stack')

It carries `role="alert"` and nothing else — that role already implies an
assertive live region, and pairing it with `aria-live="polite"` asks for both at
once. It renders nothing when no bag was shared and when it has no name to look
up, because an empty key would otherwise display any error in the bag, including
a neighbour's.

## Spacing and disabled state

No component inside a field sets its own outer margin; the field owns the gap.
That is also why the error message carries no `mt-*`.

A disabled control dims its own label, in CSS:

```css
[&:has(>[data-shape-control]:disabled)>[data-shape-label]]:opacity-50
```

Direct children only, and deliberately. A checkbox carries its label inside
itself; matching descendants would mean one disabled radio dimming the labels of
the other four in its group. The alternative is a `disabled` prop plumbed into
three components, all of which then stop folding the moment it is bound
dynamically.

## Reference

### Field

| Prop | Default | Values |
| --- | --- | --- |
| `field-name` | — | the field name every child reads |
| `as` | `div` | `div`, `fieldset` |

### Label

| Prop | Default | Values |
| --- | --- | --- |
| `for` | the field's name | the id of the control it names |
| `as` | `label` | `label`, `legend` |

### Description

| Prop | Default | Values |
| --- | --- | --- |
| `for` | the field's name | the name the id is derived from |

Renders `id="{name}-description"`, which is what `aria-describedby` points at.
Its colour comes from the surface contract, so a field inside a tinted card
stays legible instead of turning to mud.

### Error

| Prop | Default | Values |
| --- | --- | --- |
| `name` | the field's `field-name` | the key to look up |
| `bag` | `default` | the error bag to read |

`name` here, where the label and the description take `for`. Those point at an
element; this names a validation key, which is a different thing that happens to
share a value.

## Folding

Tier A — `@blaze(fold: true)` for all four.

`error` is tier C on top of that: the same directive, with the request-scoped
region cut out by `@unblaze` so everything around it still folds. `label`'s `as`
is a real branch rather than an interpolated tag name — a `<legend>` takes no
`for` — so unlike `heading`'s `level` it is not declared safe.

See [Forms](../forms.md#the-error-hole) and [Folding](../folding.md).
