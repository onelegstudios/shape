@blaze(memo: false)

{{--
    Resolves an icon by name at runtime.

    A missing name renders nothing. That guard is load-bearing: without it the
    component resolves to `shape::icon.`, which Blade resolves straight back to
    this file, and the request recurses until it runs out of memory. A name that
    doesn't exist still raises Blade's usual "unable to locate component" error.

    `variant` defaults to null rather than to a style, so that a call site naming
    only a size leaves the choice to the icon — where the set's own rule about
    which style a size prefers is baked in. Naming a style here overrides it.

    Not memoized — this resolves a different component per call, which is the
    opposite of what memoization is for.

    On hot paths reach for `<x-shape::icon.check />` instead. That form folds and
    memoizes; this one can do neither, because the component it renders isn't
    known until the name is.
--}}

@props([
    'name' => null,
    'variant' => null,
    'size' => 'base',
])

@if (filled($name))
    <x-dynamic-component :component="'shape::icon.'.$name" :variant="$variant" :size="$size" {{ $attributes }} />
@endif
