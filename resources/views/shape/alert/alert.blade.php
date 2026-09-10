@blaze(fold: true, safe: ['heading', 'actions'])

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

    `icon-placement` moves the glyph rather than paints it, and there are two
    places it can go. `gutter` is the default and the one this component was
    drawn with: a column of its own, level with the first line and beside
    everything under it, which is what a heading and a paragraph and a row of
    buttons all want to be indented past. `inline` sets it at the head of the
    first line instead, so the sentence wraps under the glyph rather than into a
    column beside it — the shape a one-line alert wants, and the one that stops
    a notice in a form field's width from reading as a layout.

    It is a float rather than an inline-level glyph, which is what lets the same
    markup serve an alert with a heading and one without: the glyph is set
    before both and lands beside whichever turns out to hold the first line.

    `actions` is the row of buttons a message sometimes ends in, and it is a slot
    rather than the loose content of the default one, because the default slot is
    wrapped in an `<x-shape::text>` — a button written there would render inside a
    small muted paragraph. Named, it is a sibling of the prose instead of a child
    of it.

    Where it sits is the interesting half. A narrow alert wants the row beneath
    the message; a wide one has room for a single call to action out on the
    right, level with the text. That is a question about the alert's width and
    not about what the call site meant, so `actions-placement` names a width and
    lets a container query answer it — the alert declares itself a query
    container and the row flips at `@lg` by default, which is the alert grown
    past a form field and out to the width of a column of prose.

    One step cannot be right for every alert, though, because what fits beside a
    message depends on the message. A three-word notice with an `Undo` shares a
    line at 416px; a heading, a paragraph and three buttons are still cramped at
    700px. So the prop takes any of `sm`, `md`, `lg`, `xl` and `2xl`, and
    `below` and `side` pin the row at the two ends for the cases no width can
    decide.

    Those are Tailwind's *container* sizes, which is the distinction the whole
    prop rests on: `md` here is 28rem of alert and has nothing to do with the
    768px of viewport `md:` means everywhere else. They are not this library's
    `size` scale either, and the missing name is the tell — a `size` runs `sm`,
    `base`, `lg`, because that is Tailwind's type scale and `text-md` does not
    exist. The container scale has an `md` and no `base`. Calling the default
    step `base` here would put an invented name in a borrowed scale and land it
    in a slot the scale it was borrowed from has never had.

    A container query rather than a breakpoint because nothing else in this
    library ships a `sm:` or an `md:`, and deliberately — a component cannot see
    the viewport it landed in, and an alert in a sidebar and an alert across a
    page are the same markup at two widths. Querying its own width is the version
    of that rule a component can actually keep. The query container is declared
    only where there is an actions row to move, so the containment it brings with
    it lands on the alerts that use it and no others.

    Which leaves the ghost hover, where a fill arrives after the fact.
    `data-shape-surface-hover` is the pair `data-shape-surface` publishes,
    published for as long as the pointer is on the block — because a tint
    without the ink that belongs on it is the failure the contract exists to
    prevent, arriving a hundred milliseconds late.

    `tone` is branched on to resolve the glyph, exactly as the badge does, so it
    is *not* declared safe here — and neither are `variant`, `toned`, `border`,
    `shadow`, `bar` or `bar-square`, which branch to resolve the paint, nor
    `actions-placement` and `icon-placement`, which branch to resolve the layout.
    The same `tone` is safe on the button, which only ever interpolates it.
    Whatever a component does with a value decides that.

    `size` is the room the block is drawn with and the type the message is set
    in, moving together — an alert that grew its padding and left its sentence at
    14px would read as a small alert with a wide margin. `sm` and `base` share a
    type step and differ in the inset, which is the button's arrangement and for
    the button's reason: the two most common alerts on a page should not set
    their text differently.

    `icon-size` and the dismiss control follow it unless they are named, so the
    glyph and the × are in proportion at every step without a call site having to
    say so three times.

    `heading` is interpolated and nothing more, so an alert whose title comes from
    a variable still folds.

    `actions` is declared safe despite the `@if` below it, which is the one entry
    here that wants a sentence. A named slot listed in `@props` is unsafe to Blaze
    by default, because a component that branches on a prop usually branches on
    its *value* and a slot's value is whatever the call site wrote. This one
    branches on its presence, and presence is not a runtime fact: a call site
    either wrote `<x-slot:actions>` or it did not, and Blaze resolves the branch
    per call site while it folds. Anything that reached inside the slot would
    break that; nothing here does.

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
    'iconVariant' => null,
    'iconSize' => null,
    'iconPlacement' => 'gutter',
    'dismissible' => false,
    'actions' => null,
    'actionsPlacement' => 'lg',
    'size' => 'base',
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

