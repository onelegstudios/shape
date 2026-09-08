@blaze(fold: true, memo: true, safe: ['initials', 'alt', 'tone', 'badgeTone'])

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

    `border` is that edge, asked for rather than arrived at through the variant.
    `outline` was the only way to ring an avatar, and ringing a photograph that
    way meant giving up the fill under it — the ground a transparent picture
    sits on, and what fills the circle while any picture is arriving. The prop
    holds the two apart: the fill stays whatever the variant paints and the edge
    is a separate question.

    It draws `--shape-tone-border-strong`, the same edge `outline` draws, so the
    two agree about what the tone's edge is and swapping the variant moves the
    fill rather than the ring. `solid` cannot take that step — a pale edge on a
    saturated fill reads as a highlight — so it takes the one past the fill,
    `--shape-tone-hover`, which is darker in light mode and brighter in dark for
    the same reason the fill's own hover is.

    `outline` is the arm the prop does nothing to, because the ring it would
    draw is the ring already there. Which is the one place this parts from the
    alert, whose outline arm draws the neutral chrome border and whose `border`
    tones it: an avatar's outline is the tone's own already, for the reason
    above.

    A border and not the ring the group draws, which is the other edge in this
    file. The ring is outside the box in the page's own colours, holding
    overlapping faces apart; the border is inside it in the tone's, saying the
    circle has an edge. A bordered avatar in a group has both, because they are
    answering different questions.

    It takes no room either way. The box is a fixed width and a height and the
    border is drawn inside it, so a bordered avatar lays out exactly as one
    without and a row of faces does not move when one of them is ringed.

    `ground` is for the pictures that do not cover the circle. A Gravatar asked
    for `d=blank` answers for a stranger with a transparent GIF, and a
    transparent GIF over a tint is an empty circle where two letters would have
    done. So the letters — or the glyph, on the same ladder they always take —
    are drawn first and the picture is laid over them.

    It is asked for rather than assumed, and the bare `<img>` is the reason. A
    picture with nothing around it is the one arm that carries the attribute bag
    on the picture itself, which is what keeps `class="object-contain"` landing
    on the thing it was written for. A ground needs an element to sit in, so
    `ground` moves the bag one element out exactly as `as` does, and letterboxing
    becomes `class="[&>img]:object-contain"`. Made the default, that would have
    moved under every call site that never asked for it.

    It is a runtime fallback as well, which it did not use to be. A picture that
    failed used to leave a broken image sitting on the letters rather than the
    letters, because a broken image is something the browser draws rather than
    something it does not — so `shape.js` hides a picture that errors, and what
    was already underneath is what is left.

    In the markup rather than in the script, which is the division worth stating.
    The letters are drawn by this component whether or not anything fails; all
    the script does is take away the icon a browser draws over them. A page with
    no `shape.js` on it loses the hiding and keeps everything else.

    So `ground` answers three things now: a picture that arrives and is
    see-through, the moment before any picture arrives at all, and a picture that
    never arrives. Only the first two are the markup's own.

    A picture with nothing under it is the arm the script leaves alone. A bare
    `<img>` is the whole avatar, and hiding it takes a face out of a row and
    leaves a hole; the broken icon at least holds the place. Which is the reason
    to pass `ground` — or `as` — to any avatar whose `src` a stranger controls.

    The picture is positioned and the ground is not, so it paints over without a
    z-index — positioned elements paint after in-flow ones, which is the rule
    the group leans on from the other side.

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

    `badge` is the mark on the corner: a presence dot, a count of things waiting,
    a check standing for verified. Written bare it is a dot, and given anything
    else that thing is the mark's text.

    One prop for both, because they are one drawing at two widths — a dot is a
    badge with nothing in it. Splitting them into `badge` and `badge-count` would
    ask a call site to name the shape it wanted when the only thing it knows is
    the fact it is reporting.

    A count of zero paints nothing, which falls out of the prop being read for
    truth rather than for presence. `:badge="$person->unread"` is the whole of
    the call site, and the badge that would have said `0` is one that should not
    have been there.

    The mark is solid at every one of the circle's variants. `subtle` is a tint,
    a tint on a photograph is a pale smudge on a face, and being seen against
    whatever it landed on is the entire job. There is no `badge-variant` for the
    reason there is no `ghost` avatar: the quiet arm of a mark this small is the
    mark not being there.

    `badge-tone` is its own colour rather than the avatar's, because the common
    call site is a `brand` avatar wearing a green dot. Inheriting would have made
    that the one arrangement the component could not draw, and a status that
    agreed with the person's initials would be reporting nothing.

    It carries the tone on itself, so the badge resolves `--shape-tone` from its
    own element and the avatar beside it goes on resolving its own. Two tones in
    one component and no third set of variables to invent.

    What holds the two apart is a ring in the page's colours — the same pair the
    group rings its children with, for the same reason. Without it a green dot on
    a green-shirted photograph has no edge of its own; with it there are two
    pixels of page between the mark and everything under it, in both modes.

    The mark is absolute and its ring is a shadow, so neither takes any room: an
    avatar with a badge lays out exactly as one without. A group still overlaps
    by the same eight pixels, and a row of faces does not shift when one of them
    comes online. It does draw outside the box, which is the trade — a mark
    centred on an edge is half over that edge by definition.

    Centred on the edge and not inscribed in the corner, which is the difference
    between a mark sitting on the circle and a mark eating it. The corner of the
    box is not the corner of the shape: the arc crosses the diagonal `1 - 1/√2`
    of the corner radius inside the box on both axes — 14.6% of the width for a
    circle, whose radius is half of it, and `--radius-shape` for a squared
    avatar, which is a length rather than a fraction and so the same two pixels
    at all four sizes. Half the mark is then pulled back over that point.

    A count grows both ways from there rather than only inwards, which is the
    whole reason the anchor is the mark's centre and not its corner. Inscribed
    instead, a pill wide enough for three digits would walk across the face while
    the dot beside it sat on the edge, and the two would stop reading as the same
    component.

    It takes the corner the avatar took as well. On a dot that is the same circle
    either way — `--radius-shape` is 8px and a dot is 6 to 12 — so what it
    changes is the counts, the only marks wide enough to have ends. A squared
    avatar is not a person, and a company's unread count should not be the one
    round thing on it.

    Its size is the circle's, resolved here the way the glyph's is. The dot is
    6px, 8px, 10px and 12px, about a quarter of the circle at every size, and the
    pill is that dot with room for a number in it — eight pixels more, each time.
    Padding is half a step at the two small circles and grows with them, because
    what makes a count unreadable on a 24px avatar is the box around the digits
    rather than the digits.

    `badge-position` moves it, named for the corners the way the toaster names
    its own. `bottom-right` is the default because presence is what a dot on a
    person means nearly every time it is not a count, and presence has been drawn
    there for as long as anyone has drawn it.

    In a group it wants to be on the last avatar, or at `bottom-left`. Later
    siblings paint over earlier ones and this library has no z-index — the group
    says why — so a mark on the right-hand corner of an overlapped face is under
    the next one.

    It is `aria-hidden`, exactly as the initials are. A dot is a colour with no
    words in it and a count is a number with no noun attached; both mean
    something only in a sentence, and the sentence is `alt`. "Ada Lovelace,
    online" is the avatar announcing itself once, in the order a person reads.

    It branches twice — whether there is a wrapper at all, then whether the mark
    has text in it — so it is not safe, and `badge-position` resolves insets so
    it is not either. `badge-tone` joins `tone` in the safe list, because it is
    interpolated into an attribute and nothing more: a presence colour bound per
    person still folds, which is the arrangement worth writing at a call site —
    `badge` bare and its tone from the row.

    The wrapper is scaffolding rather than a box. It exists to be the thing the
    mark is positioned against and, on a control, the thing the pair dims as, it
    renders only when there is a mark to position, and it carries no attribute of
    its own: naming it would promise a box that is not there on the avatars that
    have no badge.

    The attributes stay on the avatar, which is the answer that keeps
    `class="object-contain"` landing on the picture it was written for. A wrapper
    that took the bag would move every class a call site has ever passed onto an
    element that is not the circle.

    `as` makes the circle a control. Most avatars are labels on a row, but some
    are the way into something — the account menu in a header, the face that
    opens a profile, the assignee that opens a picker — and those have to be
    pressable by a keyboard as well as by a pointer.

    It swaps the tag and nothing else. The control *is* the circle rather than a
    button wrapped around one: the same classes, the same box, the same
    `data-shape-avatar`, the same bag. Wrapping would have lost the paragraph
    above from the other side, moving every class a call site has ever passed
    onto an element that is not the circle.

    `href` implies `a` without being asked, the way the tab and the menu item
    resolve their own element. Middle-click, "open in new tab" and the status bar
    all work for a link and none of them work for a button pretending to be one
    — and an `href` on the `<span>` this used to render did nothing at all, so
    there is no call site that meant the other answer.

    A picture cannot be a control, which is the one place the structure moves.
    `<img>` takes no children and takes no press, so under `as` the photograph
    becomes a child of the control and the control takes the circle's classes.
    It fills the box and is cropped by it exactly as before; what changes is that
    the bag is one element further out, so letterboxing is
    `class="[&>img]:object-contain"` rather than `class="object-contain"`.

    Which works for a class and for nothing else. `loading`, `srcset`, `sizes`,
    `decoding`, `fetchpriority`, `crossorigin` and `referrerpolicy` are not
    classes and no selector reaches them, so with the bag one element out they
    landed on the `<span>` a ground renders, where they do nothing, or on the
    `<button>` a control renders, which is not an element that has them. The one
    arrangement that could say `loading="lazy"` was the bare photograph, and a
    row of fifty faces — the call site that wants it — is usually a link.

    So those seven are lifted off the bag and put back on the picture, and the
    three arrangements agree about where a picture's attribute goes. Everything
    else lands where it always has, `class` first among them: it paints the
    circle, which is the element the picture sits in.

    Attributes rather than a prop holding them, and that is folding rather than
    taste. Blaze hands a `safe` prop a string placeholder while it folds, so an
    array of attributes cannot be safe — a call site that wrote
    `:picture="['loading' => 'lazy']"` would have left the fold path to say it.
    Lifted off the bag they fold bound, exactly as a per-person `class` does.

    A control does not repaint on hover, which is the one thing it does not
    borrow from the button. The button's paint is chrome and its hover is a
    louder version of the same chrome. An avatar's paint is what the avatar
    means, and there is no photograph anywhere that a `--shape-tone-hover` would
    reach. So it dims, which is the vocabulary `disabled` already uses here and
    the one answer that reads the same on a face, on two letters and on a glyph.

    The focus ring is the button's exactly — `--shape-ring`, two pixels, offset
    two — because that one is chrome, and a control that focused differently from
    every other control in the library would be reporting a difference that is
    not there. `disabled` and `aria-disabled` both dim and both stop the pointer,
    for the reason the button carries both: an anchor cannot be disabled.

    A badged control dims from its shell rather than from the circle, and that is
    the whole of what makes the mark dim with it. The mark is a sibling of the
    circle, so a dim on the circle stops there and leaves a presence dot at full
    strength on a face that has faded — the half of the component still reporting
    that it is live.

    Dimming the mark to match is the answer that looks right and is not.
    `opacity` on the mark makes the mark itself translucent, so the circle's own
    edge reads through the dot that is meant to be covering it, and the white
    ring holding the two apart stops holding anything apart. It is worst at
    `disabled`, which fades furthest.

    On the shell the pair is rendered together first and the result is faded, so
    the mark goes on covering what it sits on and only the pair fades against the
    page. One dim instead of two, and nothing to keep in step.

    Hover is asked for with `has-` rather than taken from the shell's own
    `:hover`, because the shell is not the thing that stops taking a pointer when
    the control is disabled. `:has(:hover)` finds the circle, and a circle with
    `pointer-events-none` is never hovered — so a disabled avatar dims once,
    which is the only place these two states could have collided.

    A control has to be named. An avatar beside a name already on the page
    passes no `alt` and announces nothing, which is right for a picture and wrong
    for a button — an unnamed one is announced as "button" and nothing else. So
    pass `alt` to anything that can be pressed, even where the name is on the row
    beside it.

    Inside a control the photograph is named the way the initials are, which is
    to say it is not. `alt=""` on the picture and the name in the same
    screen-reader text the letters and the glyph use: a photograph of a person is
    a picture of their name exactly as `AL` is, and a control that carried both
    would announce them twice. The bare `<img>` keeps its real `alt`, because
    there is no element around it to put the text in.

    It chooses an element, so it branches and is not safe. It is also a word a
    call site writes rather than binds, so an avatar that is a control folds like
    any other.

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
    still folds. `variant` resolves the paint above, `border` the edge and
    `square` the radius, so none of the three is safe — the badge's arrangement
    as well.

    The consequence is worth stating rather than discovering: an avatar list
    built from per-row URLs neither folds nor usefully memoizes, because every
    memo key is unique. It is still annotated `memo: true`, because the initials
    form is common and does fold; the docs say plainly which call site pays.
