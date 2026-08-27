@blaze(fold: true, safe: ['for'])

{{--
    Opens the popover or menu named in `for`.

    `popovertarget` is the platform's toggle: it opens and closes the popover,
    dismisses it on an outside click, closes it on Escape, and puts it in the top
    layer — all without a line of script. Dialogs are opened by a command
    instead, which is why `<x-shape::overlay.trigger>` is a separate component
    rather than this one with a flag.

    The overlay finds this button by its own id — `[popovertarget="…"]` — when
    shape.js measures where to put it. There is no anchor name to carry: CSS
    anchor positioning turned out to ship in halves, so placement is JavaScript's
    everywhere, and this element only has to be findable.
--}}

@props([
    'for',
    'haspopup' => 'dialog',
])

<x-shape::button
    type="button"
    popovertarget="{{ $for }}"
    aria-haspopup="{{ $haspopup }}"
    aria-expanded="false"
    aria-controls="{{ $for }}"
    {{ $attributes }}
    data-shape-popover-trigger=""
>{{ $slot }}</x-shape::button>
