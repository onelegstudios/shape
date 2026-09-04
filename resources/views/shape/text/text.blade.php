@blaze(fold: true, safe: ['as'])

{{--
    Body copy.

    `variant` is emphasis, not colour: `muted` reads the surface's own muted
    foreground rather than a global grey, which is what stops de-emphasized text
    turning to mud the moment it lands on a tinted surface.

    Like `level` on the heading, `as` is only interpolated into the tag name, so
    it stays safe and folds when bound dynamically.
--}}

@props([
    'size' => 'base',
    'variant' => 'base',
    'as' => 'p',
])

@php
$classes = Shape::classes()
    ->add(match ($size) {
        'xs' => '[:where(&)]:text-xs [:where(&)]:leading-5',
        'sm' => '[:where(&)]:text-sm [:where(&)]:leading-6',
        'lg' => '[:where(&)]:text-lg [:where(&)]:leading-7',
        default => '[:where(&)]:text-base [:where(&)]:leading-7',
    })

    // Both foregrounds come from the surface contract, so a paragraph inside a
    // tinted card gets that surface's muted colour without being told about it.
    ->add(match ($variant) {
        'muted' => '[:where(&)]:text-[color:var(--shape-fg-muted)]',
        'strong' => '[:where(&)]:text-[color:var(--shape-fg)] [:where(&)]:font-medium',
        default => '[:where(&)]:text-[color:var(--shape-fg)]',
    });
@endphp

<{{ $as }} {{ $attributes->class($classes) }} data-shape-text data-shape-variant="{{ $variant }}">{{ $slot }}</{{ $as }}>
