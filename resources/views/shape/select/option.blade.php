@blaze(fold: true, memo: true, safe: ['label', 'value'])

{{--
    A styled option, for the callers who want one. Plain `<option>` children work
    just as well and cost nothing — this exists so that an option can be written
    the same way as everything else in the library, and so the styling has one
    home if a platform ever needs it.

    Slotless and self-closing, which is what makes it memoizable. Both props are
    interpolated and nothing more, so a list of options built from a collection
    still folds.
--}}

@props([
    'label' => null,
    'value' => null,
])

<option value="{{ $value }}" {{ $attributes }} data-shape-option>{{ $label }}</option>
