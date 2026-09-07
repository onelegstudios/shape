@blaze(fold: true, safe: ['heading'])

{{--
    A message that stays on the page, in the flow of the content it belongs to.
    Other libraries call this a callout; it is the same component.

    The toast beside it is the same idea sent from the server and dismissed on a
    timer. The distinction worth keeping is where the message lives: an alert is
    part of the page and survives it being read twice; a toast is an event and
    does not.

    Tone rides on `data-shape-tone`, as everywhere else, and the surface follows
    the variant. `data-shape-surface` is the part that matters and is easy to
    miss: it republishes the foreground that belongs on whatever this variant
    just painted, so a nested `<x-shape::text variant="muted">` reads a
    dialled-back version of the tone rather than a global grey. Grey text on a
    coloured background is the one thing the surface contract exists to make
    impossible.

    Which is why `variant` and the surface are decided together. `subtle` puts
    the tone's ink on a light wash and publishes `tint`; `solid` fills with the
    tone and publishes `solid`, where the readable foreground is the tone's own
    `-fg` instead. Nothing is passed down in either case — the nested text
    finds it.

    `outline` and `ghost` paint no fill, so the ink is the one thing here a call
    site gets a say in, and `toned` is that say. Both default to the page's own
    ink: the tone is carried by the border and the glyph, and a paragraph of
    coloured body copy is the loudest thing on a page for no reason. `subtle`
    and `solid` ignore the prop — where there is a fill, the fill decides what
    is readable on it.

    `border` is the other say, and the only one all four arms answer. It is off
    by default — a fill is already a boundary — and on, it draws the tone's own
    edge rather than a grey one: `--shape-tone-border-strong` on `subtle` and
    `outline`, the step past the fill on `solid`, and on `ghost` that same edge
    arriving with the tint it paints. On `outline` it adds nothing, because that arm
    has a border already; it only decides whether that border carries the tone
    or the neutral grey it carries by default.

    `shadow` is the third of them, and the only one that says nothing about
    the tone: it lifts the block off the page rather than colouring it. Off by
    default, because an alert belongs in the flow of the content it is about, and
    on it takes the raised step every other resting thing in the library takes.
    Ghost waits for the hover with it, for the same reason it waits with the
    border — a cast shadow around a block that paints nothing is an edge nothing
    drew.

    `bar` is the fourth, and the one that comes from next door. A toast is drawn
    with a thick rule down its left side, and this is that rule with a side to
    choose. It takes `--shape-tone` at full strength rather than the border's
    step back, because it is a mark rather than a boundary, and `solid` is the
    one arm that cannot have it — the fill is already that colour, so there it
    takes the step past the fill, exactly as `border` does. Ghost draws it at
    rest, alone among these: what the border and the shadow wait for is a
    surface to belong to, and a rule down one side belongs to nothing.

    `bar-square` is the bar's own corner and says nothing without one. A radius
    bends the last pixels of a 4px rule around the block, which reads as a
    stripe wrapped round it rather than a cut down its side; squaring the two
    corners the bar runs between straightens it and leaves the other two alone.
    It is named for the prop it modifies, the way `icon-size` is: bare `square`
    is the button's word for an equal-sided control, and one word cannot mean
    two things across the library.

    Which leaves the ghost hover, where a fill arrives after the fact.
    `data-shape-surface-hover` is the pair `data-shape-surface` publishes,
    published for as long as the pointer is on the block — because a tint
    without the ink that belongs on it is the failure the contract exists to
    prevent, arriving a hundred milliseconds late.

    `tone` is branched on to resolve the glyph, exactly as the badge does, so it
    is *not* declared safe here — and neither are `variant`, `toned`, `border`,
    `shadow`, `bar` or `bar-square`, which branch to resolve the paint. The same
    `tone` is safe on the button, which only ever interpolates it. Whatever a
    component does with a value decides that.

    `heading` is interpolated and nothing more, so an alert whose title comes from
    a variable still folds.

    Not a live region. This is markup that was on the page when it loaded;
    announcing it would repeat what a screen reader is about to read anyway. The
    toaster carries the live regions, because that is where content arrives after
    the fact.
--}}

