@blaze(fold: true, safe: ['name', 'heading', 'message', 'accept'])

{{--
    The dialog `Shape::confirm()` fills in.

    One of these in the layout answers every confirmation in the application. It
    owns no `<dialog>` of its own — it is the modal, with a message and two
    buttons, and every prop here is a placeholder that shape.js overwrites from
    the payload before it opens.

    `name` has a literal default, which is the exception to the rule stated on
    every other overlay. That rule is about *generated* ids: `uniqid()` inside a
    folded component runs once at compile time and bakes one value into every
    instance. A literal is already one value, so baking it is the correct result
    rather than a bug. Two confirm dialogs on a page need two names, and then it
    is `name` on the component and `->name()` on the builder.

    `cancel` is not declared safe, and the reason is worth stating because it is
    the composition case. Safety is a claim about what a component does with a
    value — this one only interpolates it, but it hands it to `overlay.close`,
    which branches on `label` to choose between an icon button and a text one.
    A dynamic `:cancel` therefore has to reach a runtime decision, and claiming
    otherwise here would fold a branch that hasn't been taken yet.

    Defaults are literal English, not `__()`, for the reason `overlay.close`
    records: a translation inside a folded component resolves once, at compile
    time. Translate at the call site, or send the strings with the payload —
    `->accept(__('Delete'))` is evaluated per request on the server.
--}}

@props([
    'name' => 'shape-confirm',
    'heading' => 'Are you sure?',
    'message' => null,
    'accept' => 'Confirm',
    'cancel' => 'Cancel',
])

<x-shape::modal :name="$name" :heading="$heading" size="sm" data-shape-confirm="">
    <x-shape::text size="sm" variant="muted" class="empty:hidden" data-shape-confirm-message="">{{ $message }}</x-shape::text>

    <x-shape::overlay.footer>
        <x-shape::overlay.close :for="$name" :label="$cancel" />

        {{-- Not a close button. It has to dispatch before the dialog goes away,
             so shape.js closes it after the event has left. --}}
        <x-shape::button variant="primary" data-shape-confirm-accept="">{{ $accept }}</x-shape::button>
    </x-shape::overlay.footer>
</x-shape::modal>