--}}

@props([
    'src' => null,
    'as' => null,
    'icon' => null,
    'iconVariant' => 'solid',
    'initials' => null,
    'alt' => null,
    'size' => 'base',
    'tone' => null,
    'variant' => 'subtle',
    'border' => false,
    'square' => false,
    'ground' => false,
    'badge' => false,
    'badgeTone' => null,
    'badgePosition' => 'bottom-right',
])

@php
// An avatar is a control when it is asked to be one, and an `href` asks without
// saying so — the tab and the menu item resolve their own element the same way.
$control = $as ?? ($attributes->has('href') ? 'a' : null);

// The shell a badged avatar is wrapped in, which is also where a badged control
// does its dimming. `opacity` on the mark itself would have made the mark
// translucent and shown the circle's own edge through it; on the pair it renders
// them together first and fades the result, so the mark still covers what it is
// sitting on. Hover is asked for through `has-` rather than taken from the
// shell's own `:hover`, because the shell is not the thing that stops taking a
// pointer when the control is disabled.
$shellClasses = Shape::classes()
    ->add('relative inline-flex shrink-0')
    ->add($control ? 'transition-opacity duration-100 has-hover:opacity-80' : null)
    ->add($control ? 'has-disabled:opacity-50 has-aria-disabled:opacity-50' : null);

