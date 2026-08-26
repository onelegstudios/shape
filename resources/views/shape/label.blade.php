@blaze(fold: true)

{{--
    The name of a control, or of a group of them.

    `for` is resolved rather than required: an explicit `for` wins, otherwise the
    label takes the name off the field it sits in. So the common case states
    nothing, and the uncommon one is still expressible.

    `as="legend"` is the group form, used inside `<x-shape::field as="fieldset">`.
    A `<legend>` has no `for` — it names its fieldset by being inside it — which
    is a real branch, not an interpolated tag name. So unlike `heading`'s `level`
    and `text`'s `as`, this one cannot be declared safe.
--}}

@props([
    'for' => null,
    'as' => 'label',
])

@aware([
    'fieldName' => null,
])

@php
$classes = Shape::classes()
    ->add('[:where(&)]:text-sm [:where(&)]:font-medium')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]');

$target = $for ?? $fieldName;
@endphp

<?php switch ($as): case ('legend'): ?>
{{-- A legend names the fieldset it is inside, so it takes no `for`. --}}
<legend {{ $attributes->class($classes) }} data-shape-label>{{ $slot }}</legend>
<?php break; default: ?>
<label @if ($target) for="{{ $target }}" @endif {{ $attributes->class($classes) }} data-shape-label>{{ $slot }}</label>
<?php endswitch; ?>
