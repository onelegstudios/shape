# Toast

A message about something that just happened. It is announced, and it goes away.

@docs('preview', name: 'toast-live', layout: 'stack')

Put the toaster in your layout once, and raise toasts from the server:

```blade
<x-shape::toaster />
```

```php
Shape::toast()->success('Invoice sent')->send();
```

You do not write `<x-shape::toast>` yourself. The toaster renders one per tone
into a `<template>`, and `shape.js` clones the one the payload asked for.

## Tones

`color` sets the tone and resolves the glyph:

@docs('preview', name: 'toast-tones', layout: 'stack')

There is no `icon()` on the builder, deliberately: the script clones markup, and
it cannot resolve an SVG it was not already given. The rule that falls out of it
is the right one anyway — the glyph and the colour say the same thing, so
neither can be set without the other.

## The builder

```php
Shape::toast()
    ->success('Invoice sent')
    ->description('A copy went to billing@example.com')
    ->duration(8000)
    ->send();
```

| Method | Does |
| --- | --- |
| `heading(?string)` | the line in bold |
| `description(?string)` | a second line under it |
| `color(?string)` | `accent`, `success`, `warning`, `danger` |
| `accent()` `success()` `warning()` `danger()` | the tone, and optionally the heading, in one call |
| `duration(int)` | milliseconds; default `5000` |
| `sticky()` | stay until dismissed |
| `send()` | dispatch it |

Nothing happens until `send()`. How it travels — Livewire's dispatcher or the
session — is [Feedback](../feedback.md#two-calls)'s subject.

## Position

| Prop | Default | Values |
| --- | --- | --- |
| `position` | `bottom-right` | `bottom-right`, `bottom-left`, `bottom-center`, `top-right`, `top-left`, `top-center` |

The toaster is a `popover="manual"`, which puts it in the top layer — which is
why a toast appears **above an open modal** and why there is still no z-index
anywhere in this library. It is also why toasts need `shape.js`: a popover is
`display: none` until something opens it. That trade is set out in
[Feedback](../feedback.md#toasts-need-the-script).

Above a modal, but not clickable while one is open: a modal makes the rest of
the document inert. The container is transparent to the pointer and the toasts
inside it are not, so there is no invisible column over the corner of the page
swallowing clicks.

## The timer

Five seconds, paused while the pointer is over the toast or focus is inside it —
so a toast someone is reading, or tabbing into to dismiss, does not vanish
mid-sentence. `->sticky()` for anything that has to be acted on.

## Announcing

Two live regions, and the tone picks: `danger` is announced assertively,
everything else politely. One region cannot make that distinction.

## From JavaScript

The event is the API, so anything that can dispatch a window event can raise a
toast:

```js
dispatchEvent(new CustomEvent('shape:toast', {
    detail: { toast: { heading: 'Copied', color: 'success', duration: 2000 } },
}))
```

## Toast or alert?

If a message is still true after someone has read it, it is an
[alert](alert.md). A toast is an event.

## Reference

`toast` — for anyone rendering one directly:

| Prop | Default | Values |
| --- | --- | --- |
| `color` | `neutral` | `accent`, `success`, `warning`, `danger` |
| `heading` | — | the line in bold |
| `description` | — | a second line under it |
| `icon` | resolved from `color` | any [icon](icon.md) name, or `false` for none |
| `icon-size` | `sm` | `xs`, `sm`, `base` |
| `dismissible` | `true` | `false` removes the close button |

## Folding

`toast` is tier A — `@blaze(fold: true, safe: ['heading', 'description'])`, which
is what makes the template approach pay: the toaster renders it at compile time,
when a toast's text is never known.

`toaster` is tier D — a plain `@blaze`. It reads the session, and it is the one
component in the library that does so without cutting an `@unblaze` hole. There
is one on a page, so folding it would save nothing and cost a boundary that
variables cannot cross.

See [Folding](../folding.md) and [Feedback](../feedback.md).