$iconSize = match ($size) {
    'xs', 'sm' => 'xs',
    'lg' => 'base',
    default => 'sm',
};

$classes = Shape::classes()
    ->add('inline-flex shrink-0 items-center justify-center overflow-hidden')
    ->add($square ? '[:where(&)]:rounded-shape' : '[:where(&)]:rounded-full')
    ->add('[:where(&)]:font-medium')

    ->add(['[:where(&)]:object-cover' => $src && ! $control && ! $ground])

    // Only when there is something laid over it. Structural rather than a
    // default, so it is not written through `:where()`: a call site that beat it
    // would drop the picture out of the circle it is meant to be filling.
    ->add($src && $ground ? 'relative' : null)

    ->add(match ($size) {
        'xs' => '[:where(&)]:size-6 [:where(&)]:text-2xs',
        'sm' => '[:where(&)]:size-8 [:where(&)]:text-xs',
        'lg' => '[:where(&)]:size-12 [:where(&)]:text-base',
        default => '[:where(&)]:size-10 [:where(&)]:text-sm',
    })

    ->add(match ($variant) {
        'solid' => '[:where(&)]:bg-[var(--shape-tone)] [:where(&)]:text-[var(--shape-tone-fg)]',
        'outline' => '[:where(&)]:text-[var(--shape-tone-ink)]',
        default => '[:where(&)]:bg-[var(--shape-tone-tint)] [:where(&)]:text-[var(--shape-tone-ink)]',
    })

    // The edge, which `outline` draws whether or not it was asked for one and
    // the other two arms draw only when they are. It is the same
    // `--shape-tone-border-strong` in both places, so the prop and the variant
    // agree about what the tone's edge is; `solid` takes the step past its fill
    // instead, because a pale edge on a saturated one reads as a highlight.
    // Drawn inside the fixed box, so nothing it is beside moves.
    ->add(match (true) {
        ! $border && $variant !== 'outline' => null,
        $variant === 'solid' => '[:where(&)]:border [:where(&)]:border-[var(--shape-tone-hover)]',
        default => '[:where(&)]:border [:where(&)]:border-[var(--shape-tone-border-strong)]',
    })

    // A control is the circle rather than a button around one, so all it adds is
    // the chrome a control owes: something under the pointer, something under
    // the keyboard, and a way to say it is taking neither. It does not repaint —
    // the paint above is what the avatar means, and no `--shape-tone-hover`
    // reaches a photograph — so it dims, the way `disabled` already does here.
    ->add($control ? 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]' : null)
    ->add($control ? 'disabled:pointer-events-none aria-disabled:pointer-events-none' : null)

    // A badged control dims from its shell instead, so these are the arm where
    // the circle is the whole of the drawing and there is nothing beside it to
    // keep in step.
    ->add($control && ! $badge ? 'transition-opacity duration-100 hover:opacity-80' : null)
    ->add($control && ! $badge ? 'disabled:opacity-50 aria-disabled:opacity-50' : null);

