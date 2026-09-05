# Feedback

Alerts, toasts, the confirm dialog and the progress bar are one family, and what
they share is a direction: something the server knows has to reach a page that
has already been drawn. This page covers the channel that does it. The four
component pages cover the markup.

## Two calls

```php
Shape::toast()->success('Invoice sent')->send();
Shape::confirm('Delete project?')->then('deleteProject')->send();
```

Put one of each component in your layout and nothing else is wired up:

```blade
<x-shape::toaster />
<x-shape::confirm />
```

## There is no Livewire component here

The plan for this library called the toaster its first Livewire component. It
isn't one, and the reason is worth stating rather than discovering.

A toast is transient client state. Holding it on the server means a round trip to
render a list the browser already knows the contents of, and it makes Livewire a
hard requirement for the one component every layout includes. Shape has no
Livewire in its `composer.json` and `shape.js` imports nothing — that promise is
worth more than the convenience.

So `send()` picks a transport instead:

| When | How it travels |
| --- | --- |
| During a Livewire request | Livewire's own dispatcher, as a browser event |
| Anywhere else — a controller, a redirect, a middleware | Flashed to the session, rendered by the toaster on the next page, replayed by `shape.js` |

Both arrive as the **same browser event**. There is one code path building a
toast, not two that can disagree, which is the lesson the overlays paid for. If
you want to send one by hand — from Alpine, from your own script — that path is
open too:

```js
window.dispatchEvent(new CustomEvent('shape:toast', {
    detail: { toast: { heading: 'Saved', tone: 'success' } },
}))
```

Livewire is reached through the container binding rather than the class, so
nothing here breaks in an application that has never installed it.

### `send()` right before `redirect(navigate: true)`

That is the common shape — save, toast, redirect — and it needs one more
piece: Livewire's frontend processes a `navigate: true` redirect *before* a
dispatched event, regardless of which order your PHP called them in, so the
event would otherwise fire on a page already being swapped out from under
it. `RescueFeedbackFromNavigate`, a middleware `ShapeServiceProvider`
registers on the `web` group whenever Livewire is installed, catches this
after Livewire has built the response but before it reaches the browser: it
moves a Shape event riding a `navigate: true` redirect into the session
instead, exactly where it would have gone for a plain (non-Livewire)
redirect. The next page's toaster renders it from there.

## `then()` names an event, not a method

```php
Shape::confirm('Delete project?')
    ->heading('Delete project?')
    ->accept('Delete')
    ->tone('danger')
    ->then('deleteProject', [$project->id])
    ->send();
```

When someone accepts, `shape.js` dispatches a window event by that name and does
nothing else. It never mentions Livewire. A Livewire component hears it with the
listener it already has, because `#[On]` listens for window events:

```php
#[On('deleteProject')]
public function deleteProject(int $id): void
{
    // …
}
```

Alpine hears it with `x-on:deleteProject.window`, and a plain listener hears it
with `addEventListener`. The event goes out *before* the dialog closes: closing
is what returns focus and tears the dialog down, and none of that should be able
to decide whether the thing someone confirmed actually happened.

## Toasts need the script

This is the one place in the library where that is true, and it is a deliberate
trade rather than an oversight.

The toaster is a `popover="manual"`, which puts it in the **top layer** — above
every stacking context on the page, including an open `<dialog>`. That is what
keeps the library's "no z-index anywhere" promise intact, and it is the only way
a toast fired while a modal is open is not painted behind the modal's own
backdrop. A fixed-position `<div>` with a z-index cannot win that fight, because
the dialog is not in the stack to be out-numbered.

The top layer has one ordering rule of its own, and it is worth knowing: elements
stack in the order they were **promoted into it**, not by any property you can
set. So a modal opened over a toast that is already showing paints on top of it —
correctly, by the spec, and invisibly from the DOM. `shape.js` answers that by
re-promoting the toaster whenever a dialog is open, which is one line and is the
only way to change the order. It was found by screenshot; nothing about it shows
up in markup, and no assertion in the test suite reaches it.

The cost is that a popover is `display: none` until something opens it, and only
`shape.js` opens this one. A page without the script shows no toasts — including
ones the server flashed. Alerts, confirm and progress all render and work
without it; the confirm dialog just has to be opened by a trigger rather than by
a payload.

### What the top layer does not buy

A modal dialog makes the **rest of the document inert**, and a toast is outside
the dialog. So while a modal is open a toast is visible above it and cannot be
clicked or focused — its dismiss button included. That is the platform's rule
about modality rather than a stacking problem, and no implementation of a toast
escapes it. It resolves itself when the dialog closes, and toasts that time out
still time out. If a message has to be acted on while a modal is open, it belongs
inside the modal.

```js
import shape from '../../vendor/onelegstudios/shape/resources/js/shape.js'

shape()   // or, if you use Alpine: Alpine.plugin(shape)
```

## The markup lives in Blade, even though the script builds it

The toaster renders one `<x-shape::toast>` per tone into a `<template>`, and
`shape.js` clones the one the payload asked for and fills in two pieces of text.

The obvious alternative — a string of HTML inside the script — puts half of the
design system in a file Tailwind does not scan and a designer does not open. This
way the toast's markup is a component like every other one, it folds like every
other one, and a consumer who ejects it gets Blade rather than a template
literal.

One template per tone rather than one generic template, because the glyph is an
SVG the script has no way to resolve. It costs six copies of the toast markup in
every page's layout, which gzip flattens to almost nothing, and it means the
"never rely on colour alone" rule holds for toasts without the script knowing
anything about icons.

## Two live regions

A toast is inserted after the page has been read, so it has to be announced. A
failure and a confirmation should not be announced the same way, and one live
region cannot make that distinction — so the toaster carries two, and `shape.js`
picks by tone:

| Tone | Region |
| --- | --- |
| `danger` | `aria-live="assertive"` — interrupts |
| everything else | `aria-live="polite"` — waits its turn |

An alert is not a live region at all. It was on the page when it loaded, and
announcing it repeats what a screen reader is about to read anyway.

## The timer pauses while someone is reading

Toasts dismiss themselves after five seconds, and the countdown stops while the
pointer is over one or focus is inside it. `->duration(1200)` changes it;
`->sticky()` means until dismissed, which is the right answer for anything a
person has to act on.

## Where each part goes

| | Alert | Toast |
| --- | --- | --- |
| Lives | in the page | over it |
| Survives being read twice | yes | no |
| Announced | no | yes |
| Sent from | the template | the server, or a script |

If a message is still true after someone has read it, it is an alert.

## What is not covered by tests

Render tests assert the markup, the wiring attributes and the ARIA. The channel's
two transports are asserted, including the fallback, and so is
`RescueFeedbackFromNavigate`'s rewrite of a Livewire response — but only
against a payload shaped like Livewire's, not a real `wire:navigate` swap.
What no test in this package covers yet is behaviour that needs a real
browser: whether a toast is actually announced, whether the timer pauses on
hover, whether the toast is painted above an open modal, whether focus
returns to the trigger after a confirmation, and whether a toast actually
survives a real `wire:navigate` round trip end to end.

The paint-order one is not hypothetical — it was a real bug, found by taking a
screenshot and reading the pixel where the toast was, because the DOM says the
same thing whether the toast is above the backdrop or under it. A test suite that
cannot see is a test suite that would have passed.

They are verified by hand in the workbench preview (`composer serve`). Browser
tests are the honest fix and they are not written yet — this note is here so the
gap is recorded rather than assumed away.
