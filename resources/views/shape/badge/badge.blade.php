@blaze(fold: true, memo: true, safe: ['label'])

{{--
    A label with a status attached.

    Prop-first and slotless on purpose. Badges are the highest-volume component
    in any table, and Blaze only memoizes self-closing calls — a `{{ $slot }}`
    here would trade the single most effective optimization available for a
    composition nobody needs inside a badge.

    `label` is interpolated and nothing more, so it is safe: a badge in a table
    folds even though its text differs on every row.

    `tone` is not, and cannot be. The button passes its tone straight through
    to `data-shape-tone` and stays foldable; a badge branches on the tone to
    resolve its icon, so a dynamic `:tone` drops to the memo path. That path is
    only cheap while labels repeat — with a dynamic tone AND a label unique to
    each row, every call is a memo miss, which measures around sixty times the
    cost of folding. Keep the tone static where you can.

    `as` makes the badge a control, and an `href` makes it a link without being
    asked — the same resolution the avatar, the tab and the menu item make. It
    swaps the tag and adds the chrome a control owes; the box, the padding and
    the paint are the ones a badge has anyway, because the control *is* the
    badge rather than a button wrapped around one. Everything Shape does not
    claim as a prop therefore lands on the thing being pressed.

    It branches, so it is not safe, and in practice that costs nothing: `as` is
    a word a call site writes rather than binds, so a badge that is a control
    folds like any other.

    `dismissible` turns the badge into a chip and is the same kind of prop: a
    word, a branch, not safe, and free in practice. It cannot be combined with
    `as` or an `href`, which is the one prop pairing in this component that
    raises rather than resolving — the reason is in the guard below.

    `dot` is the mark for the statuses this library has no opinion about. Four
    tones resolve a glyph because four tones are states; the other three are
    emphasis, and an application's own vocabulary — draft, archived, in review —
    lands on those with nothing in front of the word. The dot is something to
    point at in a column of them. It is not a second signal, since a colour and a
    circle say the same thing to anyone who cannot separate the hues, which is
    why the label is still the whole of what a badge means.

    `selected` is the other half of a filter bar: `dismissible` takes a chip off,
    and this is the chip that can be turned on. It is a state rather than a word,
    so it is the one prop here usually bound per call — and a bound prop that
    branches does not fold. That is the tab's position too, and for the same
    reason it is worth knowing rather than worth avoiding: a filter bar is a
    handful of chips, where a table is two hundred rows.
--}}

@props([
    'label' => null,
    'tone' => null,
    'variant' => 'subtle',
    'size' => 'base',
    'icon' => null,
    'dot' => false,
    'iconTrailing' => null,
    'iconSize' => 'xs',
    'inset' => false,
    'square' => false,
    'dismissible' => false,
    'selected' => null,
    'as' => null,
])

@php
// A badge is a control when it is asked to be one, and an `href` asks without
// saying so — the avatar, the tab and the menu item resolve their own element
// the same way. Middle-click, "open in new tab" and the status bar all work for
// a link and none of them work for a button pretending to be one, and `as` wins
// where a call site wants a control that happens to carry an `href`.
$control = $as ?? ($attributes->has('href') ? 'a' : null);

// A dismissible badge is a chip, and the x inside it is a control. That rules
// out the badge being one as well: a `<button>` inside a `<button>` is not
// invalid-but-tolerated markup, it is markup the parser rewrites — the inner
// one closes the outer, and a call site that wrote a nesting gets two siblings.
// An `<a>` does the same to any interactive content inside it.
//
// So this raises rather than resolving something. Both resolutions available
// are silent: dropping the control loses the caller's navigation, dropping the
// x loses the dismissal, and neither leaves a trace at the call site. A chip
// that both navigates and dismisses is two controls that need a box around
// them, which is a thing a call site can write and not a thing this component
// can be — everything here is one element by design.
//
// Checked inside the `dismissible` arm, so the badge that repeats down a table
// pays a single boolean for a guard it never trips.
if ($dismissible && $control) {
    throw new InvalidArgumentException('A dismissible badge cannot also be a control: [as] and [href] would put the dismiss button inside another control. Wrap the badge in a control of your own instead.');
}

