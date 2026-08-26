@blaze(fold: true, safe: ['for'])

{{--
    The popover trigger, announcing a menu rather than a dialog.

    One prop's difference, and the reason it is a file rather than something a
    caller remembers to pass: `aria-haspopup="menu"` is what tells a screen
    reader that arrow keys will do something here. A default that has to be
    typed at every call site is a default that is missing at some of them.
--}}

@props([
    'for',
])

<x-shape::popover.trigger :for="$for" haspopup="menu" {{ $attributes }}>{{ $slot }}</x-shape::popover.trigger>
