# Empty

The screen someone sees before there is any data, and again every time they
filter it all away.

@docs('preview', name: 'empty', layout: 'stack')

## Sizes

An empty state is mostly room, so that is mostly what `size` moves — and the
mark, the headline and the sentence go with it, because a 24px glyph over 20
pixels of padding reads as a mark that outgrew its box:

@docs('preview', name: 'empty-sizes', layout: 'stack')

`icon-size` follows unless you name it. The two scales are not the same length:
the glyph runs out at `xl` and so does the heading, while the padding could go on,
so the top of this scale grows the room and holds the type.

The [table](table.md#sizes) and the [list](list.md#sizes) hand theirs down to the
empty state they render for you.

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
<x-shape::table empty-icon="shape-plus"
                empty-heading="No invoices yet"
                empty-description="They'll appear here as you raise them.">
    …
</x-shape::table>
```

The [select](select.md) does not, and cannot: a `<select>` may contain only
`option`, `optgroup` and script-supporting elements, so an empty state written
inside one is discarded by the HTML parser. Its `placeholder` option is the
equivalent.

## Theming

Everything here is neutral and comes from the token layer: the icon sits in a
`shape-100` puck — `shape-800` in dark mode — painted `--shape-fg-muted`, the
headline is a [heading](heading.md) and the copy a muted [text](text.md), and
the generous padding is written at zero specificity. So a class at the call site
reaches the box:

@docs('preview', name: 'empty-override', layout: 'stack')

The two components inside it are not the bag's to reach, and neither is the
puck. A rule of your own is:

```css
[data-shape-empty] > span { background-color: transparent; }
```

### The ones you did not write

[Table](table.md) and [list](list.md) render an empty state for you, and their
`empty-*` props carry the copy and nothing else. A rule is the only way to reach
those instances — which is also the only way to reach *all* of them at once:

```css
[data-shape-table] [data-shape-empty],
[data-shape-list] [data-shape-empty] {
    padding-block: 2rem;
}
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `icon` | — | any [icon](icon.md) name |
| `icon-size` | resolved from `size` | `xs`, `sm`, `base`, `lg`, `xl` |
| `heading` | — | the headline |
| `description` | — | one line of supporting copy |

The default slot is the action row. It is always rendered and collapsed with the
CSS `empty:hidden` variant when nothing was passed, which answers "does this
slot have content" in the browser rather than at render time — where asking it
would cost the component its fold.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
