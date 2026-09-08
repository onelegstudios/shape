@blaze(fold: true)

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

    It is not the avatar's file either, though the four arms are identical. The
    registry ejects a component with everything it composes, and a badge that
    composed `avatar.element` would pull the avatar, its group and its element
    into an application that asked for a badge.

    `div` is here for the reason the other two list it: a badge inside something
    already clickable should not be a second control inside the first.
--}}

@props([
    'as' => null,
])

<?php switch ($as): case ('button'): ?>
<button type="button" {{ $attributes }}>{{ $slot }}</button>
<?php break; case ('a'): ?>
<a {{ $attributes }}>{{ $slot }}</a>
<?php break; case ('div'): ?>
<div {{ $attributes }}>{{ $slot }}</div>
<?php break; default: ?>
<span {{ $attributes }}>{{ $slot }}</span>
<?php endswitch; ?>
