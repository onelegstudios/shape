# Textarea

The [input](input.md)'s chrome without the fixed height. `label`, `description`
and name resolution all work the same way.

@docs('preview', name: 'textarea', layout: 'stack')

## Sizes

`size` changes the type and the padding, not the height:

@docs('preview', name: 'textarea-sizes', layout: 'stack')

## Rows

`rows` is the height floor, in lines:

@docs('preview', name: 'textarea-rows', layout: 'stack')

## It resizes vertically only

A textarea a user can drag wider breaks out of whatever laid it out. `resize-y`
is applied at zero specificity, so `class="resize-none"` or `class="resize"` at
the call site still wins.

## Content is the slot

```blade
<x-shape::textarea name="notes">{{ old('notes') }}</x-shape::textarea>
```

## Theming

The chrome is the [input](input.md#theming)'s, token for token — the same
border, background, radius, shadow, ring and invalid treatment — so anything
said there applies here, and a rule written for both is one rule:

```css
input[data-shape-control],
textarea[data-shape-control] {
    border-radius: 9999px;
}
```

What is the textarea's own is the vertical rhythm: `size` sets the padding
rather than a height, `rows` sets the floor, and `resize-y` is applied at zero
specificity. All three yield to a class:

@docs('preview', name: 'textarea-override', layout: 'stack')

`[data-shape-textarea]` is on the element for the times it should be every
textarea and not this one.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `xs`, `sm`, `base`, `lg`, `xl` |
| `label` | — | assembles the whole field when given |
| `description` | — | supporting copy, wired to `aria-describedby` |
| `id` | the resolved name | the element id |
| `rows` | `3` | the height floor |

The default slot is the content. Everything else passes through to the
`<textarea>`.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
