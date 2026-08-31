@blaze(fold: true, safe: ['for'])

{{--
    Closes the overlay named in `for`.

    Deliberately not a `<form method="dialog">`, which is the other zero-script
    way to close a dialog: a modal very often contains a form, and a form inside
    a form is invalid HTML that browsers resolve by dropping one of them. The
    invoker attributes have no such constraint, and the fallback in shape.js
    already exists for the trigger.

    With no `label` it is the icon button in a dialog's corner; with one it is an
    ordinary button, for the "Cancel" that belongs beside a confirm action. That
    is a prop and not a slot for the usual reason — a slot would have to be
    inspected, and inspecting a slot is a runtime question this library doesn't
    ask.

    The default `aria-label` is a plain English string rather than a translation
    call, which is not laziness. `__()` in a folded component resolves once, at
    compile time, and bakes whichever locale compiled the view into the template
    for every visitor. Translations belong at the call site, where they are still
    evaluated per request: `:label="__('Cancel')"`.
--}}

@props([
    'for',
    'label' => null,
])

@if ($label === null)
    <x-shape::button
        type="button"
        square
        variant="ghost"
        icon="shape-close"
        icon-size="sm"
        aria-label="Close"
        command="close"
        commandfor="{{ $for }}"
        {{ $attributes }}
        data-shape-overlay-close=""
    />
@else
    <x-shape::button
        type="button"
        command="close"
        commandfor="{{ $for }}"
        {{ $attributes }}
        data-shape-overlay-close=""
    >{{ $label }}</x-shape::button>
@endif
