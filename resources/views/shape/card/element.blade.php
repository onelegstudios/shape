@blaze(fold: true, safe: ['type'])

{{--
    Renders the element the card should actually be, so that card.blade.php
    holds one copy of the card rather than one per tag name.

    It is not `button.element`, for the reason the badge keeps one of its own:
    that file's default arm is a `<button>`, and a card is a surface until a
    call site asks for something that can be pressed. It is not the badge's
    either, whose default is a `<span>` — a card holds block content, and the
    registry would eject the badge into an application that asked for a card.

    `type` is a prop rather than a hardcoded attribute, the same as it is on
    the button and the badge, so a caller's `type="submit"` is not printed
    second and dropped.
--}}

@props([
    'as' => null,
    'type' => 'button',
])

<?php switch ($as): case ('a'): ?>
<a {{ $attributes }}>{{ $slot }}</a>
<?php break; case ('button'): ?>
<button type="{{ $type }}" {{ $attributes }}>{{ $slot }}</button>
<?php break; default: ?>
<div {{ $attributes }}>{{ $slot }}</div>
<?php endswitch; ?>
