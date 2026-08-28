# Toast

```blade
{{-- Once, in your layout. --}}
<x-shape::toaster />
```

```php
Shape::toast()->success('Invoice sent')->send();
```

You do not write `<x-shape::toast>` yourself. The toaster renders one per tone
into a `<template>`, and `shape.js` clones the one the payload asked for.

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

There is no `icon()`, deliberately. The tone resolves the glyph and there is no
way to send another one: the script clones markup, and it cannot resolve an SVG
it was not already given. The rule that falls out of it is the right one anyway —
the glyph and the colour say the same thing, so neither can be set without the
other. `<x-shape::toast icon="…">` still takes one, for anyone rendering a toast
directly.

## The toaster

| Prop | Default | Values |
| --- | --- | --- |
| `position` | `bottom-right` | `bottom-right`, `bottom-left`, `bottom-center`, `top-right`, `top-left`, `top-center` |

It is a `popover="manual"`, which puts it in the top layer, which is why a toast
appears **above an open modal** and why there is still no z-index anywhere in
this library. It is also why toasts need `shape.js` — a popover is
`display: none` until something opens it. That trade is set out in
[Feedback](../feedback.md#toasts-need-the-script).

Above it, but not clickable while it is open: a modal makes the rest of the
document inert. See
[what the top layer does not buy](../feedback.md#what-the-top-layer-does-not-buy).

The container is transparent to the pointer and the toasts inside it are not, so
there is no invisible column over the corner of the page swallowing clicks.

## Announcing

Two live regions, and the tone picks: `danger` is announced assertively,
everything else politely. One region cannot make that distinction.

## The timer

Five seconds, paused while the pointer is over the toast or focus is inside it —
so a toast someone is reading, or tabbing into to dismiss, does not vanish
mid-sentence. `->sticky()` for anything that has to be acted on.

## Sending one without PHP

The event is the API, so anything that can dispatch a window event can raise a
toast:

```js
dispatchEvent(new CustomEvent('shape:toast', {
    detail: { toast: { heading: 'Copied', color: 'success', duration: 2000 } },
}))
```

## Folding

`toast` is tier A — `@blaze(fold: true, safe: ['heading', 'description'])`, which
is what makes the template approach pay: the toaster renders it at compile time,
when a toast's text is never known.

`toaster` is tier D — a plain `@blaze`. It reads the session, and it is the one
component in the library that does so without cutting an `@unblaze` hole. There
is one on a page, so folding it would save nothing and cost a boundary that
variables cannot cross. Not every component that touches request state needs the
machinery; only the ones worth folding do.
