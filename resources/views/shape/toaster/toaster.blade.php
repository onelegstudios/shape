@blaze

{{--
    Where toasts arrive. One of these in the layout; nothing per page.

    Tier D, and the only component in the library that reads request state
    without cutting a hole for it. `error.blade.php` needs `@unblaze` because the
    rest of it folds and the validation message must not be baked in. This one
    doesn't fold at all — there is one on a page, so folding it would save
    nothing and cost a boundary that variables can't cross. Not every component
    that touches the session needs the machinery; only the ones worth folding do.

    It is a `popover="manual"`, which is the whole reason the library still has
    no z-index in it. A popover is in the top layer, so a toast is above every
    stacking context including an open `<dialog>` — the case that decides it,
    since a toast that fires while a modal is open would otherwise appear behind
    the modal's own backdrop. `manual` because an auto popover light-dismisses,
    and a toast that vanishes when you click anything is not a toast.

    Three things live inside it:

    1. Two live regions. A failure is announced assertively and everything else
       politely, which is a distinction a single region cannot make. shape.js
       picks by tone.
    2. One `<template>` per tone, holding a real `<x-shape::toast>`. Per tone
       rather than one generic template because the glyph is an SVG the script
       has no way to resolve — this way the markup is Blade, it folds, and the
       script only ever clones and fills.
    3. Whatever the session is carrying, as JSON. shape.js replays each entry as
       the same browser event Livewire would have dispatched, so a toast that
       survived a redirect and one that arrived live are built by the same code.

    The consequence, stated plainly here and in the docs: toasts need shape.js.
    Everything else in this library renders without it.
--}}

@props([
    'position' => 'bottom-right',
])

@php
$feedback = Shape::flashedFeedback();

$classes = Shape::classes()
    ->add('pointer-events-none flex-col gap-3')
    ->add('[:where(&)]:w-[min(24rem,calc(100vw-2rem))]')
    ->add('[:where(&)]:border-0 [:where(&)]:bg-transparent [:where(&)]:p-0');
@endphp

<div
    popover="manual"
    {{ $attributes->class($classes) }}
    data-shape-toaster
    data-shape-position="{{ $position }}"
>
    <div class="flex flex-col gap-3" data-shape-toast-region="polite" aria-live="polite"></div>
    <div class="flex flex-col gap-3" data-shape-toast-region="assertive" aria-live="assertive"></div>

    <template data-shape-toast-template="neutral"><x-shape::toast /></template>
    <template data-shape-toast-template="accent"><x-shape::toast tone="accent" /></template>
    <template data-shape-toast-template="info"><x-shape::toast tone="info" /></template>
    <template data-shape-toast-template="success"><x-shape::toast tone="success" /></template>
    <template data-shape-toast-template="warning"><x-shape::toast tone="warning" /></template>
    <template data-shape-toast-template="danger"><x-shape::toast tone="danger" /></template>

    @if ($feedback)
        {{-- The flags matter. Without them a message containing `</script>`
             closes this element early, and the rest of it is parsed as markup. --}}
        <script type="application/json" data-shape-feedback>@json($feedback, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
    @endif
</div>
