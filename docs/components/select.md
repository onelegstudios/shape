# Select

A native `<select>`, restyled. Options are children.

@docs('preview', name: 'select', layout: 'stack')

## Placeholder

`placeholder` renders prompt text as an unchoosable first option — `disabled
selected hidden` together, which is the only way a native select shows prompt
text without offering it as an answer. Leave it off when one option should be
selected instead:

@docs('preview', name: 'select-placeholder', layout: 'stack')

It is also the select's empty state. A `<select>` may contain only `option`,
`optgroup` and script-supporting elements, so an [empty state](empty.md) written
inside one is discarded by the HTML parser before it reaches the page.

## Sizes

@docs('preview', name: 'select-sizes', layout: 'stack')

## Disabled

@docs('preview', name: 'select-disabled', layout: 'stack')

The arrow dims with the control through `has-disabled:`, so the two never
disagree about whether the thing is interactive.

## Options

Write plain `<option>` children, with whatever `@foreach`, `<optgroup>` or
`@selected` you already have:

```blade
<x-shape::select label="Plan" wire:model="plan">
    @foreach ($plans as $plan)
        <option value="{{ $plan->id }}" @selected($plan->is($current))>{{ $plan->name }}</option>
    @endforeach
</x-shape::select>
```

There is also `<x-shape::select.option label="Monthly" value="monthly" />`, which
renders a plain `<option>` and exists so options can be written the way the rest
of the library is. Both are equally correct.

## It stays a native select

`appearance-none` removes the platform arrow and the component draws its own,
reserving the room for it. Everything else is the browser's: keyboard
behaviour, the mobile picker, type-ahead.

## Theming

The control's chrome is the [input](input.md#theming)'s, and a class at the call
site reaches it the same way — the attribute bag lands on the `<select>`, not on
the wrapper:

@docs('preview', name: 'select-override', layout: 'stack')

The arrow is the one piece a class cannot reach, because it is a sibling inside
the wrapper rather than part of the control the bag lands on. It paints in
`--shape-fg-muted` and dims with the control, and a rule of your own can find it
through the wrapper:

```css
[data-shape-select] [data-shape-icon] { color: var(--color-shape-400); }
```

The room it sits in is the control's right padding, which `size` picks, so an
arrow of a different size wants that padding moved with it.

What the option list looks like when it is open is the platform's and not this
package's. It is drawn by the operating system, and a `<select>` that is
restyled far enough to look otherwise has stopped being the thing that gives you
the mobile picker and the type-ahead for free — see
[It stays a native select](#it-stays-a-native-select).

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `placeholder` | — | prompt text, as an unchoosable first option |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `label` | — | assembles the whole field when given |
| `description` | — | supporting copy, wired to `aria-describedby` |
| `id` | the resolved name | the element id |

`select.option` takes `label` and `value`. The default slot is the options.

## Folding

Tier A — `@blaze(fold: true)`. `select.option` is tier B —
`@blaze(fold: true, memo: true, safe: ['label', 'value'])` — so a list of options
built from a collection still folds. See [Folding](../folding.md).