// Every step of the scale, resolved once: the inset the block is drawn with and
// the gap between the glyph and the message, the gap between the message and the
// row of buttons under it, the type the message is set in, and the glyph's own
// step.
//
// The heading and the body take the same word, which is what keeps the glyph's
// two pixels of offset true across the scale — half the difference between a
// 20px line and a 16px glyph, and between a 24px line and a 20px one. The one
// place it slips is `lg`, where a heading's line is 24px and a paragraph's 28,
// so the glyph is centred on the sentence and sits two pixels below the cap line
// of a heading. Two pixels is not a difference anyone can see; a match arm for
// it would be.
[$inset, $rowGap, $messageSize, $glyphSize] = match ($size) {
    'xs' => ['[:where(&)]:gap-2 [:where(&)]:p-2.5', 'gap-2', 'xs', 'xs'],
    'sm' => ['[:where(&)]:gap-2.5 [:where(&)]:p-3', 'gap-2', 'sm', 'sm'],
    'lg' => ['[:where(&)]:gap-4 [:where(&)]:p-5', 'gap-3', 'base', 'base'],
    'xl' => ['[:where(&)]:gap-5 [:where(&)]:p-6', 'gap-4', 'lg', 'base'],
    default => ['[:where(&)]:gap-3 [:where(&)]:p-4', 'gap-3', 'sm', 'sm'],
};

// The control that closes it: its own step, the mark inside it, and the negative
// margins that pull it back into the corner the padding above just made. The
// three move together because the inset it is cancelling is the one in the arm
// above — a dismiss control that kept its `-mt-1.5` inside `p-6` would drift
// into the middle of the corner rather than sitting in it.
[$dismissSize, $dismissGlyph, $dismissInset] = match ($size) {
    'xs' => ['xs', 'xs', '-mr-1 -mt-1'],
    'sm' => ['xs', 'xs', '-mr-1.5 -mt-1.5'],
    'lg' => ['base', 'sm', '-mr-2 -mt-2'],
    'xl' => ['lg', 'sm', '-mr-2.5 -mt-2.5'],
    default => ['sm', 'xs', '-mr-1.5 -mt-1.5'],
};

// Named beats resolved, which is the alert's rule for the glyph and the toast's.
$iconSize ??= $glyphSize;

