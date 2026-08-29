# Textarea

The [input](input.md)'s chrome without the fixed height. `label`, `description`
and name resolution all work the same way.

@docs('preview', name: 'textarea', layout: 'stack')

## Rows

`rows` is the height floor, in lines:

@docs('preview', name: 'textarea-rows', layout: 'stack')

## Sizes

`size` changes the type and the padding, not the height:

@docs('preview', name: 'textarea-sizes', layout: 'stack')

## It resizes vertically only

A textarea a user can drag wider breaks out of whatever laid it out. `resize-y`
is applied at zero specificity, so `class="resize-none"` or `class="resize"` at
the call site still wins.

## Content is the slot

```blade
<x-shape::textarea name="notes">{{ old('notes') }}</x-shape::textarea>
```

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `sm`, `base`, `lg` |
| `rows` | `3` | the height floor |
| `label` | — | assembles the whole field when given |
| `description` | — | supporting copy, wired to `aria-describedby` |
| `id` | the resolved name | the element id |

The default slot is the content. Everything else passes through to the
`<textarea>`.

## Folding

Tier A — `@blaze(fold: true)`. See [Folding](../folding.md).