@props([
    'tone' => null,
    'variant' => 'subtle',
    'toned' => null,
    'border' => false,
    'shadow' => false,
    'bar' => null,
    'barSquare' => false,
    'heading' => null,
    'icon' => null,
    'iconSize' => 'sm',
    'dismissible' => false,
])

@php
// A side or nothing, resolved once here so that every arm below branches on the
// same four words. `bar` written bare is `left` — the toast's own side, and the
// one a page read left to right marks a block on — because an attribute that
// silently drew nothing would be the easiest way to use this prop wrong.
$bar = match ($bar) {
    true, 'left' => 'left',
    'right' => 'right',
    'top' => 'top',
    'bottom' => 'bottom',
    default => null,
};

// Variant is how loud the alert is; tone is what it means. The two never
// multiply into a class matrix, because every arm below paints with the same
// tone variables and the foreground comes from the surface rather than from
// here — one `text-` class serves all four.
//
// The badge's set: `outline` takes a border and leaves the ink to `toned`
// below — the neutral one by default, the tone's own under `border`. `ghost`
// is that arm with the border dropped: the quietest of the four, for a message
// that belongs in the flow of a form or a panel that is already boxed.
//
// Only the paint changes. The padding above stays with it, so swapping a
// variant never moves the text, and the dismiss control's negative margins go
// on pulling it back into the same corner.
//
// Ghost is the one arm that paints on hover, and it resolves to `subtle`
// entire: the tint is the fill this alert would have had at rest, and the ink
// below is the foreground that belongs on it. Pointing at one shows its bounds
// — the block a dismiss control belongs to — rather than promising a click. It
// is the ghost button's recipe and the same variable, which is why the two
// agree without either knowing about the other. The transition rides in this
// arm rather than on the root, since the other three never move.
//
// It names its properties rather than taking `transition-colors`, because the
// cast under `shadow` is not a colour and Tailwind's shorthand does not carry
// `box-shadow`. Left to the shorthand the fill would fade in over a hundred
// milliseconds and the shadow would appear at once, which is the one way to
// make a hover look broken. The four named here are the whole of what this arm
// changes — fewer than the shorthand's, which reaches for gradients and strokes
// an alert has none of.
$classes = Shape::classes()
    ->add('flex items-start')
    ->add('[:where(&)]:gap-3 [:where(&)]:rounded-shape [:where(&)]:p-4')
    ->add(match ($variant) {
        'solid' => '[:where(&)]:bg-[var(--shape-tone)]',
        'ghost' => 'transition-[color,background-color,border-color,box-shadow] duration-100 hover:bg-[var(--shape-tone-tint)]',
        'outline' => null,
        default => '[:where(&)]:bg-[var(--shape-tone-tint)]',
    })
    // `border` is the edge, and it is opt-in because three of the four arms
    // read fine without one — the fill is the boundary. It earns its place
    // where the alert has to hold its own against a busy page, or sit next to
    // a card that is already drawn with one.
    //
    // The colour is a step of the tone rather than a border palette of its own.
    // `subtle` and `outline` take `--shape-tone-border-strong`, which is the
    // tone's answer to the neutral `--shape-tone-border` the outline arm reads
    // by default: the same job, far enough along the ramp that it reads as an
    // edge someone chose rather than a definition line. How far that is belongs
    // to the tone and not here — the coloured ones stop at their 300 and the
    // neutral goes on to its 400, for reasons shape.css sets out where the
    // variable is defined. It is one variable for both arms because the edge is
    // doing the same thing in each — on `subtle` it bounds the wash, on
    // `outline` it is the whole of the paint — and a border that changed weight
    // between them would make swapping the variant move more than the fill.
    //
    // `solid` cannot use it. The fill is the 700 and a 300 edge on it would
    // read as a highlight, so it takes `--shape-tone-hover` — the step past
    // the fill, which is darker in light mode and brighter in dark. Not
    // "darker" literally, then, but the same thing the fill's own hover means:
    // one step further from the page. A fixed darkening would invert in dark
    // mode, where the solid fill is already the light end of the ramp.
    //
    // Ghost draws the edge on hover with the tint it fills with, so the two
    // arrive together. The transparent border at rest is what keeps that from
    // moving the text a pixel — the box is reserved before the colour lands,
    // and `transition-colors` in the arm above already carries the border.
    //
    // Outline is the one arm the prop does not add a border to, because it
    // already has one; here the prop only decides its colour. Which is why
    // that arm's border moved out of the fill match above and into this one.
    ->add(match (true) {
        ! $border && $variant === 'outline' => '[:where(&)]:border [:where(&)]:border-[var(--shape-tone-border)]',
        ! $border => null,
        $variant === 'solid' => '[:where(&)]:border [:where(&)]:border-[var(--shape-tone-hover)]',
        $variant === 'ghost' => '[:where(&)]:border [:where(&)]:border-transparent hover:border-[var(--shape-tone-border-strong)]',
        default => '[:where(&)]:border [:where(&)]:border-[var(--shape-tone-border-strong)]',
    })
    // `shadow` is elevation, and the one prop here that carries no tone. An
    // alert is part of the page by default and a resting block in the flow of
    // the content it is about has nothing to lift away from; the prop is for
    // the alert that has to read as laid *on* the page rather than set into it
    // — over a dense table, beside a card drawn with its own.
    //
    // One step, and it is `shadow-sm`: the raised one, which is what buttons,
    // cards and inputs already take for sitting on the page. Shape owns no
    // elevation scale of its own — Tailwind's is already two-part and already
    // steps the way one should — so this reads the same `--shadow-sm` a
    // consumer rethemes, and docs/elevation.md is where the convention lives.
    // A call site wanting another step passes `class="shadow-lg"`, which the
    // zero specificity below is there to let it win.
    //
    // Ghost takes it on hover, with the fill and the border. A shadow is a cast
    // from a surface, and that arm has no surface until the pointer arrives —
    // drawn at rest it would ring a transparent block with an edge nothing in
    // it drew. Unlike the border there is nothing to reserve: a shadow is
    // painted outside the box and moves no text when it lands.
    ->add(match (true) {
        ! $shadow => null,
        $variant === 'ghost' => 'hover:shadow-sm',
        default => '[:where(&)]:shadow-sm',
    })

    // `bar` is the toast's edge, given a side. A toast carries its whole tone
    // in a thick rule down its left, because its fill stays white so that the
    // message reads over whatever it is floating above; an alert has four
    // variants for saying the same thing, so here the rule is opt-in — for the
    // block that has to be findable down a long page without being filled, and
    // for the one whose fill is already spoken for.
    //
    // `--shape-tone` at full strength, and deliberately not the border's step.
    // The two draw different things: a border bounds the block, so it sits a
    // step back down the ramp and lets the fill speak, while a bar *is* the
    // speaking. Four pixels of the pale 300 an edge reads would be a wide weak
    // stripe saying less than the one pixel it replaced.
    //
    // `solid` is where that breaks, and it breaks the way the border broke
    // there: the fill is already `--shape-tone` and a rule painted in it is no
    // rule at all. It takes `--shape-tone-hover` for the same reason the border
    // does — the step past the fill, darker in light mode and brighter in dark,
    // rather than a fixed darkening that would invert between them.
    //
    // Ghost draws its bar at rest, which neither of the other two edges does.
    // What the border and the shadow wait for is a surface to belong to: both
    // ring the block, and a ring around something that paints nothing is an
    // edge nothing drew. A rule down one side rings nothing — it is the mark in the
    // margin a blockquote takes, and it reads on the bare page as well as it
    // reads on a fill. Which makes `ghost` with a bar the quietest way this
    // component has of saying which tone a message is.
    //
    // The restatement under the pointer is on that arm and no other, and it is
    // there for `border`: the edge it paints on hover is the shorthand, which
    // lands after this in the cascade and would carry the bar off to the
    // border's colour along with the other three sides. Naming the side again
    // holds it. Every side is spelled out rather than composed, because
    // Tailwind reads these class names out of this file as text.
    ->add(match (true) {
        $bar === null => null,

        $bar === 'left' && $variant === 'solid' => '[:where(&)]:border-l-4 [:where(&)]:border-l-[var(--shape-tone-hover)]',
        $bar === 'left' && $variant === 'ghost' => '[:where(&)]:border-l-4 [:where(&)]:border-l-[var(--shape-tone)] hover:border-l-[var(--shape-tone)]',
        $bar === 'left' => '[:where(&)]:border-l-4 [:where(&)]:border-l-[var(--shape-tone)]',

        $bar === 'right' && $variant === 'solid' => '[:where(&)]:border-r-4 [:where(&)]:border-r-[var(--shape-tone-hover)]',
        $bar === 'right' && $variant === 'ghost' => '[:where(&)]:border-r-4 [:where(&)]:border-r-[var(--shape-tone)] hover:border-r-[var(--shape-tone)]',
        $bar === 'right' => '[:where(&)]:border-r-4 [:where(&)]:border-r-[var(--shape-tone)]',

        $bar === 'top' && $variant === 'solid' => '[:where(&)]:border-t-4 [:where(&)]:border-t-[var(--shape-tone-hover)]',
        $bar === 'top' && $variant === 'ghost' => '[:where(&)]:border-t-4 [:where(&)]:border-t-[var(--shape-tone)] hover:border-t-[var(--shape-tone)]',
        $bar === 'top' => '[:where(&)]:border-t-4 [:where(&)]:border-t-[var(--shape-tone)]',

        $variant === 'solid' => '[:where(&)]:border-b-4 [:where(&)]:border-b-[var(--shape-tone-hover)]',
        $variant === 'ghost' => '[:where(&)]:border-b-4 [:where(&)]:border-b-[var(--shape-tone)] hover:border-b-[var(--shape-tone)]',
        default => '[:where(&)]:border-b-4 [:where(&)]:border-b-[var(--shape-tone)]',
    })
    // `bar-square` is the bar's own corner, and the one prop here that does
    // nothing by itself. `rounded-shape` bends the last few pixels of a four-pixel rule
    // around the block, and the wider the rule the more that reads as a stripe
    // wrapped round a corner rather than a cut down one side. Squaring the two
    // corners the bar runs between straightens its ends and leaves the other
    // two rounded, so the alert still reads as one of these rather than as a
    // rectangle of tint.
    //
    // Without a bar it is inert, deliberately. Unrounding a block that has no
    // rule to straighten is a different decision — one about the shape of the
    // whole library rather than the end of one edge — and a call site that
    // wants it says `class="rounded-none"`, which the zero specificity is there
    // to let it win.
    ->add(match (true) {
        ! $barSquare || $bar === null => null,
        $bar === 'left' => '[:where(&)]:rounded-l-none',
        $bar === 'right' => '[:where(&)]:rounded-r-none',
        $bar === 'top' => '[:where(&)]:rounded-t-none',
        default => '[:where(&)]:rounded-b-none',
    })

    ->add('[:where(&)]:text-[color:var(--shape-fg)]');

