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
--}}

@props([
    'value' => null,
    'label' => null,
    'description' => null,
    'delta' => null,
    'trend' => null,
    'tone' => null,
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

$valueClasses = match ($emphasis) {
    'label' => 'text-base font-medium tabular-nums text-[color:var(--shape-fg-muted)]',
    default => 'text-2xl font-semibold tracking-tight tabular-nums text-[color:var(--shape-fg)]',
};

$labelClasses = match ($emphasis) {
    'label' => 'text-base font-semibold text-[color:var(--shape-fg)]',
    default => 'text-sm font-medium text-[color:var(--shape-fg-muted)]',
};
@endphp

<div {{ $attributes->class('flex flex-col [:where(&)]:gap-1') }} data-shape-stat data-shape-emphasis="{{ $emphasis }}">
    <p class="{{ $labelClasses }}" data-shape-stat-label>{{ $label }}</p>

    <p class="{{ $valueClasses }}" data-shape-stat-value>{{ $value }}</p>

    <p class="inline-flex items-center gap-1 text-sm font-medium text-[color:var(--shape-tone-ink)] empty:hidden" data-shape-stat-delta data-shape-tone="{{ $tone }}">@if ($glyph)<x-shape::icon :name="$glyph" size="xs" />@endif{{ $delta }}</p>

    <p class="text-sm text-[color:var(--shape-fg-muted)] empty:hidden" data-shape-stat-description>{{ $description }}</p>
</div>
