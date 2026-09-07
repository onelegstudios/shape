@blaze(fold: true)

{{--
    Resolves the alert's glyph, so that alert.blade.php doesn't have to repeat
    the chain once per place the glyph can sit. `icon-placement` gives it two —
    the gutter beside the message, and the head of the first line — and the four
    states resolve the same way in both.

    Never colour alone — every state tone resolves a glyph, so an alert stays
    readable in greyscale. `:icon="false"` opts out; forgetting isn't possible.

    `brand` and `accent` are not among them. Both are emphasis: the brand is the
    product's colour, which an application is free to move, and the accent is the
    one kept for "look here". A glyph on either would make it a state under
    another name — the one `info` now is, in a blue that stays blue whatever the
    brand becomes.

    A static tag per tone, as the badge does, rather than
    `<x-shape::icon :name=".." />` — the four built-in states never need
    `<x-dynamic-component>`'s temp-file round trip. A caller's own `icon` still
    goes through it; there's no fixed set of those to special-case.

    `variant` and `size` are the icon's own two props under their own names,
    because here there is nothing else they could modify. The alert prefixes
    them, the way it prefixes `bar-square`, only because `size` and `variant`
    already mean the alert's own on every other component in the library.

    `variant` defaults to null rather than to a style, so that an alert naming
    nothing leaves the choice with the icon — where the set's rule about which
    drawing a size prefers is baked in, solid at `xs` and `sm` and outline at
    `base`. A default named here would override that rule for every alert in
    order to serve the few that want the other drawing.

    Nothing is declared safe. Both of the icon's props pick a drawing rather than
    a value, `tone` picks which glyph, and `icon` picks whether any of that
    happens at all — so the fold gives up here for the same reasons it gives up
    on the icon itself.
--}}

@props([
    'tone' => null,
    'icon' => null,
    'variant' => null,
    'size' => 'sm',
])

@if ($icon !== false)
    @if ($icon)
        <x-shape::icon :name="$icon" :variant="$variant" :size="$size" {{ $attributes }} />
    @elseif ($tone === 'success')
        <x-shape::icon.shape-success :variant="$variant" :size="$size" {{ $attributes }} />
    @elseif ($tone === 'danger')
        <x-shape::icon.shape-danger :variant="$variant" :size="$size" {{ $attributes }} />
    @elseif ($tone === 'warning')
        <x-shape::icon.shape-warning :variant="$variant" :size="$size" {{ $attributes }} />
    @elseif ($tone === 'info')
        <x-shape::icon.shape-info :variant="$variant" :size="$size" {{ $attributes }} />
    @endif
@endif
