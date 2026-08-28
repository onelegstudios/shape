@blaze(fold: true, safe: ['name'])

{{--
    The region a tab controls, found by id rather than by position — so a panel
    can sit anywhere in the document and does not have to be a sibling of the
    strip that switches it. `name` here, `for` on the tab: the same convention
    the overlays use, and for the same reason.

    `hidden` is the mechanism, and it is deliberately not a class. The script
    toggles the attribute, so a panel carrying its own `display` utility would
    outrank it and never hide — that is the one thing to know before styling one.

    Without the script every panel is visible and every tab still focusable,
    which is a page that reads long rather than a page that is broken.
--}}

@props([
    'name' => null,
    'selected' => false,
])

<div
    {{ $attributes->class('[:where(&)]:pt-4') }}
    id="{{ $name }}"
    role="tabpanel"
    aria-labelledby="{{ $name }}-tab"
    tabindex="0"
    @if (! $selected) hidden @endif
    data-shape-tab-panel
>{{ $slot }}</div>
