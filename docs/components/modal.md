# Modal

A `<dialog>`, opened with `showModal()`. `name` is the id a trigger points at.

@docs('preview', name: 'modal')

The focus trap, the top layer, Escape and the inertness of everything behind it
are the platform's, not this package's — and they are the parts a hand-rolled
modal gets wrong. Two consequences: the scrim is `::backdrop` rather than a
component, and there is no `z-index` anywhere, so a modal cannot be covered by a
sticky header or clipped by an ancestor's `overflow: hidden`.

## Sizes

@docs('preview', name: 'modal-sizes')

## Heading and description

`heading` renders the title row and names the dialog; `description` adds a line
under it, referenced by `aria-describedby`. Passing neither leaves a bare panel
— useful when the content brings its own header, and the case where you should
set `aria-label` yourself:

@docs('preview', name: 'modal-heading')

Both are props rather than slots. A question about a prop is answered when the
template compiles; the same question asked of a slot is asked at run time and
costs the component its fold. The default slot is the body; actions go in
`overlay.footer`.

## Not dismissible

`:dismissible="false"` removes the close button and turns off Escape and the
outside click together. Use it for a decision that must be answered, not to trap
someone:

@docs('preview', name: 'modal-not-dismissible')

## Opening and closing

`command` and `commandfor` are the platform's invoker attributes, so a trigger
opens the dialog with no script:

```blade
<x-shape::overlay.trigger for="delete-project">Delete</x-shape::overlay.trigger>
<x-shape::overlay.close for="delete-project" label="Cancel" />
```

Where those attributes aren't supported yet, `shape.js` delegates one click
listener and calls `showModal()` itself — same markup, same behaviour.

From the server, when the decision genuinely lives there:

```php
$this->dispatch('shape:open', name: 'delete-project');
```

`<form method="dialog">` is the other zero-script way to close a dialog, and it
is deliberately not what `overlay.close` renders: modals contain forms, and a
form inside a form is invalid HTML that browsers resolve by dropping one.

## Clicking outside

Closes it. That is `closedby="any"`, the platform's own light dismiss — worth
knowing about because a `<dialog>` does **not** do it by default. `shape.js`
carries a fallback for browsers that ignore the attribute.

## `name` is required, and cannot be generated

A folded component is pre-rendered once, at compile time. An id generated inside
one — `uniqid()`, a random string, a counter — is generated once too, and every
instance on the page renders the same one. Ids that have to be unique per
instance come from the call site or they are not unique at all.

## Translating the close button

`overlay.close` renders `aria-label="Close"` as a literal, because a folded
component resolves a translation once at compile time and serves that locale to
every visitor afterwards. Translate at the call site:

```blade
<x-shape::overlay.close for="terms" :label="__('Cancel')" />
```

See [Folding](../folding.md#translations-bake-too).

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the dialog's id; what a trigger points `for` at |
| `heading` | — | renders the title row and names the dialog |
| `description` | — | a line under the heading, referenced by `aria-describedby` |
| `size` | `base` | `sm`, `base`, `lg` |
| `dismissible` | `true` | `false` removes the close button and holds Escape off |

`overlay.trigger` takes `for` and `command` (`show-modal` by default) and passes
everything else to a [button](button.md). `overlay.close` takes `for` and
`label` — without a label it renders an icon button. `overlay.footer` takes no
props and lays its children out in a right-aligned row.

## Folding

Tier A — `@blaze(fold: true)`, including the dialog itself. Built on `<dialog>`
it inspects no slots. See [Folding](../folding.md) and
[Overlays](../overlays.md).
