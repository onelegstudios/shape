# Alert

A message that stays in the flow of the page it belongs to. Other libraries call
this a callout.

@docs('preview', name: 'alert', layout: 'stack')

## Tones

`tone` says what the alert means, and resolves a matching icon. With no tone the
alert is neutral and draws no glyph:

@docs('preview', name: 'alert-tones', layout: 'stack')

## Heading and body

`heading` is a title above the body; the default slot is the body. Either can
stand alone:

@docs('preview', name: 'alert-heading', layout: 'stack')

## Icons

Every state colour resolves a glyph of its own, so an alert stays readable in
greyscale and to anyone who can't separate the hues. `icon` picks a different
one, `:icon="false"` removes it, and `icon-size` changes how big it is:

`brand` and `accent` are the exceptions, and deliberately: both are emphasis
rather than a state, so they draw nothing. An informational message wants
[`info`](../theming.md#the-state-colours-are-not-yours-to-rebrand), which is blue
whatever the brand becomes.

@docs('preview', name: 'alert-icons', layout: 'stack')

## Dismissing

`dismissible` adds a close button. `shape.js` removes the nearest alert when it
is clicked:

@docs('preview', name: 'alert-dismissible', layout: 'stack')

Dismissal is not remembered. If an alert should stay dismissed, that is state
your application owns.

## Muted text inside an alert

An alert publishes its own foreground, so a nested muted paragraph reads a
dialled-back version of the tone rather than grey on pink:

@docs('preview', name: 'alert-surface', layout: 'stack')

Nothing was passed down — see [Theming](../theming.md#the-surface-contract).

## Alert or toast

If a message is still true after someone has read it, it is an alert. A
[toast](toast.md) is an event: it happened, it is announced, it goes away.

An alert carries no `role="alert"` and no `aria-live`, because this markup was on
the page when it loaded and announcing it repeats what a screen reader is about
to read anyway. Announcements belong to the toaster, where content arrives after
the fact.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `tone` | `neutral` | `info`, `success`, `warning`, `danger`, `brand`, `accent` |
| `heading` | — | a title above the body |
| `icon` | resolved from `tone` | any [icon](icon.md) name, or `false` for none |
| `icon-size` | `sm` | `xs`, `sm`, `base` |
| `dismissible` | `false` | adds a close button |

The default slot is the body.

## Folding

Tier A — `@blaze(fold: true, safe: ['heading'])`.

`heading` is interpolated and nothing more, so an alert whose title comes from a
variable still folds. `tone` branches to resolve its glyph, so `:tone="$tone"`
drops to the compiled path — the same prop is safe on the [button](button.md),
which only ever interpolates it. See [Folding](../folding.md).
