@blaze(memo: false)

{{--
    Resolves an icon by name at runtime.

    A missing name renders nothing. That guard is load-bearing: without it the
    component resolves to `shape::icon.`, which Blade resolves straight back to
    this file, and the request recurses until it runs out of memory. A name that
    doesn't exist still raises Blade's usual "unable to locate component" error.

    Not memoized — this resolves a different component per call, which is the
    opposite of what memoization is for.

    On hot paths reach for `<x-shape::icon.check />` instead. That form folds and
    memoizes; this one can do neither, because the component it renders isn't
    known until the name is.
--}}

@props([
    'name' => null,
    'variant' => 'outline',
])

@if (filled($name))
    <x-dynamic-component :component="'shape::icon.'.$name" :variant="$variant" {{ $attributes }} />
@endif
