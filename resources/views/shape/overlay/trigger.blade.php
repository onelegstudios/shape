@blaze(fold: true, safe: ['for'])

{{--
    Opens the dialog named in `for` — a modal or a drawer, which are the same
    element with different placement.

    `command` and `commandfor` are the platform's own invoker attributes: the
    button opens the dialog with no script, no event handler and no state of its
    own. Where they aren't supported yet, shape.js delegates a single click
    listener for the whole document and calls `showModal()` itself — a fallback
    of about twenty lines, not a component that behaves differently.

    Menus and popovers have a trigger of their own, because they are opened by a
    different platform mechanism: `popovertarget` rather than a command. Two
    triggers, because there are genuinely two mechanisms.

    It renders a `button`, so everything the button takes works here: variant,
    tone, size, icons.
--}}

@props([
    'for',
    'command' => 'show-modal',
])

<x-shape::button
    type="button"
    command="{{ $command }}"
    commandfor="{{ $for }}"
    {{ $attributes }}
    data-shape-overlay-trigger=""
>{{ $slot }}</x-shape::button>
