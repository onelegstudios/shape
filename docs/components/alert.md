# Alert

```blade
<x-shape::alert color="warning" heading="Your trial ends on Friday">
    Add a payment method to keep your projects.
</x-shape::alert>
```

Other libraries call this a callout. It is the same component: a message that
stays in the flow of the page it belongs to.

| Prop | Default | Values |
| --- | --- | --- |
| `color` | — | `accent`, `success`, `warning`, `danger` |
| `heading` | — | a title above the body |
| `icon` | resolved from `color` | any icon name, or `false` for none |
| `iconVariant` | `mini` | `micro`, `mini`, `outline`, `solid` |
| `dismissible` | `false` | `true` adds a close button |

## Alert or toast

If a message is still true after someone has read it, it is an alert. A toast is
an event — it happened, it is announced, it goes away. See
[Feedback](../feedback.md#where-each-part-goes).

## Colour is never the only signal

Every tone resolves a glyph of its own, so an alert stays readable in greyscale
and to anyone who cannot separate the hues. `:icon="false"` opts out; forgetting
is not possible.

## It publishes its own foreground

`data-shape-surface="tint"` is on the rendered element, and it is doing more work
than it looks like. It republishes the tone's ink as this element's foreground
contract, so a nested muted paragraph reads a dialled-back version of the tone
rather than the global grey:

```blade
<x-shape::alert color="danger" heading="Card declined">
    {{-- Reads a muted red, not grey-on-pink. Nothing was passed down. --}}
    <x-shape::text size="sm" variant="muted">Update the card on file.</x-shape::text>
</x-shape::alert>
```

Grey text on a coloured background is the one thing the surface contract exists
to make structurally impossible rather than merely documented.

## Not a live region

No `role="alert"`, no `aria-live`. This markup was on the page when it loaded, and
announcing it repeats what a screen reader is about to read anyway. Announcements
belong to the toaster, where content arrives after the fact.

## Dismissing

```blade
<x-shape::alert color="accent" dismissible>Weekly digest is on.</x-shape::alert>
```

The close button carries `data-shape-dismiss` and `shape.js` removes the nearest
alert or toast. It is the one job in this library with no platform primitive
behind it — a dialog closes itself and a popover hides itself, but an element
someone asked to go away has to be taken out by something.

Dismissal is not remembered. If an alert should stay dismissed, that is state the
application owns.

## Folding

Tier A — `@blaze(fold: true, safe: ['heading'])`.

`heading` is interpolated and nothing more, so an alert whose title comes from a
variable still folds. `color` is **not** safe here: the alert branches on it to
resolve its glyph, exactly as the badge does, so `:color="$tone"` drops to the
compiled path. The same prop is safe on the button, which only ever interpolates
it — what a component does with a value is what decides it.