// Whether the text is the tone's ink or the page's own foreground — the one
// thing about the paint a call site decides rather than the variant.
//
// Only where there is no fill. `subtle` and `solid` both paint a background,
// and what is readable on it is not a choice: dark ink on a saturated fill, or
// the global grey on a pink wash, are the two failures the surface contract
// exists to make impossible. `outline` and `ghost` sit on the page, where both
// answers read, so they are the two arms that ask.
//
// Both default to the page's ink. The tone is not lost with it — the border
// and the glyph go on carrying it — and what is gained is a block of body copy
// that reads as body copy, which is the better default for the long ones.
$toned = match ($variant) {
    'subtle', 'solid' => true,
    default => (bool) $toned,
};

// Untoned publishes no foreground at all rather than publishing the page's.
// The difference shows inside something that has a surface of its own — an
// outline alert in a solid card should take that card's ink, and inheritance
// already does it. It also leaves the dismiss control resolving its own
// neutral ink, which is the right answer on the page background and the same
// miss the toast relies on.
$surface = match (true) {
    $variant === 'solid' => 'solid',
    $toned => 'tint',
    default => null,
};

// The other half of the ghost arm's hover, and the reason it is an attribute
// rather than two more utilities: what changes on hover is the pair every
// nested component reads, and a heading painting its own `--shape-fg` cannot
// be reached by a `hover:text-` on this element. `data-shape-surface-hover`
// publishes the tint's foregrounds for as long as the pointer is there, and
// the heading, the body and the dismiss control all find them where they
// already look. Outline never paints on hover, so it never needs one.
$hoverSurface = $variant === 'ghost' && ! $toned ? 'tint' : null;

