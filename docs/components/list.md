# List

```blade
<x-shape::list>
    @foreach ($people as $person)
        <x-shape::list.item wire:key="{{ $person->id }}">
            <x-shape::avatar :initials="$person->initials" :alt="$person->name" size="sm" />
            <x-shape::text>{{ $person->name }}</x-shape::text>
        </x-shape::list.item>
    @endforeach
</x-shape::list>
```

## The table's answer without the columns

Reach for a list when the records have one shape rather than several columns —
people, activity, notifications. Everything the table does about separation and
empty states, the list does the same way, so the two are worth learning once.

| Prop | Default | Values |
| --- | --- | --- |
| `as` | `ul` | any list element — `ol` for a ranked list |
| `empty` | `true` | render the built-in empty state |
| `empty-icon` | — | icon name for it |
| `empty-heading` | `Nothing here yet` | |
| `empty-description` | — | |

`as` is only ever interpolated into the tag name, so a list whose element is
decided at runtime still folds.

## Separation is a rule on the list, not a border on each item

`divide-y` on the `<ul>`, nothing on the `<li>`. It draws the same line and has
nothing to reset on the last item — which is the whole of "use fewer borders",
in one class.

An item is a slot rather than a set of props, unlike a table cell. A list item
almost always holds an avatar, two lines of text and a trailing button, and a
`value` prop would buy nothing while costing the composition that is the reason
to reach for a list at all.

## The empty state is always there

```blade
<x-shape::list empty-heading="No teammates yet"
               empty-description="Invite someone to get started." />
```

Rendered every time and removed by a `:has()` rule as soon as one item exists,
for the reason [Table](table.md#the-empty-state-is-always-there) gives at
length: asking Blade whether the slot has items is a runtime question and would
cost the fold. It sits beside the `<ul>` rather than inside it, because an
`<li>` holding an empty state would be an item like any other and would hide
itself.

Pass `:empty="false"` to turn it off.

## Folding

Tier A — `@blaze(fold: true, safe: ['as'])` on the list, `@blaze(fold: true)` on
the item.

See [Folding](../folding.md) and [Data display](../data.md).
