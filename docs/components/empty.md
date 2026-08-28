# Empty

```blade
<x-shape::empty
    icon="information-circle"
    heading="No invoices yet"
    description="Invoices you send will show up here."
/>
```

## Why this is a component at all

The empty state is the screen someone sees first, before there is any data to
look at — and again every time they filter it all away. Most libraries leave it
to the application, which is why most applications ship a blank div.

Shipping it means it gets designed once.

| Prop | Default | Values |
| --- | --- | --- |
| `icon` | — | any icon name |
| `icon-variant` | `outline` | `micro`, `mini`, `solid`, `outline` |
| `heading` | — | the headline |
| `description` | — | one line of supporting copy |

The default slot is for actions:

```blade
<x-shape::empty icon="plus" heading="No invoices yet" description="Send one to get started.">
    <x-shape::button variant="primary" icon="plus">New invoice</x-shape::button>
    <x-shape::button variant="ghost">Import</x-shape::button>
</x-shape::empty>
```

An empty state without an action is a dead end. Give it one wherever there's a
sensible next step.

## Props, not slots

`heading` and `description` are props rather than slots on purpose. A condition
on a prop is answered when the template compiles; a condition on a slot is a
runtime question, and asking it would cost the component its fold.

The action row is the one slot, and it is always rendered — the CSS `empty:hidden`
variant collapses it when nothing was passed, which answers the same question in
the browser instead of at render time.

## Where it is rendered for you

[Table](table.md) and [list](list.md) both render one by default, and neither of
them ever asks whether it has rows — a `:has()` rule in the stylesheet removes
the empty state when a row appears. [Data display](../data.md) explains the
arrangement and its two visible consequences.

The [select](select.md) does not, and cannot: a `<select>` may contain only
`option`, `optgroup` and script-supporting elements, so an empty state written
inside one is discarded by the HTML parser before any stylesheet sees it. Its
`placeholder` option is the equivalent.

## Folding

Tier A — `@blaze(fold: true)`.

See [Folding](../folding.md).
