# Input

A text input. Give it a `label` and it assembles the whole field around itself.

@docs('preview', name: 'input', layout: 'stack')

## Label and description

`label` renders the field, the label and the error slot. `description` adds
supporting copy and wires it to `aria-describedby`:

@docs('preview', name: 'input-label', layout: 'stack')

Without a `label` you get the bare control, for composing by hand — see
[Field](field.md).

## Sizes

@docs('preview', name: 'input-sizes', layout: 'stack')

## Types

`type` passes straight through, so every native input type works and keeps its
platform behaviour:

@docs('preview', name: 'input-types', layout: 'stack')

## Invalid

There is no `invalid` prop. Styling keys off `aria-invalid`, so what a screen
reader announces and what a sighted user sees cannot drift apart:

@docs('preview', name: 'input-invalid', layout: 'stack')

The message underneath comes from the [error](field.md#error) component, which
the shorthand renders for you and which reads Laravel's error bag. See
[Forms](../forms.md#the-error-hole).

## Disabled

@docs('preview', name: 'input-disabled', layout: 'stack')

The label dims with the control, and the field does that in CSS rather than by
plumbing a `disabled` prop through three components.

## It never has to be told its name

The control resolves one, most specific first: an explicit `name`, then the
[field](field.md) it sits in, then the Livewire binding. So `wire:model="email"`
alone wires up the label's `for`, this element's `id` and `name`, and the key
the error message looks up:

```blade
{{-- All three produce name="email" id="email" --}}
<x-shape::input name="email" />
<x-shape::field field-name="email"><x-shape::input /></x-shape::field>
<x-shape::input wire:model="email" />
```

## Overriding styles

Every default carries zero specificity:

```blade
<x-shape::input class="rounded-full font-mono" wire:model="email" />
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `type` | `text` | any input type |
| `size` | `base` | `sm`, `base`, `lg` |
| `label` | — | assembles the whole field when given |
| `description` | — | supporting copy, wired to `aria-describedby` |
| `id` | the resolved name | the element id |

Everything else — `placeholder`, `required`, `disabled`, `wire:model`,
`aria-invalid` — passes through to the `<input>`.

## Folding

Tier A — `@blaze(fold: true)`.

Nothing here branches on a dynamic value, so an input folds at essentially every
call site. The one thing that takes it off the path is a dynamic `:field-name`
on the field around it. See [Folding](../folding.md).
