# Modal

```blade
<x-shape::overlay.trigger for="delete-project" variant="subtle" color="danger">
    Delete project
</x-shape::overlay.trigger>

<x-shape::modal name="delete-project" heading="Delete project" description="This cannot be undone.">
    <x-shape::text size="sm">Every invoice attached to it goes too.</x-shape::text>

    <x-shape::overlay.footer>
        <x-shape::overlay.close for="delete-project" label="Cancel" />
        <x-shape::button variant="primary" color="danger">Delete</x-shape::button>
    </x-shape::overlay.footer>
</x-shape::modal>
```

## It is a `<dialog>`

Opened with `showModal()`, which is where the focus trap, the top layer, Escape
and the inertness of everything behind it come from. None of those four is this
package's code, and all four are the parts a hand-rolled modal gets wrong.

Two consequences worth knowing. There is no backdrop component: the scrim is
`::backdrop`, styled in `shape.css`. And there is no `z-index` anywhere, because
the top layer is above every stacking context on the page — a modal cannot be
covered by a sticky header or clipped by an ancestor's `overflow: hidden`.

| Prop | Default | Values |
| --- | --- | --- |
| `name` | *required* | the dialog's id; what a trigger points `for` at |
| `heading` | — | renders the title row and names the dialog |
| `description` | — | a line under the heading, referenced by `aria-describedby` |
| `size` | `base` | `sm`, `base`, `lg` |
| `dismissible` | `true` | `false` removes the close button and holds Escape off |

## `name` is required, and cannot be generated

A folded component is pre-rendered once, at compile time. An id generated inside
one — `uniqid()`, a random string, a counter — is generated once too, and every
instance on the page renders the same one. Ids that have to be unique per
instance come from the call site or they are not unique at all.

## Opening and closing

`command` and `commandfor` are the platform's invoker attributes, so the trigger
opens the dialog with no script:

```blade
<x-shape::overlay.trigger for="delete-project">Delete</x-shape::overlay.trigger>
<x-shape::overlay.close for="delete-project" label="Cancel" />
```

Where those attributes aren't supported yet, `shape.js` delegates one click
listener for the document and calls `showModal()` itself. Same markup, same
behaviour, twenty lines of fallback.

`<form method="dialog">` is the other zero-script way to close a dialog, and it
is deliberately not what `overlay.close` renders: modals contain forms, and a
form inside a form is invalid HTML that browsers resolve by dropping one.

From the server, when the decision genuinely lives there:

```php
$this->dispatch('shape:open', name: 'delete-project');
```

## Heading and description are props

Not slots. A question about a prop is answered when the template compiles; the
same question asked of a slot is asked at run time and costs the component its
fold. The slot is the body; actions go in `<x-shape::overlay.footer>`.

Passing neither leaves a bare panel — useful when the content brings its own
header, and the case where you should set `aria-label` yourself.

## Not dismissible

```blade
<x-shape::modal name="terms" heading="Accept the terms" :dismissible="false">
```

Removes the close button and prevents the `cancel` event, which is the one line
of JavaScript a modal needs: Escape closes a dialog unless something stops it.
Use it for a decision that must be answered, not to trap someone.

## Translating the close button

`overlay.close` renders `aria-label="Close"` as a literal. It is not `__('Close')`
because a folded component resolves a translation once, at compile time, and
serves that locale to every visitor afterwards. Translate at the call site, where
it is still evaluated per request:

```blade
<x-shape::overlay.close for="terms" :label="__('Cancel')" />
```

See [Folding](../folding.md#translations-bake-too).

## Folding

Tier A — `@blaze(fold: true)`, including the dialog itself.

This is a correction to what the plan for this library expected. A modal was
filed as compile-only on the assumption that it has to inspect its slots to know
whether it has a header or a footer. Built on `<dialog>` it inspects nothing.
