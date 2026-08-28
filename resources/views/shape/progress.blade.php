@blaze(fold: true, safe: ['value', 'max', 'label'])

{{--
    A native `<progress>`, restyled.

    Choosing the platform element here is not only about semantics — it is what
    keeps the component foldable. The browser computes the bar's width from
    `value` and `max`, so this template never divides one by the other, never
    branches on either, and `:value="$percent"` still folds. A div with an inline
    `width: {{ $percent }}%` would fold too; a div with a `match` on the
    percentage would not, and that is the version most libraries end up with.

    The corollary: this component prints no number. Formatting "42%" is the call
    site's job, where the value is already in hand and folding is not at stake.

    `indeterminate` is its own prop rather than "no value given", which looks
    like a redundant flag and isn't. `@isset($value)` is a branch on `value`, and
    branching on the one prop that is always dynamic would take every real call
    site off the fold path to express a state most of them never use.

    `aria-label` goes through `merge()` rather than an `@if`, for the same
    reason: a null attribute is dropped by the bag without anything having to ask
    whether it is null. A progress bar with no accessible name is a bar that
    announces a number and nothing about what it measures, so pass `label`, or
    point `aria-labelledby` at the text next to it.
--}}

@props([
    'value' => 0,
    'max' => 100,
    'indeterminate' => false,
    'size' => 'base',
    'color' => null,
    'label' => null,
])

@php
$classes = Shape::classes()
    ->add('block w-full')
    ->add('[:where(&)]:rounded-full')

    ->add(match ($size) {
        'sm' => '[:where(&)]:h-1',
        'lg' => '[:where(&)]:h-3',
        default => '[:where(&)]:h-2',
    });
@endphp

<progress
    @unless ($indeterminate) value="{{ $value }}" @endunless
    max="{{ $max }}"
    {{ $attributes->class($classes)->merge(['aria-label' => $label]) }}
    data-shape-progress
    data-shape-size="{{ $size }}"
    data-shape-tone="{{ $color ?? 'accent' }}"
></progress>
