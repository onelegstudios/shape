@blaze(fold: true)

{{--
    The line of supporting copy under a label.

    It carries an id derived from the field name so the control can point at it
    with `aria-describedby` — which is what makes this copy part of the control's
    accessible description rather than decoration a screen reader skips past.

    `variant="muted"` on the text reads the surface's own muted foreground rather
    than a global grey, so a field inside a tinted card stays legible.
--}}

@props([
    'for' => null,
])

@aware([
    'fieldName' => null,
])

@php
$target = $for ?? $fieldName;
@endphp

<p
    @if ($target) id="{{ $target }}-description" @endif
    {{ $attributes->class('[:where(&)]:text-sm [:where(&)]:leading-6 [:where(&)]:text-[color:var(--shape-fg-muted)]') }}
    data-shape-description
>{{ $slot }}</p>