// The picture inside an element, which is every arrangement but the bare one:
// a control, or a ground. `absolute` is what lays it over the letters, and it
// paints above them without a z-index because positioned elements paint after
// in-flow ones.
$pictureClasses = Shape::classes()
    ->add($ground ? 'absolute inset-0' : null)
    ->add('size-full')
    ->add('[:where(&)]:object-cover');

// The names only a picture understands, lifted off the bag so they reach the
// `<img>` in the two arrangements where the bag is one element out. On the bare
// arm the whole bag is already on the picture and this takes nothing away.
$picturesOwn = ['loading', 'decoding', 'fetchpriority', 'srcset', 'sizes', 'crossorigin', 'referrerpolicy'];

$pictureAttributes = $attributes->only($picturesOwn)->class((string) $pictureClasses);

// The badge's classes are not merged with anything, because the attribute bag
// stays on the avatar, so they are written flat rather than through `:where()`.
// Nothing here is a default a call site could beat with a class of its own.
$badgeClasses = Shape::classes()
    ->add('pointer-events-none absolute inline-flex items-center justify-center')
    ->add('bg-[var(--shape-tone)] text-[var(--shape-tone-fg)]')

    // The mark takes the corner the avatar took, which on a dot is the same
    // circle either way — `--radius-shape` is 8px and a dot is 6 to 12 — and
    // shows up on the counts, which are the marks wide enough to have ends.
    ->add($square ? 'rounded-shape' : 'rounded-full')

    // Two pixels of page between the mark and whatever it landed on, in the same
    // colours the group rings its children with.
    ->add('ring-2 ring-white dark:ring-shape-900')

    // Half the mark, on each axis, pulled back over the corner it is anchored
    // to. Which is what puts its centre on the edge rather than its corner in
    // the box, and what makes a count grow both ways instead of only inwards.
    ->add(match ($badgePosition) {
        'top-left' => '-translate-x-1/2 -translate-y-1/2',
        'top-right' => 'translate-x-1/2 -translate-y-1/2',
        'bottom-left' => '-translate-x-1/2 translate-y-1/2',
        default => 'translate-x-1/2 translate-y-1/2',
    })

    // And the corner is the point where the shape's own arc crosses the
    // diagonal, which sits `1 - 1/√2` of the corner radius inside the box on
    // both axes. A circle's radius is half its width, so that is 14.6% of it; a
    // squared avatar's is `--radius-shape`, which is a length rather than a
    // fraction and stays where it is at all four sizes.
    ->add($square
        ? match ($badgePosition) {
            'top-left' => 'top-[calc(var(--radius-shape)*0.293)] left-[calc(var(--radius-shape)*0.293)]',
            'top-right' => 'top-[calc(var(--radius-shape)*0.293)] right-[calc(var(--radius-shape)*0.293)]',
            'bottom-left' => 'bottom-[calc(var(--radius-shape)*0.293)] left-[calc(var(--radius-shape)*0.293)]',
            default => 'bottom-[calc(var(--radius-shape)*0.293)] right-[calc(var(--radius-shape)*0.293)]',
        }
        : match ($badgePosition) {
            'top-left' => 'top-[14.6%] left-[14.6%]',
            'top-right' => 'top-[14.6%] right-[14.6%]',
            'bottom-left' => 'bottom-[14.6%] left-[14.6%]',
            default => 'bottom-[14.6%] right-[14.6%]',
        })

    // A dot is a fixed circle; anything with text in it is that dot with room
    // for a number in it, which is eight pixels more at every size. It never
    // goes narrower than it is tall, and `tabular-nums` keeps a count from
    // changing width as it counts.
    ->add($badge === true
        ? match ($size) {
            'xs' => 'size-1.5',
            'sm' => 'size-2',
            'lg' => 'size-3',
            default => 'size-2.5',
        }
        : match ($size) {
            'xs' => 'h-3.5 min-w-3.5 px-0.5 text-2xs font-medium tabular-nums',
            'sm' => 'h-4 min-w-4 px-0.5 text-2xs font-medium tabular-nums',
            'lg' => 'h-5 min-w-5 px-1.5 text-xs font-medium tabular-nums',
            default => 'h-4.5 min-w-4.5 px-1 text-2xs font-medium tabular-nums',
        });
