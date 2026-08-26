# Field

What every control assembles when you give it a `label`. Reach for these
directly only when the shorthand cannot express what you need.

```blade
{{-- This… --}}
<x-shape::input type="email" label="Email" description="For receipts." wire:model="email" />

{{-- …renders this. --}}
<x-shape::field name="email">
    <x-shape::label>Email</x-shape::label>
    <x-shape::description>For receipts.</x-shape::description>
    <x-shape::input type="email" aria-describedby="email-description" wire:model="email" />
    <x-shape::error />
</x-shape::field>
```

Compose by hand when you need a control between the label and the description,
two controls in one field, or markup of your own between the pieces. Groups are
the other case — there is no single control for a shorthand to live on.

## The name is stated once

`name` is written on the field and nowhere else. The label takes its `for` from
it, the description its `id`, the control its `id` and `name`, the error the key
it looks up. Passing it down by hand means writing it four times and getting it
wrong once.

An explicit value on a child still wins:

```blade
<x-shape::label for="something-else">Email</x-shape::label>
```

| Prop | Default | Values |
| --- | --- | --- |
| `name` | — | the field name every child reads |
| `as` | `div` | `div`, `fieldset` |

## Fieldset mode

A group of radios or checkboxes is only a group if it is a `<fieldset>` with a
`<legend>` naming it:

```blade
<x-shape::field as="fieldset" name="billing">
    <x-shape::label as="legend">Billing period</x-shape::label>
    <x-shape::radio value="monthly" label="Monthly" />
    <x-shape::radio value="yearly" label="Yearly" />
</x-shape::field>
```

The reset classes for `<fieldset>` are applied unconditionally, so `as` stays a
pass-through prop. `min-w-0` is the load-bearing one — without it a fieldset
refuses to shrink below its content and breaks any flex parent it is placed in.

## It owns the spacing

No component inside a field sets its own outer margin. The field owns the gap, so
"which element does this space belong to" is never a question. That is also why
the error message carries no `mt-*`.

## Disabled state, without prop plumbing

A disabled control dims its own label:

```css
[&:has(>[data-shape-control]:disabled)>[data-shape-label]]:opacity-50
```

Direct children only, and deliberately. A checkbox carries its label inside
itself; matching descendants would mean one disabled radio dimming the labels of
the other four in its group.

The alternative is a `disabled` prop plumbed into three components, all of which
then stop folding the moment it is bound dynamically.

## Label

| Prop | Default | Values |
| --- | --- | --- |
| `for` | the field's `name` | the id of the control it names |
| `as` | `label` | `label`, `legend` |

`as` is a real branch rather than an interpolated tag name — a `<legend>` takes
no `for` — so unlike `heading`'s `level` it is **not** declared safe.

## Description

| Prop | Default | Values |
| --- | --- | --- |
| `for` | the field's `name` | the name the id is derived from |

Renders `id="{name}-description"`, which is what `aria-describedby` points at.
Its colour comes from the surface contract (`--shape-fg-muted`), so a field
inside a tinted card stays legible instead of turning to mud.

## Error

| Prop | Default | Values |
| --- | --- | --- |
| `for` | the field's `name` | the key to look up |
| `bag` | `default` | the error bag to read |

Renders nothing when the field is valid, when no bag was shared, and when it has
no name to look up — an empty key would otherwise make it display any error in
the bag, including a neighbour's.

`role="alert"` and nothing else: that role already implies an assertive live
region, and pairing it with `aria-live="polite"` asks for both at once.

## Folding

Tier A — `@blaze(fold: true)` for all four.

`error` cuts the request-scoped region out with `@unblaze` and keeps folding
everything around it. See [Forms](../forms.md#the-error-hole) for the three rules
that come with that, and [Folding](../folding.md) for what a dynamic `:name`
costs.
