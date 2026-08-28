@blaze(fold: true)

{{--
    Overlapping avatars, which is two utilities and no stylesheet rule: a
    negative gap to overlap them and a ring on each so the one underneath reads
    as a separate person rather than a smudge.

    They stack in DOM order, last on top. Choosing the other order is a z-index,
    and this library does not have one — everything that needs to paint above
    something else is in the top layer instead, which a group of avatars is not.
    Reverse the order at the call site if the first face should be the front one.
--}}

@props([])

<div
    {{ $attributes->class('flex items-center [:where(&)]:-space-x-2 [&>*]:ring-2 [&>*]:ring-white dark:[&>*]:ring-shape-900') }}
    data-shape-avatar-group
>{{ $slot }}</div>
