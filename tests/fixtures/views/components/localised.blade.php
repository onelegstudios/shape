@blaze(fold: true)

{{--
    A fixture, not a component. It exists so the folding suite can demonstrate
    what happens when a folded component resolves a translation: the string is
    baked in at compile time and the locale stops mattering.
--}}

@props([])

<span data-probe>{{ __('probe.hello') }}</span>
