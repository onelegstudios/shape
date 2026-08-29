@blaze(fold: true)

{{--
    The screen someone sees first and most often — before there is any data to
    look at, and again every time they filter it all away. It ships as a real
    component because leaving it to the application is how applications end up
    shipping a blank div.

    Heading and description are props rather than slots, deliberately. A `@if`
    on a prop is answered when the template compiles; a `@if` on a slot is a
    runtime question, and asking it would take this component off the fold path.

    The slot that remains is for actions, and it is always rendered — `empty:hidden`
    collapses the row when nothing was passed, without anyone having to inspect
    the slot to find out. (CSS `:empty` is defeated by whitespace, so a slot
    holding only a newline still reserves its gap. Harmless, and worth knowing.)
--}}

@props([
    'icon' => null,
    'iconSize' => 'base',
    'heading' => null,
    'description' => null,
])

<div
    {{ $attributes->class('flex flex-col items-center text-center [:where(&)]:gap-2 [:where(&)]:px-6 [:where(&)]:py-12') }}
    data-shape-empty
>
    @if ($icon)
        <span class="mb-2 rounded-full bg-shape-100 p-3 text-[color:var(--shape-fg-muted)] dark:bg-shape-800">
            <x-shape::icon :name="$icon" :size="$iconSize" />
        </span>
    @endif

    @if ($heading)
        <x-shape::heading :level="3" size="lg">{{ $heading }}</x-shape::heading>
    @endif

    @if ($description)
        <x-shape::text variant="muted" size="sm" class="max-w-sm">{{ $description }}</x-shape::text>
    @endif

    <div class="flex flex-wrap items-center justify-center gap-3 pt-4 empty:hidden">{{ $slot }}</div>
</div>
