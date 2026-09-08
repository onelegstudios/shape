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
--}}

@props([
    'label' => null,
    'tone' => null,
    'variant' => 'subtle',
    'size' => 'base',
    'icon' => null,
    'iconTrailing' => null,
    'iconSize' => 'xs',
    'inset' => false,
    'dismissible' => false,
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

$classes = Shape::classes()
    ->add('inline-flex items-center whitespace-nowrap align-middle')
    ->add('[:where(&)]:rounded-shape [:where(&)]:font-medium')

    // Four heights: 16px, 20px, 24px, 28px. The vertical padding is what moves
    // them apart — `text-2xs` and `text-xs` share a 1rem line box, so a scale
    // that only changed the type size and the side padding would render two
    // badges the same height and differ by two pixels of gutter.
    ->add(match ($size) {
        'xs' => '[:where(&)]:gap-1 [:where(&)]:px-1.5 [:where(&)]:py-0 [:where(&)]:text-2xs',
        'sm' => '[:where(&)]:gap-1 [:where(&)]:px-2 [:where(&)]:py-0.5 [:where(&)]:text-2xs',
        'lg' => '[:where(&)]:gap-1.5 [:where(&)]:px-3 [:where(&)]:py-1 [:where(&)]:text-sm',
        default => '[:where(&)]:gap-1.5 [:where(&)]:px-2.5 [:where(&)]:py-1 [:where(&)]:text-xs',
    })

    // `inline-flex` makes a badge an atomic box on its line, so a caller
    // dropping one into running text gets that line's height grown to fit the
    // badge's padding — 24px of badge in a 21px line, say. `inset` cancels
    // exactly the padding added above with an equal negative margin, so the
    // badge keeps its size but stops pushing its own line apart from the
    // ones around it. `xs` has no vertical padding to cancel.
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
    } : null);
@endphp

<x-shape::badge.element
    :as="$control"
    {{ $attributes->class($classes) }}
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

    {{ $label }}

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
