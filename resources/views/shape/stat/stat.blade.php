@blaze(fold: true, memo: true, safe: ['value', 'label', 'description', 'delta'])

{{--
    A number and what it is a number of.

    "Labels are a last resort" — so the value takes the large treatment and the
    label is rendered de-emphasized beside it, and emphasizing the label is what
    a caller has to ask for. The label still comes first in the DOM, so the order
    read aloud is "Invoices sent, 1,204, up 12%" rather than a number with no
    subject.

    Prop-first and slotless, which is what lets it memoize at a call site written
    self-closing — the call site a row of stats is made of.

    The delta row is always rendered and collapsed with `empty:hidden` rather
    than written behind an `@if ($delta)`. That is the whole reason the memo tier
    is real here: a delta is dynamic by definition, and a prop that drives a
    condition cannot be safe, so the `@if` would have taken every stat with a
    computed delta off the fold path — which is all of them. Nothing is inside
    that paragraph but the glyph and the delta, and nothing is between them,
    because `:empty` is defeated by a single space.

    `trend` picks the glyph, so it is branched and unsafe. That is fine: a trend
    is one of three literals at nearly every call site. `tone` overrides what
    the trend resolves to, for the metrics where up is the bad direction.

    `size` and `emphasis` are two questions about the same three lines and they
    do not collide: `size` picks the pair of type steps a stat is set in, and
    `emphasis` decides which of the two the number gets. That is why every step
    below names a third, the size the two share when the label is the thing being
    emphasized — with the number and the word set alike, what separates them is
    weight and colour, and a stat whose label had been promoted to 24px would
    read as a heading someone forgot to write.
--}}

@props([
    'value' => null,
    'label' => null,
    'description' => null,
    'delta' => null,
    'trend' => null,
    'tone' => null,
    'size' => 'base',
    'emphasis' => 'value',
])

@php
// Never colour alone: a direction is drawn as well as tinted, and the drawing is
// resolved here rather than asked for at the call site so that forgetting is not
// one of the things a caller can do.
$glyph = match ($trend) {
    'up' => 'shape-trend-up',
    'down' => 'shape-trend-down',
    'flat' => 'shape-trend-flat',
    default => null,
};

$tone ??= match ($trend) {
    'up' => 'success',
    'down' => 'danger',
    default => 'neutral',
};

// Four steps and a gap at each size: the number, the word beside it, the size
// the two share when `emphasis` levels them, and the arrow in the delta row.
//
// The number moves further than the words do — four sizes across the scale
// against two — because that is what a stat is. Twelve pixels of label under a
// 36px figure is the same relationship as fourteen under twenty-four; a label
// that grew as fast as the number would flatten the block into two lines of
// large text with a gap in them.
[$number, $word, $level, $arrow, $gap] = match ($size) {
    'xs' => ['text-lg', 'text-xs', 'text-sm', 'xs', '[:where(&)]:gap-0.5'],
    'sm' => ['text-xl', 'text-xs', 'text-sm', 'xs', '[:where(&)]:gap-0.5'],
    'lg' => ['text-3xl', 'text-base', 'text-lg', 'sm', '[:where(&)]:gap-1.5'],
    'xl' => ['text-4xl', 'text-lg', 'text-xl', 'sm', '[:where(&)]:gap-2'],
    default => ['text-2xl', 'text-sm', 'text-base', 'xs', '[:where(&)]:gap-1'],
};

$valueClasses = match ($emphasis) {
    'label' => $level.' font-medium tabular-nums text-[color:var(--shape-fg-muted)]',
    default => $number.' font-semibold tracking-tight tabular-nums text-[color:var(--shape-fg)]',
};

$labelClasses = match ($emphasis) {
    'label' => $level.' font-semibold text-[color:var(--shape-fg)]',
    default => $word.' font-medium text-[color:var(--shape-fg-muted)]',
};
@endphp

<div {{ $attributes->class(Shape::classes('flex flex-col')->add($gap)) }} data-shape-stat data-shape-size="{{ $size }}" data-shape-emphasis="{{ $emphasis }}">
    <p class="{{ $labelClasses }}" data-shape-stat-label>{{ $label }}</p>

    <p class="{{ $valueClasses }}" data-shape-stat-value>{{ $value }}</p>

    <p class="inline-flex items-center gap-1 {{ $word }} font-medium text-[color:var(--shape-tone-ink)] empty:hidden" data-shape-stat-delta data-shape-tone="{{ $tone }}">@if ($glyph)<x-shape::icon :name="$glyph" :size="$arrow" />@endif{{ $delta }}</p>

    <p class="{{ $word }} text-[color:var(--shape-fg-muted)] empty:hidden" data-shape-stat-description>{{ $description }}</p>
</div>
