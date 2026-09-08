@blaze(fold: true)

{{--
    Renders the element the avatar should actually be, so that avatar.blade.php
    holds one copy of what goes inside the circle rather than one copy per tag
    name.

    It is not `button.element`, which does the same job for the button, the tab
    and the menu item. That file's default arm is a `<button>`, because those
    three are controls before they are anything else and the only question is
    which control. An avatar's default is a `<span>`: it is a picture of a
    person until a call site asks for something that can be pressed. Borrowing
    the button's file would have made every avatar that passed no `as` a button,
    which is the one arm this component must not have.

    The four arms are the same otherwise, `div` included, and for the same
    reason the button lists it — an avatar inside something already clickable
    should not be a second control inside the first.
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
