@blaze(fold: true, memo: true, safe: ['initials', 'alt', 'tone'])

{{--
    A person, at one of four sizes, as a picture, as their initials, or as a
    glyph standing in for both.

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

    `size` sets a width and a height, which on an `<img>` is an instruction to
    squash whatever arrives into that square. Photographs of people are mostly
    taller than they are wide, so the default answer is the wrong one almost
    every time: `object-cover` fills the circle and crops what does not fit,
    which is what the circle was always implying. It rides the same `:where()`
    wrapper as everything else here, so a call site that wants the whole frame
    letterboxed passes `object-contain` and wins.

    The span never gets it. There is nothing inside it to fit — the initials are
    text, and text in a flex centre is already where it should be.

    `square` moves the corners and nothing else. The circle stays the default,
    because a circle is how a person is drawn everywhere else on the page and an
    avatar that disagreed with the rest of them would be read as a different kind
    of thing. Which is the point of the prop: the squared form is for the call
    sites where it *is* a different kind of thing — a company, a repository, a
    product, an initial standing for something that was never a face.

    It takes `--radius-shape`, the same radius the button and the card take,
    rather than a radius proportional to `size`. A squared avatar's whole job is
    to sit next to squared things and agree with them, and a per-size radius
    would put an 8px corner beside a 3px one for no reason a call site could see.

    Everything else survives the change untouched. The picture is still cropped,
    because the box is still fixed. The group still rings each child, because a
    ring follows whatever radius it finds. There is no branch here beyond the one
    class.

    `icon` is the third thing this circle can hold, and it is for the rows that
    have neither a face nor a name: an invitation nobody has accepted yet, an
    account that has been deleted, a service acting on its own. It pairs with
    `square` more often than not, for the reason `square` is here at all.

    There is no default. An avatar with nothing to show goes on showing nothing,
    which is the answer this component has always given, and a default person
    would have had to ask whether the initials were there to be preferred — the
    one question the branch below exists to avoid. `shape-user` ships so that the
    documentation renders, and is not a slot: the person in an application's own
    avatars is one it generated.

    It resolves ahead of `initials` and behind `src`, and that order is settled
    by folding rather than by taste. Asking whether initials are present would
    make `initials` a prop this component branches on, and `initials` is `safe`
    — bound per row from an accessor, an avatar still folds. So the glyph takes
    the branch instead, the way `src` already does, and a call site that passes
    all three spends one unsafe prop rather than two.

    Its size is the circle's, resolved here rather than asked for. An avatar is
    a fixed box and the drawing inside it wants to be about half of it, which is
    one answer per size and no prop worth adding: 16px in the two small circles,
    20px at `base`, 24px at `lg`. The smallest circle is the one that misses —
    16px inside 24 is two thirds of it — and it misses because 16 is the
    smallest drawing an icon set has. Squeezing that drawing into 12px with a
    utility would throw away the optical work that made it a drawing of its own.

    `solid` at every size by default, against the icon's own rule that `base`
    prefers the stroked drawing. That rule is about what reads at a size; this is
    about four avatars agreeing, and a `lg` avatar wearing a stroked glyph above
    three wearing filled ones would make `size` change more than the size. The
    filled drawing is also the one that matches what it stands in for — initials
    are ink at `font-medium`, not an outline. A set that draws a single style
    ignores the word rather than rendering it, so naming it costs nothing there.

    A default and not a constant, which is `icon-variant`. Every other decision
    this component makes is a class a call site can beat with one of its own, and
    a style is the exception: it picks which of the set's drawings renders, so no
    class can reach it. Written into the tag it would have been the only setting
    here with no way round it. Named for the thing it modifies, the way the alert
    names its own, because `variant` already means the circle's.

    Initials are stated, never derived. Deriving them from a name inside a folded
    component would run the derivation once, at compile time, and bake one
    person's initials into every avatar the template renders — the same failure
    as a translation, arriving from a different direction.

    They are also not the accessible name. Initials are a picture of a name, so
    they are hidden and the name itself is carried in text only a screen reader
    reaches. An avatar sitting next to a name that is already on the page passes
    no `alt` at all and announces nothing, which is right.

    A glyph is the same picture drawn differently. It arrives `aria-hidden` from
    the icon component without this one saying so, and `alt` is carried in the
    same hidden span beside it.

    `src` cannot be safe, and no attribute-bag trick gets around it. A null
    attribute can be dropped by `merge()` without anyone asking whether it is
    null — the progress bar's `aria-label` is exactly that — but this is not one
    attribute either way: an `<img>` with no source is a broken image request,
    and a `<span>` cannot show a photograph. `src` decides which element renders,
    so it branches.

    `icon` branches for the same reason from the other side: it decides what goes
    inside the element rather than which element renders, and there is no
    arrangement of the two that leaves both it and `initials` safe.
    `icon-variant` rides with it — nothing here reads the word, but the icon it
    is handed to chooses a drawing with it.

    `tone` does not branch. It is interpolated into an attribute and nothing
    more, the way the button carries it, so it is safe and a per-person tone
    still folds. `variant` resolves the paint above and `square` the radius, so
    neither is safe — the badge's arrangement as well.

    The consequence is worth stating rather than discovering: an avatar list
    built from per-row URLs neither folds nor usefully memoizes, because every
    memo key is unique. It is still annotated `memo: true`, because the initials
    form is common and does fold; the docs say plainly which call site pays.
--}}

@props([
    'src' => null,
    'icon' => null,
    'iconVariant' => 'solid',
    'initials' => null,
    'alt' => null,
    'size' => 'base',
    'tone' => null,
    'variant' => 'subtle',
    'square' => false,
])

@php
$iconSize = match ($size) {
    'xs', 'sm' => 'xs',
    'lg' => 'base',
    default => 'sm',
};

$classes = Shape::classes()
    ->add('inline-flex shrink-0 items-center justify-center overflow-hidden')
    ->add($square ? '[:where(&)]:rounded-shape' : '[:where(&)]:rounded-full')
    ->add('[:where(&)]:font-medium')

    ->add(['[:where(&)]:object-cover' => (bool) $src])

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
    <span {{ $attributes->class($classes) }} data-shape-avatar data-shape-size="{{ $size }}" data-shape-variant="{{ $variant }}" data-shape-tone="{{ $tone ?? 'neutral' }}">@if ($icon)<x-shape::icon :name="$icon" :variant="$iconVariant" :size="$iconSize" />@else<span aria-hidden="true">{{ $initials }}</span>@endif<span class="sr-only">{{ $alt }}</span></span>
@endif
