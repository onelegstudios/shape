# Empty

The screen someone sees before there is any data, and again every time they
filter it all away.

@docs('preview', name: 'empty', layout: 'stack')

## Actions

The default slot is for actions, laid out in a centred row under the copy. An
empty state without one is a dead end, so give it a next step wherever there is
a sensible one:

@docs('preview', name: 'empty-actions', layout: 'stack')

## Rendered for you

[Table](table.md) and [list](list.md) both render one by default, and neither
ever asks whether it has rows — a `:has()` rule removes the empty state when a
row appears. Set the copy through their `empty-*` props:

```blade
<x-shape::table empty-icon="plus"
                empty-heading="No invoices yet"
                empty-description="They'll appear here as you raise them.">
    …
</x-shape::table>
```

The [select](select.md) does not, and cannot: a `<select>` may contain only
`option`, `optgroup` and script-supporting elements, so an empty state written
inside one is discarded by the HTML parser. Its `placeholder` option is the
equivalent.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `icon` | — | any icon name |
| `icon-variant` | `outline` | `micro`, `mini`, `solid`, `outline` |
| `heading` | — | the headline |
| `description` | — | one line of supporting copy |

The default slot is the action row. It is always rendered and collapsed with the
CSS `empty:hidden` variant when nothing was passed, which answers "does this
slot have content" in the browser rather than at render time — where asking it
would cost the component its fold.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
