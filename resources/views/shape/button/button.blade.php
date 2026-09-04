@blaze(fold: true, safe: ['color'])

{{--
    `icon-trailing` and `icon-size` arrive as the camelCased props below —
    Blade does that conversion itself, so there is no attribute-plucking here.
--}}

@props([
    'variant' => 'outline',
    'color' => null,
    'size' => 'base',
    'type' => 'button',
    'icon' => null,
    'iconTrailing' => null,
    'iconSize' => 'sm',
    'square' => false,
    'as' => null,
])

@php
$classes = Shape::classes()
    ->add('inline-flex items-center justify-center gap-2 whitespace-nowrap select-none')
    ->add('transition-colors duration-100')
    ->add('focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]')
    ->add('disabled:pointer-events-none disabled:opacity-50')
    ->add('aria-disabled:pointer-events-none aria-disabled:opacity-50')

    // Zero-specificity defaults. A caller passing `class="rounded-full text-base"`
    // simply wins, with no !important and no class-merging utility.
    ->add('[:where(&)]:rounded-shape [:where(&)]:font-medium')

    ->add(match ($size) {
        'sm' => $square ? 'size-8' : 'h-8 gap-1.5 px-3',
        'lg' => $square ? 'size-12' : 'h-12 px-5',
        default => $square ? 'size-10' : 'h-10 px-4',
    })
    ->add(match ($size) {
        'lg' => '[:where(&)]:text-base',
        default => '[:where(&)]:text-sm',
    })

    // Variant is hierarchy — where this action sits in the pyramid of importance.
    // Colour is semantics, and it is applied through `data-shape-tone` instead of
    // a second match arm, so the two concerns never multiply into a class matrix.
    ->add(match ($variant) {
        'primary' => 'bg-[var(--shape-tone)] text-[var(--shape-tone-fg)] [:where(&)]:shadow-sm hover:bg-[var(--shape-tone-hover)]',
        'subtle' => 'bg-[var(--shape-tone-tint)] text-[var(--shape-tone-ink)] hover:bg-[var(--shape-tone-tint-hover)]',
        'ghost' => 'text-[var(--shape-tone-ink)] hover:bg-[var(--shape-tone-tint)]',
        default => 'border border-[var(--shape-tone-border)] bg-[var(--shape-tone-surface)] text-[var(--shape-tone-ink)] [:where(&)]:shadow-sm hover:bg-[var(--shape-tone-surface-hover)]',
    });
@endphp

<x-shape::button.element
    :as="$as"
    :type="$type"
    {{ $attributes->class($classes) }}
    data-shape-button=""
    data-shape-variant="{{ $variant }}"
    data-shape-tone="{{ $color ?? 'neutral' }}"
>
    @if ($icon)
        <x-shape::icon :name="$icon" :size="$iconSize" />
    @endif

    {{ $slot }}

    @if ($iconTrailing)
        <x-shape::icon :name="$iconTrailing" :size="$iconSize" />
    @endif
</x-shape::button.element>