// A chip cannot be on and removable at once, for the same reason it cannot be a
// control and dismissible: the state has to sit on something pressable and the x
// is already that something, and a badge is one element. Caught here rather than
// left to the guard above, which would otherwise send a call site to add `as`
// and then throw at it for having done so.
if ($selected !== null && $dismissible) {
    throw new InvalidArgumentException('A badge cannot be both selected and dismissible: a toggle and a dismiss button are two controls, and a badge is one element. Wrap the two in a box of your own instead.');
}

// `selected` is a state, and a state nobody can change is not one. It also has
// nowhere to be announced on a `<span>`: `aria-pressed` needs a button under it,
// and painting the chip without it would leave the colour saying what nothing
// says out loud. Both resolutions are silent, so this raises the way the guard
// above does.
if ($selected !== null && ! $control) {
    throw new InvalidArgumentException('A badge cannot be selected without being a control: [selected] is the state of something that can be pressed, so give it [as] or an [href].');
}

// Which claim the state makes depends on what the badge turned out to be.
// `aria-pressed` is a button's claim about itself; `aria-current` is a link's
// claim about where it points, and it is a global attribute, so the `div` arm
// takes it too. The tab splits the same way, for the same reason.
//
// Null rather than false is what makes a badge a toggle at all. A chip that
// clears a filter is an action, and an `aria-pressed="false"` on it would report
// a pressed-ness nobody asked about — so the prop has three states and only the
// two written ones say anything.
$state = match (true) {
    $selected === null => [],
    $control === 'button' => ['aria-pressed' => $selected ? 'true' : 'false'],
    default => ['aria-current' => $selected ? 'page' : null],
};

// The dot is drawn here rather than named in the icon set, because the set is
// generated from Heroicons and a circle is not one of them — and because a
// status mark is a shape at 4 to 8 pixels, which is a size no glyph is drawn
// for. Roughly a quarter of the badge's height, which is the proportion the
// avatar's mark keeps.
//
// `bg-current` for the reason the dismiss control takes no colour of its own:
// preflight leaves it inheriting whatever ink the variant resolved — the tone's
// on a tint, the readable foreground on a fill — so one element is right on all
// three variants and branches on none of them. A dot painted `--shape-tone`
// would have vanished into a `solid` badge of the same tone.
$dotClasses = ! $dot ? null : match ($size) {
    'xs' => 'size-1 shrink-0 rounded-full bg-current',
    'lg' => 'size-2 shrink-0 rounded-full bg-current',
    default => 'size-1.5 shrink-0 rounded-full bg-current',
};