// The glyph keeps the tone the text gave up. An untoned alert still has to say
// what it means without colour being the only signal, and a one-pixel border
// is thin: leaving the icon toned is what keeps a `danger` outline alert
// readable as danger at a glance.
$iconClasses = $toned ? 'mt-0.5' : 'mt-0.5 text-[color:var(--shape-tone-ink)]';
@endphp

<div
    {{ $attributes->class($classes) }}
    data-shape-alert
    data-shape-variant="{{ $variant }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
    @if ($surface) data-shape-surface="{{ $surface }}" @endif
    @if ($hoverSurface) data-shape-surface-hover="{{ $hoverSurface }}" @endif
>
    {{--
        Never colour alone — every state tone resolves a glyph, so an alert
        stays readable in greyscale. `:icon="false"` opts out; forgetting
        isn't possible.

        `brand` and `accent` are not among them. Both are emphasis: the brand
        is the product's colour, which an application is free to move, and the
        accent is the one kept for "look here". A glyph on either would make it
        a state under another name — the one `info` now is, in a blue that
        stays blue whatever the brand becomes.

        A static tag per tone, as the badge does, rather than
        `<x-shape::icon :name=".." />` — the four built-in states never need
        `<x-dynamic-component>`'s temp-file round trip. A caller's own `icon`
        still goes through it; there's no fixed set of those to special-case.
    --}}
    @if ($icon !== false)
        @if ($icon)
            <x-shape::icon :name="$icon" :size="$iconSize" class="{{ $iconClasses }}" />
        @elseif ($tone === 'success')
            <x-shape::icon.shape-success :size="$iconSize" class="{{ $iconClasses }}" />
        @elseif ($tone === 'danger')
            <x-shape::icon.shape-danger :size="$iconSize" class="{{ $iconClasses }}" />
        @elseif ($tone === 'warning')
            <x-shape::icon.shape-warning :size="$iconSize" class="{{ $iconClasses }}" />
        @elseif ($tone === 'info')
            <x-shape::icon.shape-info :size="$iconSize" class="{{ $iconClasses }}" />
        @endif
    @endif

    <div class="flex min-w-0 flex-1 flex-col gap-1">
        @if ($heading)
            <x-shape::heading :level="3" size="sm">{{ $heading }}</x-shape::heading>
        @endif

        <x-shape::text size="sm" variant="muted" class="empty:hidden">{{ $slot }}</x-shape::text>
    </div>

    @if ($dismissible)
        {{-- One delegated listener in shape.js removes the nearest alert or
             toast. There is no platform primitive for "remove this element", so
             this is the one place feedback needs a handler of its own.

             `data-shape-dismiss` is also what the paint rule at the foot of
             shape.css keys on. A button declares a `data-shape-tone` of its own,
             so left alone this control resolves the neutral ink and the neutral
             tint whatever the tone above it; the rule hands it the surface's
             foreground instead. Nothing is passed down here either — the
             attribute the listener already needed is the whole hook. --}}
        <x-shape::button
            square
            size="sm"
            variant="ghost"
            icon="shape-close"
            icon-size="xs"
            aria-label="Dismiss"
            class="-mr-1.5 -mt-1.5 shrink-0"
            data-shape-dismiss=""
        />
    @endif
</div>
