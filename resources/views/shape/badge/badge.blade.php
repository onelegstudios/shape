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
--}}

@props([
    'label' => null,
    'tone' => null,
    'variant' => 'subtle',
    'size' => 'base',
    'icon' => null,
    'iconTrailing' => null,
    'iconSize' => 'xs',
])

@php
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

    // The same tone variables the button reads, so a badge and a button given
    // the same colour agree without either knowing about the other.
    ->add(match ($variant) {
        'solid' => 'bg-[var(--shape-tone)] text-[var(--shape-tone-fg)]',
        'outline' => 'border border-[var(--shape-tone-border)] text-[var(--shape-tone-ink)]',
        default => 'bg-[var(--shape-tone-tint)] text-[var(--shape-tone-ink)]',
    });
@endphp

<span
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
</span>
