# Forms

Forms are where a component library either earns its keep or gets in the way, and
where Shape's compile-time folding is under the most pressure — because
validation errors are request state, and folding bakes markup in at compile time.

Both problems have the same answer: state the name once, and cut exactly one hole
in the fold.

## Start with the shorthand

One call site does the whole field:

```blade
<x-shape::input
    type="email"
    label="Email"
    description="We'll only use this for receipts."
    wire:model="email"
/>
```

That renders a field, a label wired to the control, a description the control
points at with `aria-describedby`, the control itself, and the error message for
`email`. Every control takes `label` and `description`, so this is the shape of
almost every field you will write.

It is also the form that folds most reliably, because everything it needs is
known at the call site. Reaching for the primitives is a choice with a cost, and
it is worth knowing which cost before you pay it.

## Breaking it apart

The shorthand is not a separate implementation. It renders these:

```blade
<x-shape::field field-name="email">
    <x-shape::label>Email</x-shape::label>
    <x-shape::description>We'll only use this for receipts.</x-shape::description>
    <x-shape::input type="email" aria-describedby="email-description" wire:model="email" />
    <x-shape::error />
</x-shape::field>
```

Compose by hand when you need something the shorthand cannot express: a control
between the label and the description, two controls in one field, markup of your
own between the pieces. Note the one thing you take on by doing it —
`aria-describedby`, which the shorthand set for you. See
[below](#aria-describedby-in-the-composed-form).

## The name is stated once

`<x-shape::field field-name="email">` is the only place the name appears. Everything
inside reads it back:

| Component | What it does with the name |
| --- | --- |
| `label` | its `for` |
| `description` | its `id`, as `{name}-description` |
| `input` / `textarea` / `select` | its `id` and its `name` |
| `checkbox` / `radio` / `switch` | its `name`; the `value` separates the group |
| `error` | the key it looks up |

### Why `field-name` and not `name`

Because `@aware` walks the whole ancestor stack, not just the nearest field. A
child reading `name` would accept one from anything that had it — your own
`<x-panel name="Billing">` wrapped around a form would rename every control
inside it to `Billing`, beating even an explicit `wire:model`. Nothing else in a
template is called `field-name`, so nothing else can answer for it.

It also keeps `name` free to mean what it usually means: `<x-shape::error
name="email" />` and `<x-shape::input name="email" />` are ordinary props, not
values competing with an inherited one.

## Resolving a control's name

A control resolves its name from three places, most specific first:

1. an explicit `name` on the control,
2. the field it sits in,
3. the Livewire binding.

So the shortest working call site states nothing at all:

```blade
{{-- `wire:model` alone gives the label its `for`, the input its `id` and
     `name`, and the error message the key to look up. --}}
<x-shape::input label="Email" wire:model="email" />
```

## Groups are real fieldsets

Groups are the one case with no shorthand, because there is no single control to
hang it on. A radio group with no accessible name is a set of unrelated radios,
and `field` and `label` both take an `as`, which is all a group needs:

```blade
<x-shape::field as="fieldset" field-name="billing">
    <x-shape::label as="legend">Billing period</x-shape::label>

    <x-shape::radio value="monthly" label="Monthly" />
    <x-shape::radio value="yearly" label="Yearly" description="Two months free." />

    <x-shape::error />
</x-shape::field>
```

Every radio inherits `name="billing"` and gets an id of `billing-{value}`. The
`<legend>` names the group, which is the part hand-rolled radio groups almost
always miss.

## The error hole

Validation messages are request state. A folded component is pre-rendered once,
at compile time — so a message compiled into the template would be served to
everyone who came after the person who triggered it.

`error` is the only component in the set that touches that state, and it isolates
it:

```blade
@unblaze(scope: ['name' => $target, 'bag' => $bag, 'class' => $classes])
    {{-- request-scoped; runs per render --}}
@endunblaze
```

Everything around the hole still folds. Three rules come out of it, and they
apply to any component you write with the same shape:

- **Nothing crosses the boundary implicitly.** Variables have to be listed in
  `scope` and read back off `$scope`.
- **`$attributes` cannot cross it at all**, and it fails asymmetrically: outside
  a fold the block compiles inline and the bag resolves, inside one it does not.
  Build what you need outside and hand it in.
- **`scope` values are written out with `var_export`**, so they must be plain
  scalars, and anything in there has to be known at compile time.

## Spacing

No control sets its own outer margin. The field owns the space between its
children, so a label, a control and a message are spaced by their parent and
never by themselves. Stack fields with your own layout:

```blade
<div class="space-y-5">
    <x-shape::input label="Name" wire:model="name" />
    <x-shape::input label="Email" type="email" wire:model="email" />
</div>
```

## `aria-describedby` in the composed form

A control claims `aria-describedby` only when it rendered the description
itself — which the shorthand always does. In the composed form the description is
a sibling the control cannot see, so it stays quiet rather than pointing at an id
that might not exist. A dangling reference is worse than an absent one.

If you compose by hand and want the link, say so:

```blade
<x-shape::field field-name="email">
    <x-shape::label>Email</x-shape::label>
    <x-shape::description>We'll only use this for receipts.</x-shape::description>
    <x-shape::input type="email" aria-describedby="email-description" wire:model="email" />
</x-shape::field>
```

Or use the shorthand, which does it for you.

## Folding

Every component in this set is tier A — `@blaze(fold: true)`.

The cost of stating the name once is that `@aware` props count as unsafe. A
dynamic `:name` on the field takes **every** child off the fold path, not just
the one that reads it:

```blade
{{-- Folds: label, description, control and error are all inlined. --}}
<x-shape::field field-name="email"> … </x-shape::field>

{{-- Does not fold. Renders correctly, through the compiled path. --}}
<x-shape::field :field-name="$field->name"> … </x-shape::field>
```

Field names are literals in almost every real form, so this is rarely the case
you are in. When you are — a form built from a schema, say — it is a fold you
lose, not correctness.

See [Folding](folding.md).
