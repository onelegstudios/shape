@blaze(fold: true, memo: true, safe: ['initials', 'alt', 'tone'])

{{--
    A person, at one of four sizes, as a picture or as their initials.

    `variant` is how loud the circle is and `tone` is what it means, read from
    the same variables the badge and the button read — so an avatar and a badge
    given the same tone agree without either knowing about the other.

    The badge's three arms and not the alert's four. `ghost` is the arm that
    paints nothing until it is pointed at, and an avatar that paints nothing is
    two letters loose in a line of text: there is no circle left to be quiet,
    and nothing here is pointed at anyway.

    `subtle` is the default and is the avatar this component has always drawn —
    the neutral tone's tint is the same `shape-100` the fill used to name
    directly. What moves at that default is the ink. It was `--shape-fg-muted`,
    which any ancestor surface republishes, so initials inside a solid alert
    took that alert's white onto their own pale circle. The tone's own ink is a
    colour the circle owns.

    `outline` takes `--shape-tone-border-strong` rather than the neutral
    `--shape-tone-border` the outline badge and button take. Those two are
    chrome — a control is a control whatever it means — and their edge draws the
    control rather than the meaning. An avatar is a person, and with no fill
    under it the ring is the whole of the paint: the neutral edge would leave a
    coloured pair of initials in a grey circle, which reads as `subtle` half
    applied rather than as an arm of its own.

    Every arm paints onto both elements, which is one branch this component does
    not add. Under an opaque photograph the fill is simply not seen; under a
    transparent one it is the ground the face sits on, and in either case it is
    what fills the circle while the image is still arriving. The border rings
    the picture, which is the same edge doing the same job.

    Initials are stated, never derived. Deriving them from a name inside a folded
    component would run the derivation once, at compile time, and bake one
    person's initials into every avatar the template renders — the same failure
    as a translation, arriving from a different direction.

    They are also not the accessible name. Initials are a picture of a name, so
    they are hidden and the name itself is carried in text only a screen reader
    reaches. An avatar sitting next to a name that is already on the page passes
    no `alt` at all and announces nothing, which is right.

    `src` is the one prop here that cannot be safe, and no attribute-bag trick
    gets around it. A null attribute can be dropped by `merge()` without anyone
    asking whether it is null — the progress bar's `aria-label` is exactly that —
    but this is not one attribute either way: an `<img>` with no source is a
    broken image request, and a `<span>` cannot show a photograph. `src` decides
    which element renders, so it branches.

    `tone` does not. It is interpolated into an attribute and nothing more, the
    way the button carries it, so it is safe and a per-person tone still folds.
    `variant` resolves the paint above and is not, which is the badge's
    arrangement as well.

    The consequence is worth stating rather than discovering: an avatar list
    built from per-row URLs neither folds nor usefully memoizes, because every
    memo key is unique. It is still annotated `memo: true`, because the initials
    form is common and does fold; the docs say plainly which call site pays.
--}}

@props([
    'src' => null,
    'initials' => null,
    'alt' => null,
    'size' => 'base',
    'tone' => null,
    'variant' => 'subtle',
])

@php
$classes = Shape::classes()
    ->add('inline-flex shrink-0 items-center justify-center overflow-hidden')
    ->add('[:where(&)]:rounded-full [:where(&)]:font-medium')

    ->add(match ($size) {
        'xs' => '[:where(&)]:size-6 [:where(&)]:text-2xs',
        'sm' => '[:where(&)]:size-8 [:where(&)]:text-xs',
        'lg' => '[:where(&)]:size-12 [:where(&)]:text-base',
        default => '[:where(&)]:size-10 [:where(&)]:text-sm',
    })

    ->add(match ($variant) {
        'solid' => '[:where(&)]:bg-[var(--shape-tone)] [:where(&)]:text-[var(--shape-tone-fg)]',
        'outline' => '[:where(&)]:border [:where(&)]:border-[var(--shape-tone-border-strong)] [:where(&)]:text-[var(--shape-tone-ink)]',
        default => '[:where(&)]:bg-[var(--shape-tone-tint)] [:where(&)]:text-[var(--shape-tone-ink)]',
    });
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->class($classes) }} data-shape-avatar data-shape-size="{{ $size }}" data-shape-variant="{{ $variant }}" data-shape-tone="{{ $tone ?? 'neutral' }}">
@else
    <span {{ $attributes->class($classes) }} data-shape-avatar data-shape-size="{{ $size }}" data-shape-variant="{{ $variant }}" data-shape-tone="{{ $tone ?? 'neutral' }}"><span aria-hidden="true">{{ $initials }}</span><span class="sr-only">{{ $alt }}</span></span>
@endif
