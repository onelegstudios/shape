@blaze(fold: true)

{{--
    A table, in a box that can scroll, with an empty state beside it.

    Three things about that arrangement are deliberate.

    The attribute bag lands on the wrapper rather than on the `<table>`. Almost
    everything a caller wants to say about a table is really about its box — a
    `max-h-*`, a `wire:key`, a class — and the one thing that has to be said
    about the box is the height bound a sticky head needs to work at all.

    The empty state is a sibling of the `<table>`, not a row inside it. A `<div>`
    written inside a `<tbody>` is foster-parented back out of the table by the
    HTML parser before any stylesheet sees it, so the choice is between a full
    width `<td colspan>` — which needs a column count this component has no way
    to know — and a sibling. The wrapper the table already needs makes the
    sibling free.

    And the empty state is always rendered. Asking whether the table has rows
    would mean inspecting the slot, which is a runtime question that would take
    this component off the fold path; a `:has()` rule in the stylesheet answers
    it instead, and costs nothing.

    A table is content, not a surface: no border, no background, no elevation of
    its own. Compose it inside a card when it needs to sit on one.
--}}

@props([
    'empty' => true,
    'emptyIcon' => null,
    'emptyHeading' => 'Nothing here yet',
    'emptyDescription' => null,
])

<div {{ $attributes->class('[:where(&)]:overflow-x-auto') }} data-shape-table>
    <table class="w-full [:where(&)]:text-sm [:where(&)]:text-[color:var(--shape-fg)]">{{ $slot }}</table>

    @if ($empty)
        <div data-shape-table-empty>
            <x-shape::empty
                :icon="$emptyIcon"
                :heading="$emptyHeading"
                :description="$emptyDescription"
            />
        </div>
    @endif
</div>
