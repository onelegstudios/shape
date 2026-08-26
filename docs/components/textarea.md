# Textarea

```blade
<x-shape::textarea label="Notes" rows="5" wire:model="notes" />
```

The input's chrome without the fixed height. Name resolution, the shorthand and
the `aria-describedby` rule all work exactly as they do on
[input](input.md).

| Prop | Default | Values |
| --- | --- | --- |
| `size` | `base` | `sm`, `base`, `lg` |
| `rows` | `3` | the height floor |
| `label` | — | assembles the whole field when given |
| `description` | — | supporting copy, wired to `aria-describedby` |
| `id` | the resolved name | the element id |

## It resizes vertically only

A textarea a user can drag wider breaks out of whatever laid it out. `resize-y`
is applied at zero specificity, so `class="resize-none"` or `class="resize"` at
the call site still wins.

## Content is the slot

```blade
<x-shape::textarea name="notes">{{ old('notes') }}</x-shape::textarea>
```

## Folding

Tier A — `@blaze(fold: true)`.
