@blaze(fold: true)

{{--
    Overlapping avatars, which is two utilities and no stylesheet rule: a
    negative gap to overlap them and a ring on each so the one underneath reads
    as a separate person rather than a smudge.

    They stack in DOM order, last on top. Choosing the other order is a z-index,
    and this library does not have one — everything that needs to paint above
    something else is in the top layer instead, which a group of avatars is not.
    Reverse the order at the call site if the first face should be the front one.

    Which is also where a badge wants to be. A badged avatar's mark sits on its
    own corner and the next face paints over that corner, so the mark belongs on
    the last child or at `bottom-left`. Nothing here can fix that from the
    outside without the z-index the paragraph above declines to have.

    There is no `max` here, because there is nothing to count. A group renders a
    slot, and a slot has already been rendered by the time it arrives — the
    avatars past the limit are elements to hide rather than avatars to leave
    out, which is the same work done later and worse. The slice belongs where
    the collection is, and the remainder is an ordinary avatar: `+3` is initials
    the same way `AL` is, hidden the same way, with the sentence in `alt`. Last
    in the slot, so it paints on top, which is where a summary wants to be.

    The ring finds the avatar rather than the child. A badged avatar arrives
    wrapped in the shell its mark is positioned against, so `[&>*]` would ring
    that shell — a square ring around a circle, at the one call site that most
    wants the separation. `[data-shape-avatar]` is the element that is actually
    a face, whether it is a direct child or one shell down, and the overlap goes
    on being applied to the children because the shell is the thing that has to
    move.
--}}

@props([])

<div
    {{ $attributes->class('flex items-center [:where(&)]:-space-x-2 [&_[data-shape-avatar]]:ring-2 [&_[data-shape-avatar]]:ring-white dark:[&_[data-shape-avatar]]:ring-shape-900') }}
    data-shape-avatar-group
>{{ $slot }}</div>
