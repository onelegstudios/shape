@blaze(fold: true, memo: true, safe: ['label'])

{{--
    A label with a status attached.

    Prop-first and slotless on purpose. Badges are the highest-volume component
    in any table, and Blaze only memoizes self-closing calls — a `{{ $slot }}`
    here would trade the single most effective optimization available for a
    composition nobody needs inside a badge.

    `label` is interpolated and nothing more, so it is safe: a badge in a table
    folds even though its text differs on every row.

    `color` is not, and cannot be. The button passes its colour straight through
    to `data-shape-tone` and stays foldable; a badge branches on colour to
    resolve its icon, so a dynamic `:color` drops to the memo path. That path is
    only cheap while labels repeat — with a dynamic colour AND a label unique to
    each row, every call is a memo miss, which measures around sixty times the
    cost of folding. Keep the colour static where you can.
--}}

@props([
    'label' => null,
    'color' => null,
    'variant' => 'subtle',
    'size' => 'base',
    'icon' => null,
    'iconVariant' => 'micro',
])

@php
// Never rely on colour alone. Every state resolves a glyph of its own, so a
// badge stays readable in greyscale and to anyone who can't separate the hues.
// Opting out is `:icon="false"`; forgetting isn't possible.
$glyph = $icon ?? match ($color) {
    'success' => 'check-circle',
    'danger' => 'x-circle',
    'warning' => 'exclamation-triangle',
    'accent' => 'information-circle',
    default => null,
};

$classes = Shape::classes()
    ->add('inline-flex items-center whitespace-nowrap align-middle')
    ->add('[:where(&)]:rounded-shape [:where(&)]:font-medium')

    ->add(match ($size) {
        'sm' => '[:where(&)]:gap-1 [:where(&)]:px-1.5 [:where(&)]:py-0.5 [:where(&)]:text-2xs',
        default => '[:where(&)]:gap-1.5 [:where(&)]:px-2 [:where(&)]:py-0.5 [:where(&)]:text-xs',
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
    data-shape-tone="{{ $color ?? 'neutral' }}"
>
    @if ($glyph)
        <x-shape::icon :name="$glyph" :variant="$iconVariant" />
    @endif

    {{ $label }}
</span>
