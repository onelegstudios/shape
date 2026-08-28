@blaze(fold: true, memo: true, safe: ['initials', 'alt'])

{{--
    A person, at one of four sizes, as a picture or as their initials.

    Initials are stated, never derived. Deriving them from a name inside a folded
    component would run the derivation once, at compile time, and bake one
    person's initials into every avatar the template renders — the same failure
    as a translation, arriving from a different direction.

    They are also not the accessible name. Initials are a picture of a name, so
    they are hidden and the name itself is carried in text only a screen reader
    reaches. An avatar sitting next to a name that is already on the page passes
    no `alt` at all and announces nothing, which is right.

    `src` is the one prop here that cannot be safe, and no attribute-bag trick
    gets around it. A null attribute can be dropped by `merge()` without anyone
    asking whether it is null — the progress bar's `aria-label` is exactly that —
    but this is not one attribute either way: an `<img>` with no source is a
    broken image request, and a `<span>` cannot show a photograph. `src` decides
    which element renders, so it branches.

    The consequence is worth stating rather than discovering: an avatar list
    built from per-row URLs neither folds nor usefully memoizes, because every
    memo key is unique. It is still annotated `memo: true`, because the initials
    form is common and does fold; the docs say plainly which call site pays.
--}}

@props([
    'src' => null,
    'initials' => null,
    'alt' => null,
    'size' => 'base',
])

@php
$classes = Shape::classes()
    ->add('inline-flex shrink-0 items-center justify-center overflow-hidden')
    ->add('[:where(&)]:rounded-full [:where(&)]:bg-shape-100 dark:[:where(&)]:bg-shape-800')
    ->add('[:where(&)]:font-medium [:where(&)]:text-[color:var(--shape-fg-muted)]')

    ->add(match ($size) {
        'xs' => '[:where(&)]:size-6 [:where(&)]:text-2xs',
        'sm' => '[:where(&)]:size-8 [:where(&)]:text-xs',
        'lg' => '[:where(&)]:size-12 [:where(&)]:text-base',
        default => '[:where(&)]:size-10 [:where(&)]:text-sm',
    });
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->class($classes) }} data-shape-avatar data-shape-size="{{ $size }}">
@else
    <span {{ $attributes->class($classes) }} data-shape-avatar data-shape-size="{{ $size }}"><span aria-hidden="true">{{ $initials }}</span><span class="sr-only">{{ $alt }}</span></span>
@endif