// The width the actions row flips at, resolved here for the same reason the
// side above is: two arms below branch on it, and both want the same answer.
//
// These are Tailwind's *container* sizes and not its breakpoints, which is the
// whole distinction this prop rests on. `md:` is 768px of viewport; `@md:` is
// 28rem of the nearest query container, which here is the alert. An alert in a
// sidebar and an alert across a page hit `@md` at different viewport widths and
// the same alert width, which is the only reading that makes sense for a
// component that does not know where it was put.
//
// Every step is spelled out rather than composed, for the reason `bar` spells
// out its four sides: Tailwind reads these class names out of this file as
// text, so `'@'.$step.':flex-row'` would generate nothing at all.
//
// Five steps and not the fourteen the scale has. Below `@sm` the row and the
// message are fighting over 350px and neither wins; above `@2xl` an alert that
// wide is rare enough that `below` is the honest answer. The span they cover —
// 416px to 704px of alert — is the whole of the interesting range.
//
// There is no `auto` among them, and there was for a while. It named the step
// the library had settled on, so that a call site could follow the default when
// it moved rather than pinning a width — which sounded useful until you ask who
// would type it. Omitting the prop already does that, and does it for the
// people who never thought about the question, which is the whole population
// `auto` was for. A value meaning "unset" inside a prop that has an unset state
// is one word for two things.
//
// So the default is a step like any other, and `lg` is that step. Anything
// unrecognised lands on it too, the same way `bar` falls back rather than
// drawing something no call site asked for.
$actionsQuery = match ($actionsPlacement) {
    'side', 'below' => null,
    'sm' => '@sm:flex-row @sm:items-center @sm:justify-between',
    'md' => '@md:flex-row @md:items-center @md:justify-between',
    'xl' => '@xl:flex-row @xl:items-center @xl:justify-between',
    '2xl' => '@2xl:flex-row @2xl:items-center @2xl:justify-between',
    default => '@lg:flex-row @lg:items-center @lg:justify-between',
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
    ->add('[:where(&)]:rounded-shape')
    ->add($inset)
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

    // The query container the placement steps below read, declared here and
    // nowhere else. `container-type: inline-size` is not free — it takes the
    // element out of intrinsic sizing and brings layout containment with it —
    // and an alert with no actions row has nothing to move, so it stays an
    // ordinary block. An alert that has pinned the row with `below` or `side`
    // has nothing to ask either.
    //
    // On the root rather than on the body below, because an element cannot
    // query itself: a container query resolves against the nearest *ancestor*
    // container, so the element that flips has to be inside the one that
    // measures. Which is the more useful of the two anyway — it means the
    // threshold is stated in the alert's own width, which is what a call site
    // can see, rather than in the width of a column it cannot.
    ->add($actions !== null && $actionsQuery !== null ? '@container' : null)

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

// Where the glyph sits, and the only prop here that moves it rather than
// paints it. Anything unrecognised is the gutter, the way `bar` falls back
// rather than drawing something no call site asked for.
$iconPlacement = $iconPlacement === 'inline' ? 'inline' : 'gutter';

// The glyph keeps the tone the text gave up. An untoned alert still has to say
// what it means without colour being the only signal, and a one-pixel border
// is thin: leaving the icon toned is what keeps a `danger` outline alert
// readable as danger at a glance.
//
// `mt-0.5` is the two pixels that centre a 20px glyph in the 24px line beside
// it, and it is the same two pixels in both placements — the heading and the
// body are both `leading-6` here, so the gutter's first line and the inline
// line box are the same height.
//
// Inline adds the float, which is the whole of what the placement changes.
// Taking the glyph out of flow is what makes the second line wrap *under* it
// rather than into a column beside it: the float is 20px against a 24px line,
// so it clears after the first one and the paragraph goes flush left. An
// inline-level glyph would do the same to the first line and then push the
// line box taller; a flex gutter cannot do it at all.
//
// `mr-2` rather than the root's `gap-3`. Twelve pixels is the distance between
// two blocks; eight is the distance between a glyph and the words it belongs
// to, and inline the glyph belongs to them.
$iconClasses = $toned ? 'mt-0.5' : 'mt-0.5 text-[color:var(--shape-tone-ink)]';

if ($iconPlacement === 'inline') {
    $iconClasses .= ' float-left mr-2';
}

// The message keeps a column of its own in both placements, so that the actions
// row is its sibling rather than another line of it, and `min-w-0` is what lets
// a long word inside it wrap instead of pushing the actions off the side.
//
// What changes is the formatting context. A flex column cannot hold a float —
// a flex item is taken out of the flow the float would have shifted — so the
// inline arm is block layout, `flow-root` to say out loud that the float is
// contained here rather than leaving it to the fact that this element happens
// to be a flex item of the body below.
//
// Which costs the `gap-1` block layout has no answer for. `mt-1` on the body
// where a heading precedes it is that gap restated: the same four pixels, and
// the same absence when the body is hidden by `empty:hidden`, because a margin
// on a `display: none` element is a margin on nothing. A margin under the
// heading instead would have left four pixels below a heading standing alone.
$messageClasses = $iconPlacement === 'inline'
    ? 'flow-root min-w-0 [&>[data-shape-heading]+[data-shape-text]]:mt-1'
    : 'flex min-w-0 flex-col gap-1';

// Where the actions row sits, which is the only thing the body element decides.
//
// Every named step resolves to a container query above; the two arms here are
// the pins, which ask nothing. `side` is the row entire and
// `below` is the column, and both are the same string the query would have
// produced at one end of its range — which is why they read as the ends of one
// scale rather than as a second prop.
//
// The default step, `@lg`, is 32rem of the root's content box: an alert of
// about 544px and wider. The boundary it draws is between an alert inside
// something — a form, a panel, a sidebar — and an alert across a column of
// content. Below it sit `max-w-sm` through `max-w-lg`, which is most of the
// alerts in a form; above it sits the 640-to-720px column that documentation
// and settings pages are built out of, which is where a call to action out on
// the right stops looking marooned.
//
// It was `@xl` first, and that was wrong by about one step: a 736px content
// column — this package's own documentation, and a common enough measure — puts
// an alert at 638px, which cleared 36rem by thirty pixels and stacked again the
// moment the page was read in a narrower window. A threshold the canonical case
// only just reaches is a threshold in the wrong place. It is also the reason a
// call site can name its own: the right width to flip at depends on how much is
// in the alert, and one number cannot serve a terse notice with one button and
// a heading with a paragraph and three.
//
// `justify-between` rather than `ml-auto` on the row itself, so the message
// keeps the space it needs and the actions take what is left. `items-center`
// with it: side placement only happens where there is room, and a button level
// with the middle of a two-line message reads better than one hung off its
// first line.
//
// The stacked gap is `gap-3` against the `gap-1` the heading and the message
// keep between them. A row of buttons is a different thing from the sentence
// above it, and the two gaps are what say so.
//
// Nothing at all without a row to place — the classes would be inert either way
// (a gap needs two children, and a container query with no container never
// matches), but an alert that renders three dead utilities is one that has to
// be explained every time someone reads its output.
$bodyClasses = match (true) {
    $actions === null => 'flex min-w-0 flex-1 flex-col',
    $actionsQuery === null && $actionsPlacement === 'side' => 'flex min-w-0 flex-1 flex-row items-center justify-between '.$rowGap,
    $actionsQuery === null => 'flex min-w-0 flex-1 flex-col '.$rowGap,
    default => 'flex min-w-0 flex-1 flex-col '.$rowGap.' '.$actionsQuery,
};
@endphp

<div
    {{ $attributes->class($classes) }}
    data-shape-alert
    data-shape-size="{{ $size }}"
    data-shape-variant="{{ $variant }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
    @if ($surface) data-shape-surface="{{ $surface }}" @endif
    @if ($hoverSurface) data-shape-surface-hover="{{ $hoverSurface }}" @endif
>
    {{--
        The gutter: the glyph in a column of its own, level with the first line
        and beside everything under it. It is the placement that suits the alert
        with something to say — a heading, a paragraph, a row of buttons all
        indented past one mark in the margin.

        `icon-size` and `icon-variant` are the icon's own two props, handed
        along to the glyph. Both are named for the thing they modify rather than
        taken bare, the way `bar-square` is: `size` and `variant` already mean
        the alert's own on every other component in the library, and one word
        cannot mean two things across it. `icon-placement` is the third of them
        and named on the same rule — `actions-placement` is the alert's other
        one, and neither is `placement`.

        `icon-size` follows the alert's `size` unless it is named, and at the
        three smaller steps that is a solid drawing. Which drawing belongs to the
        icon set and is the whole of what `icon-variant` is for; the glyph says
        why on its own page.
    --}}
    @if ($iconPlacement === 'gutter')
        <x-shape::alert.glyph
            :tone="$tone"
            :icon="$icon"
            :variant="$iconVariant"
            :size="$iconSize"
            class="{{ $iconClasses }}"
        />
    @endif

    <div class="{{ $bodyClasses }}">
        <div class="{{ $messageClasses }}">
            {{--
                The other placement: the glyph at the head of the first line,
                whichever element that line belongs to. There is no arm here
                choosing between the heading and the body, and that is the point
                of doing it with a float — the glyph is set before both of them
                and lands beside whatever the first line box turns out to be, so
                a heading takes it when there is one and the sentence takes it
                when there is not.

                Which makes it the placement for the alert that is one line
                long. A gutter under a single sentence is a column holding one
                thing; inline, the glyph reads as part of the sentence it marks,
                and an alert in a form field's width stops looking like a layout.
            --}}
            @if ($iconPlacement === 'inline')
                <x-shape::alert.glyph
                    :tone="$tone"
                    :icon="$icon"
                    :variant="$iconVariant"
                    :size="$iconSize"
                    class="{{ $iconClasses }}"
                />
            @endif

            @if ($heading)
                <x-shape::heading :level="3" :size="$messageSize">{{ $heading }}</x-shape::heading>
            @endif

            <x-shape::text :size="$messageSize" variant="muted" class="empty:hidden">{{ $slot }}</x-shape::text>
        </div>

        {{-- Rendered only where the slot was written, rather than always and
             hidden when empty. `actions` is declared in `@props` above, so the
             question here is the same compile-time one `heading` asks — whether
             the call site passed it — and not the runtime one about what a slot
             happens to contain.

             `shrink-0` beside the message's `min-w-0`: when the two share a row
             the buttons are the fixed part and the sentence is the part that
             gives. `flex-wrap` for when even that is not enough, the way the
             card's footer wraps for the same reason. --}}
        @if ($actions)
            <div class="flex shrink-0 flex-wrap items-center {{ $rowGap }}" data-shape-alert-actions>{{ $actions }}</div>
        @endif
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
            :size="$dismissSize"
            variant="ghost"
            icon="shape-close"
            :icon-size="$dismissGlyph"
            aria-label="Dismiss"
            class="{{ $dismissInset }} shrink-0"
            data-shape-dismiss=""
        />
    @endif
</div>
