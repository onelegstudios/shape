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
    'as' => null,
])

@php
// A badge is a control when it is asked to be one, and an `href` asks without
// saying so — the avatar, the tab and the menu item resolve their own element
// the same way. Middle-click, "open in new tab" and the status bar all work for
// a link and none of them work for a button pretending to be one, and `as` wins
// where a call site wants a control that happens to carry an `href`.
$control = $as ?? ($attributes->has('href') ? 'a' : null);

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
</x-shape::badge.element>
