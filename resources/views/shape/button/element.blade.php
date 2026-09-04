@blaze(fold: true, safe: ['type'])

{{--
    Renders the element the button should actually be, so that button.blade.php
    doesn't have to repeat its content once per tag name.
--}}

@props([
    'as' => null,
    'type' => 'button',
])

<?php switch ($as): case ('a'): ?>
<a {{ $attributes }}>{{ $slot }}</a>
<?php break; case ('div'): ?>
<div {{ $attributes }}>{{ $slot }}</div>
<?php break; default: ?>
<button type="{{ $type }}" {{ $attributes }}>{{ $slot }}</button>
<?php endswitch; ?>
