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

## Theming

The four pieces paint from three variables and nothing else. The label is
`--shape-fg`, the description `--shape-fg-muted`, and the error message
`--shape-tone-ink` under a `data-shape-tone="danger"` of its own — so the red a
validation message is written in is the danger tone's ink, and follows a
[retint](../theming.md) rather than being a colour this component keeps. The red
border beside it comes from the same ramp's `500`, on the control.

The type and the gap are written at zero specificity, so a class at the call
site wins:

@docs('preview', name: 'field-override', layout: 'stack')

### Every field at once

Each piece carries an attribute of its own, which is what makes a house style
for forms a handful of rules rather than a prop threaded through four
components:

```css
[data-shape-label] { text-transform: uppercase; letter-spacing: 0.04em; }
[data-shape-description] { font-size: 0.8125rem; }
[data-shape-error] { font-weight: 600; }
```

The disabled treatment is already a rule rather than a prop, for the reason
[above](#spacing-and-disabled-state) — direct children only, so one disabled
radio never dims its group.

## Reference

### Field

| Prop | Default | Values |
| --- | --- | --- |
| `as` | `div` | `div`, `fieldset` |
| `field-name` | — | the field name every child reads |

### Label

| Prop | Default | Values |
| --- | --- | --- |
| `as` | `label` | `label`, `legend` |
| `for` | the field's name | the id of the control it names |

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
