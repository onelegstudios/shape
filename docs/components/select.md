# Select

```blade
<x-shape::select label="Plan" placeholder="Choose a plan" wire:model="plan">
    <option value="monthly">Monthly</option>
    <option value="yearly">Yearly</option>
</x-shape::select>
```

## Options are children, not an array

A slot rules memoization out — but a select is not a per-row component, so memo
was never available to it anyway. What children buy is everything an array prop
would have to reinvent: `@foreach`, `<optgroup>`, `selected`, and any markup a
caller already has.

```blade
<x-shape::select wire:model="plan">
    @foreach ($plans as $plan)
        <option value="{{ $plan->id }}" @selected($plan->is($current))>{{ $plan->name }}</option>
    @endforeach
</x-shape::select>
```

There is also `<x-shape::select.option>`, which renders a plain `<option>` and
exists so options can be written the way the rest of the library is. Plain
`<option>` children are equally correct and cost nothing.

| Prop | Default | Values |
| --- | --- | --- |
| `placeholder` | — | prompt text, rendered as an unchoosable first option |
| `size` | `base` | `sm`, `base`, `lg` |
| `label` | — | assembles the whole field when given |
| `description` | — | supporting copy, wired to `aria-describedby` |
| `id` | the resolved name | the element id |

## The placeholder is also its empty state

A [table](table.md) and a [list](list.md) render an
[empty state](empty.md) when they have nothing in them. A select cannot: a
`<select>` may contain only `option`, `optgroup` and script-supporting elements,
and anything else inside one is discarded by the HTML parser before it reaches
the page. The `placeholder` option does the job that an empty state would do
elsewhere.

## The placeholder cannot be chosen

`disabled selected hidden` together are the only way a native select shows prompt
text without offering it as an answer.

## It stays a native select

`appearance-none` removes the platform arrow and the component draws its own,
reserving the room for it. Everything else is the browser's: keyboard behaviour,
the mobile picker, type-ahead. Rebuilding this control out of divs would trade
all of that for a custom arrow.

The arrow is `aria-hidden`, and it dims with the control through
`has-disabled:` so the two never disagree about whether the thing is interactive.

## Folding

Tier A — `@blaze(fold: true)`. `select.option` is tier B —
`@blaze(fold: true, memo: true, safe: ['label', 'value'])` — so a list of options
built from a collection still folds.
