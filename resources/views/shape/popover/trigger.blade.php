@blaze(fold: true, safe: ['for'])

{{--
    Opens the popover or menu named in `for`.

    `popovertarget` is the platform's toggle: it opens and closes the popover,
    dismisses it on an outside click, closes it on Escape, and puts it in the top
    layer — all without a line of script. Dialogs are opened by a command
    instead, which is why `<x-shape::overlay.trigger>` is a separate component
    rather than this one with a flag.

    The anchor name goes on as an inline style because it has to carry the
    overlay's name, and a class containing an interpolated value is a class
    Tailwind never sees and therefore never generates. The caller's own `style`
    is concatenated rather than overwritten — the attribute bag's `merge` does
    that for elements, but this forwards into a component, where the last value
    for a key would simply win.
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
    style="anchor-name: --shape-{{ $for }};{{ $attributes->get('style') }}"
    {{ $attributes->except('style') }}
    data-shape-popover-trigger=""
>{{ $slot }}</x-shape::button>