$classes = Shape::classes()
    ->add('inline-flex items-center whitespace-nowrap align-middle')
    ->add('[:where(&)]:rounded-shape [:where(&)]:font-medium')

    // Four heights: 16px, 20px, 24px, 28px. The vertical padding is what moves
    // them apart — `text-2xs` and `text-xs` share a 1rem line box, so a scale
    // that only changed the type size and the side padding would render two
    // badges the same height and differ by two pixels of gutter.
    ->add($square ? null : match ($size) {
        'xs' => '[:where(&)]:gap-1 [:where(&)]:px-1.5 [:where(&)]:py-0 [:where(&)]:text-2xs',
        'sm' => '[:where(&)]:gap-1 [:where(&)]:px-2 [:where(&)]:py-0.5 [:where(&)]:text-2xs',
        'lg' => '[:where(&)]:gap-1.5 [:where(&)]:px-3 [:where(&)]:py-1 [:where(&)]:text-sm',
        default => '[:where(&)]:gap-1.5 [:where(&)]:px-2.5 [:where(&)]:py-1 [:where(&)]:text-xs',
    })

    // A badge with nothing in it but a glyph, or a count — the side padding is
    // for holding a word off the ends, and neither of those is a word. The same
    // four heights, asked for as a height because there is no longer a padding
    // to arrive at them through, which is `square` on the button too.
    //
    // A minimum width rather than a size, which is where it parts from the
    // button: a button's square arm holds one glyph and a badge's holds a
    // number, so one digit has to draw a square and three have to draw a pill.
    // That is the avatar mark's arrangement, and for the reason it gives — a
    // count that clipped at two digits would be a count that lies. The padding
    // left is what keeps three digits off the ends once it does grow.
    //
    // `tabular-nums` for the same reason the mark takes it: a number that is
    // being counted down should not shuffle its own box on the way.
    ->add($square ? 'justify-center [:where(&)]:tabular-nums' : null)
    ->add($square ? match ($size) {
        'xs' => '[:where(&)]:gap-1 [:where(&)]:h-4 [:where(&)]:min-w-4 [:where(&)]:px-0.5 [:where(&)]:text-2xs',
        'sm' => '[:where(&)]:gap-1 [:where(&)]:h-5 [:where(&)]:min-w-5 [:where(&)]:px-0.5 [:where(&)]:text-2xs',
        'lg' => '[:where(&)]:gap-1.5 [:where(&)]:h-7 [:where(&)]:min-w-7 [:where(&)]:px-1.5 [:where(&)]:text-sm',
        default => '[:where(&)]:gap-1.5 [:where(&)]:h-6 [:where(&)]:min-w-6 [:where(&)]:px-1 [:where(&)]:text-xs',
    } : null)

    // `inline-flex` makes a badge an atomic box on its line, so a caller
    // dropping one into running text gets that line's height grown to fit the
    // badge's padding — 24px of badge in a 21px line, say. `inset` cancels
    // exactly the padding added above with an equal negative margin, so the
    // badge keeps its size but stops pushing its own line apart from the
    // ones around it. `xs` has no vertical padding to cancel.
    //
    // The same margins hold for a square badge, which has no vertical padding
    // either: what they cancel is the badge standing taller than the line box,
    // and that difference is the same however the height was arrived at.
    ->add($inset ? match ($size) {
        'xs' => null,
        'sm' => '[:where(&)]:-my-0.5',
        default => '[:where(&)]:-my-1',
    } : null)

    // The same tone variables the button reads, so a badge and a button given
    // the same colour agree without either knowing about the other.
    ->add(match ($variant) {
        'solid' => 'bg-[var(--shape-tone)] text-[var(--shape-tone-fg)]',
        'outline' => 'border border-[var(--shape-tone-border)] text-[var(--shape-tone-ink)]',
        default => 'bg-[var(--shape-tone-tint)] text-[var(--shape-tone-ink)]',
    })

    // All a control adds, because everything else about it is already drawn
    // above. The focus ring is the button's exactly — two pixels, offset two,
    // `--shape-ring` — since a control that focused differently from every
    // other control in the library would be reporting a difference that is not
    // there. `disabled` and `aria-disabled` both dim it and stop it taking a
    // pointer, for the reason the button carries both: an anchor cannot be
    // disabled.
    ->add($control ? 'select-none transition-colors duration-100' : null)
    ->add($control ? 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]' : null)
    ->add($control ? 'disabled:pointer-events-none disabled:opacity-50' : null)
    ->add($control ? 'aria-disabled:pointer-events-none aria-disabled:opacity-50' : null)

    // It repaints on hover rather than dimming, which is where it parts from the
    // avatar: an avatar's paint is what the avatar means, and a badge's is the
    // same chrome the button paints out of the same variables. So the hover is
    // the button's too — a louder version of the same paint, one step per
    // variant. `outline` has no fill to lift, so it takes the tint the ghost
    // button takes.
    ->add($control ? match ($variant) {
        'solid' => 'hover:bg-[var(--shape-tone-hover)]',
        'outline' => 'hover:bg-[var(--shape-tone-tint)]',
        default => 'hover:bg-[var(--shape-tone-tint-hover)]',
    } : null)

    // On is the fill the tone would have taken as a `solid` badge, because that
    // is the loudest a badge gets and a chip that is on should be the one you
    // see first. `solid` has nowhere louder to go, so it takes the step it uses
    // for hover instead — which is why a bar of toggles reads best built out of
    // the default `subtle` or out of `outline`.
    //
    // Written as `aria-*` variants rather than as a branch on `$selected`, so
    // the paint cannot disagree with what the badge announces: the attribute
    // that carries the state to a screen reader is the same one that colours it.
    // Once per spelling, and only the spelling this element can carry — a link
    // that wore the button's variants would be carrying rules nothing on it can
    // ever match.
    //
    // They sit at the same specificity as the plain hover above, which costs
    // nothing: a chip that is not hovered matches only these, and one that is
    // matches the three-part rule below it and wins there.
    ->add($selected !== null && $control === 'button' ? match ($variant) {
        'solid' => 'aria-pressed:bg-[var(--shape-tone-hover)]',
        'outline' => 'aria-pressed:border-[var(--shape-tone)] aria-pressed:bg-[var(--shape-tone)] aria-pressed:text-[var(--shape-tone-fg)]',
        default => 'aria-pressed:bg-[var(--shape-tone)] aria-pressed:text-[var(--shape-tone-fg)]',
    } : null)
    ->add($selected !== null && $control === 'button' ? 'aria-pressed:hover:bg-[var(--shape-tone-hover)]' : null)

    ->add($selected !== null && $control !== 'button' ? match ($variant) {
        'solid' => 'aria-[current=page]:bg-[var(--shape-tone-hover)]',
        'outline' => 'aria-[current=page]:border-[var(--shape-tone)] aria-[current=page]:bg-[var(--shape-tone)] aria-[current=page]:text-[var(--shape-tone-fg)]',
        default => 'aria-[current=page]:bg-[var(--shape-tone)] aria-[current=page]:text-[var(--shape-tone-fg)]',
    } : null)
    ->add($selected !== null && $control !== 'button' ? 'aria-[current=page]:hover:bg-[var(--shape-tone-hover)]' : null);
