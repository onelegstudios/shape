@blaze(fold: true, memo: true, safe: ['label', 'scope'])

{{--
    A column heading, prop-first and slot-capable at the same time.

    Blaze memoizes a component at the call sites where it is written
    self-closing, and that is decided per call site rather than per file — so
    `<x-shape::table.heading label="Amount" />` memoizes even though this file
    also renders a slot for the headings that need markup in them.

    `label` and `scope` are interpolated and never branched on, so both stay safe
    and a heading built from a column definition still folds. `align` picks
    between class strings, so it does not.
--}}

@props([
    'label' => null,
    'align' => 'start',
    'scope' => 'col',
])

@php
$classes = Shape::classes()
    ->add('[:where(&)]:px-3 [:where(&)]:py-2')
    ->add('[:where(&)]:text-2xs [:where(&)]:font-medium [:where(&)]:uppercase [:where(&)]:tracking-wide')
    ->add('[:where(&)]:text-[color:var(--shape-fg-muted)]')

    ->add(match ($align) {
        'center' => '[:where(&)]:text-center',
        'end' => '[:where(&)]:text-end',
        default => '[:where(&)]:text-start',
    });
@endphp

<th scope="{{ $scope }}" {{ $attributes->class($classes) }} data-shape-table-heading>{{ $label }}{{ $slot }}</th>
