# Input

```blade
<x-shape::input type="email" label="Email" wire:model="email" />
```

## It never has to be told its name

The control resolves one, most specific first: an explicit `name`, then the
[field](field.md) it sits in, then the Livewire binding. So `wire:model="email"`
alone wires up the label's `for`, this element's `id` and `name`, and the key the
error message looks up.

```blade
{{-- All three of these produce name="email" id="email" --}}
<x-shape::input name="email" />
<x-shape::field field-name="email"><x-shape::input /></x-shape::field>
<x-shape::input wire:model="email" />
```

| Prop | Default | Values |
| --- | --- | --- |
| `type` | `text` | any input type |
| `size` | `base` | `sm`, `base`, `lg` |
| `label` | — | assembles the whole field when given |
| `description` | — | supporting copy, wired to `aria-describedby` |
| `id` | the resolved name | the element id |

## Breaking it apart

Passing a `label` makes this component render the whole field around itself,
which is how you should write almost every field. When you need something that
shape cannot express, the [primitives](field.md) are underneath it — identical
output either way, because the shorthand renders them.

The `@if` that chooses between the two asks about a prop, and a question about a
prop is answered when the template compiles; asking the same of a slot would be a
runtime question and would cost this component its fold.

## Invalid state

There is no `invalid` prop. Styling keys off `aria-invalid`, so what a screen
reader announces and what a sighted user sees cannot drift apart:

```blade
<x-shape::input wire:model="email" aria-invalid="true" />
```

## Overriding styles

Every default carries zero specificity:

```blade
<x-shape::input class="rounded-full font-mono" wire:model="email" />
```

## Folding

Tier A — `@blaze(fold: true)`.

Nothing here branches on a dynamic value, so an input folds at essentially every
call site. The one thing that takes it off the path is a dynamic `:name` on the
field around it, which is a property of the field rather than of this component.