@endphp

<x-shape::badge.element
    :as="$control"
    {{ $attributes->merge($state)->class($classes) }}
    data-shape-badge
    data-shape-variant="{{ $variant }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
>
    {{--
        Never rely on colour alone. Every state resolves a glyph of its own, so
        a badge stays readable in greyscale and to anyone who can't separate
        the hues. Opting out is `:icon="false"`; forgetting isn't possible.
        `brand` and `accent` resolve nothing: they are emphasis rather than
        states, and `info` is the state the brand used to stand in for.

        Written as a static tag per tone rather than `<x-shape::icon :name=".." />`
        so that the four built-in states never touch `<x-dynamic-component>`,
        which resolves through a temp file Blade writes and reads back on first
        use. A caller's own `icon` name still needs that dynamic path — there is
        no fixed set of those to special-case against.
    --}}
    @if ($icon !== false)
        @if ($icon)
            <x-shape::icon :name="$icon" :size="$iconSize" />
        @elseif ($dot)
            {{-- One slot, and this is the ladder that fills it: the drawing a
                 call site named, then the dot it asked for, then the glyph the
                 tone resolves, then nothing. `icon` beats `dot` because naming
                 a drawing is the more specific of the two instructions, and
                 `:icon="false"` empties the slot rather than only the glyph —
                 it is the one prop that says what the badge wears in front of
                 its label. --}}
            <span class="{{ $dotClasses }}" aria-hidden="true"></span>
        @elseif ($tone === 'success')
            <x-shape::icon.shape-success :size="$iconSize" />
        @elseif ($tone === 'danger')
            <x-shape::icon.shape-danger :size="$iconSize" />
        @elseif ($tone === 'warning')
            <x-shape::icon.shape-warning :size="$iconSize" />
        @elseif ($tone === 'info')
            <x-shape::icon.shape-info :size="$iconSize" />
        @endif
    @endif

    {{--
        The label is wrapped rather than left as a text node, so that a badge
        given a width narrower than its text has something to truncate. Text
        sitting straight in a flex container is an anonymous flex item and
        `text-overflow` does not reach into one — a `max-w-*` and a `truncate`
        on the badge itself clip mid-word with no ellipsis at all, and eat the
        right padding while they do it. The ellipsis has to sit on an element,
        and this is the only place one can be put: nothing a call site passes
        reaches inside a badge.

        It costs nothing until something is capped. `min-w-0` only matters once
        a flex item is asked to be narrower than its text, and a badge sized by
        its own content measures the same with the span as without — so a call
        site passes a `max-w-*` of its own and gets an ellipsis for it, and one
        that passes none is the badge it always was. The icons and the x are all
        `shrink-0`, so the label is the only part that gives.

        `empty:hidden` is for the badge with no label at all — an icon on its
        own, a count that resolved to nothing. An empty flex item still takes a
        gap on either side of it, so the span has to leave the layout rather
        than measure zero. It is written on one line for the same reason: a
        newline inside the tag is a text node, and a span with a text node in
        it is not `:empty`.

        `max-w-*` above rather than a width that exists, which is a rule for
        every comment in these files: an application points Tailwind at this
        directory, so a class named in prose is a class compiled into that
        application's stylesheet whether or not anything renders it.
    --}}
    <span class="min-w-0 truncate empty:hidden">{{ $label }}</span>

    {{--
        Nothing resolves one of these from the tone: the state glyph belongs in
        front of the label, and a second copy of it behind would say the same
        thing twice. `icon-trailing` is a caller's own drawing — a chevron on a
        badge that opens something — so it is always the dynamic path.
    --}}
    @if ($iconTrailing)
        <x-shape::icon :name="$iconTrailing" :size="$iconSize" />
    @endif

    {{--
        The x, last, after anything the call site put there.

        `data-shape-dismiss` is the library's one dismissal primitive — the same
        attribute the alert and the toast carry, read by the same delegated
        listener in shape.js, which removes the nearest of the three. A badge is
        not a reason for a second mechanism.

        A bare `<button>` where the alert and the toast reach for
        `<x-shape::button>`, for the two reasons element.blade.php gives about
        the avatar's file. The registry ejects a component with everything it
        composes, and a badge that composed the button would pull the button
        into an application that asked for a badge. And the button's box does
        not fit in here anyway: an `xs` badge is sixteen pixels tall, shorter
        than the smallest button, so the control would end up setting the height
        of the thing it sits inside.

        It has no padding of its own for that same reason — at `xs` the badge is
        exactly one `xs` icon tall, so a puck any larger than the glyph would
        break the height scale at the top of this file. The hover puck is the
        glyph's own box, and the gap above already sets it off from the label.

        It takes no colour either. Preflight gives a button `color: inherit`, so
        the x arrives painted in whatever ink the variant resolved — the tone's
        on a tint, the readable foreground on a fill — and the hover is that
        same ink at fifteen percent, which is the recipe the dismiss control in
        shape.css uses against a surface. It reads `currentColor` instead of
        `--shape-fg` because a badge publishes a tone and no surface. One
        element, right on all three variants, branching on none of them.

        The ring is `currentColor` too, and that is the same failure shape.css
        documents for the alert's x on a solid fill: `--shape-ring` is brand-600,
        so a solid `brand` badge would draw a ring nobody can see. The inherited
        ink is the one colour already proven readable against this fill. Offset
        inward, because two pixels of outward offset on a sixteen-pixel box puts
        the ring outside the badge.

        `label` goes in the accessible name. A filter bar of twenty chips all
        announced "Dismiss" names the button and not the thing it removes. It is
        interpolated exactly as it is in the text above — no branch on it, so a
        dismissible badge folds with a label that differs on every row.
    --}}
    @if ($dismissible)
        <button type="button"
            class="inline-flex shrink-0 items-center justify-center rounded-full transition-colors duration-100 hover:bg-current/15 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-current"
            aria-label="Dismiss {{ $label }}"
            data-shape-dismiss=""
        >
            <x-shape::icon.shape-close :size="$iconSize" />
        </button>
    @endif
</x-shape::badge.element>