@endphp

{{-- The wrapper is opened and closed around the avatar rather than repeated
     inside both arms of the branch below, so there stays one copy of what goes
     inside the circle however that circle ends up being drawn.

     A photograph with nothing around it is the one arm that cannot go through
     the element, because an `<img>` carries its picture in an attribute and
     takes no children. Every other arrangement — letters, a glyph, or a
     photograph inside a control that can be pressed — is one element with
     something in it. --}}
@if ($badge)<span class="{{ $shellClasses }}">@endif
@if ($src && ! $control && ! $ground)
    <img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->class($classes) }} data-shape-avatar data-shape-size="{{ $size }}" data-shape-variant="{{ $variant }}" data-shape-tone="{{ $tone ?? 'neutral' }}">
@else
    <x-shape::avatar.element :as="$control" {{ $attributes->except($picturesOwn)->class($classes) }} data-shape-avatar="" data-shape-size="{{ $size }}" data-shape-variant="{{ $variant }}" data-shape-tone="{{ $tone ?? 'neutral' }}">@if (! $src || $ground)
@if ($icon)<x-shape::icon :name="$icon" :variant="$iconVariant" :size="$iconSize" />@else<span aria-hidden="true">{{ $initials }}</span>@endif
@endif
@if ($src)<img src="{{ $src }}" alt="" {{ $pictureAttributes }}>@endif<span class="sr-only">{{ $alt }}</span></x-shape::avatar.element>
@endif
@if ($badge)<span class="{{ $badgeClasses }}" aria-hidden="true" data-shape-avatar-badge data-shape-position="{{ $badgePosition }}" data-shape-tone="{{ $badgeTone ?? 'neutral' }}">@if ($badge !== true){{ $badge }}@endif</span></span>@endif
