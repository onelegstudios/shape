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

    `size` is mostly room. An empty state is a screen's worth of nothing with a
    sentence in the middle of it, so what changes between the steps is how much
    of the screen it takes — and the mark, the headline and the sentence move
    with it, because a 24px glyph over 20 pixels of padding reads as a mark that
    outgrew its box.

    The table and the list hand theirs down, so an `xs` table's empty state is
    not a full page of white space under four tight rows.

    `icon-size` follows `size` unless it is named, which is the alert's rule and
    the toast's. The two scales are not the same length — the glyph runs out at
    `xl` while the padding could go on — so the steps are stated here rather than
    derived, and the headline flattens at the top for the same reason: `xl` is
    the largest heading there is.
--}}

@props([
    'size' => 'base',
    'icon' => null,
    'iconSize' => null,
    'heading' => null,
    'description' => null,
])

@php
$inset = match ($size) {
    'xs' => '[:where(&)]:gap-1 [:where(&)]:px-4 [:where(&)]:py-6',
    'sm' => '[:where(&)]:gap-1.5 [:where(&)]:px-5 [:where(&)]:py-8',
    'lg' => '[:where(&)]:gap-3 [:where(&)]:px-8 [:where(&)]:py-16',
    'xl' => '[:where(&)]:gap-4 [:where(&)]:px-10 [:where(&)]:py-20',
    default => '[:where(&)]:gap-2 [:where(&)]:px-6 [:where(&)]:py-12',
};

// The glyph's own step, the disc it sits in, the headline, the sentence under
// it, and the row of actions at the foot.
[$glyph, $disc, $headingSize, $textSize, $actions] = match ($size) {
    'xs' => ['sm', 'mb-1 p-1.5', 'sm', 'xs', 'gap-2 pt-2'],
    'sm' => ['base', 'mb-1.5 p-2', 'base', 'sm', 'gap-2.5 pt-3'],
    'lg' => ['lg', 'mb-3 p-4', 'xl', 'base', 'gap-3 pt-5'],
    'xl' => ['xl', 'mb-4 p-5', 'xl', 'lg', 'gap-4 pt-6'],
    default => ['base', 'mb-2 p-3', 'lg', 'sm', 'gap-3 pt-4'],
};

$iconSize ??= $glyph;
@endphp

<div
    {{ $attributes->class(Shape::classes('flex flex-col items-center text-center')->add($inset)) }}
    data-shape-empty
    data-shape-size="{{ $size }}"
>
    @if ($icon)
        <span class="{{ $disc }} rounded-full bg-shape-100 text-[color:var(--shape-fg-muted)] dark:bg-shape-800">
            <x-shape::icon :name="$icon" :size="$iconSize" />
        </span>
    @endif

    @if ($heading)
        <x-shape::heading :level="3" :size="$headingSize">{{ $heading }}</x-shape::heading>
    @endif

    @if ($description)
        <x-shape::text variant="muted" :size="$textSize" class="max-w-sm">{{ $description }}</x-shape::text>
    @endif

    <div class="flex flex-wrap items-center justify-center {{ $actions }} empty:hidden">{{ $slot }}</div>
</div>
