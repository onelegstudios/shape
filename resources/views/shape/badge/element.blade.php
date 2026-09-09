@blaze(fold: true, safe: ['type'])

{{--
    Renders the element the badge should actually be, so that badge.blade.php
    holds one copy of what goes inside the badge rather than one copy per tag
    name.

    It is not `button.element`, for the reason the avatar keeps one of its own:
    that file's default arm is a `<button>`, because the button, the tab and the
    menu item are controls before they are anything else and the only question
    is which control. A badge's default is a `<span>` — it is a piece of state
    attached to something else until a call site asks for something that can be
    pressed — so borrowing the button's file would have made every badge that
    passed no `as` a control, which is the one arm this component must not have.

    It is not the avatar's file either, though the four arms are all but the
    same. The registry ejects a component with everything it composes, and a
    badge that composed `avatar.element` would pull the avatar, its group and
    its element into an application that asked for a badge.

    `div` is here for the reason the other two list it: a badge inside something
    already clickable should not be a second control inside the first.

    `type` is a prop rather than a hardcoded attribute, the same as it is in
    `button.element`. Written on the tag it would be printed twice for a call
    site that passed one of its own — and a browser reads the first of a pair of
    duplicates, so `type="submit"` on a chip in a filter form would have been
    dropped without a word. A prop is the one arrangement that lets the default
    stand and the caller win. It is interpolated and never branched on, so it is
    safe.
--}}

@props([
    'as' => null,
    'type' => 'button',
])

<?php switch ($as): case ('button'): ?>
<button type="{{ $type }}" {{ $attributes }}>{{ $slot }}</button>
<?php break; case ('a'): ?>
<a {{ $attributes }}>{{ $slot }}</a>
<?php break; case ('div'): ?>
<div {{ $attributes }}>{{ $slot }}</div>
<?php break; default: ?>
<span {{ $attributes }}>{{ $slot }}</span>
<?php endswitch; ?>
