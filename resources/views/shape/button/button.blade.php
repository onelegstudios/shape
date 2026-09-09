@blaze(fold: true, safe: ['tone'])

{{--
    `icon-trailing` and `icon-size` arrive as the camelCased props below —
    Blade does that conversion itself, so there is no attribute-plucking here.

    `border` is the alert's prop, and it means the same thing here. It is off by
    default, because three of the four variants already have a boundary — a
    fill, or in `outline`'s case the neutral edge it is drawn with — and on, it
    draws the tone's own edge instead of a grey one. It earns its place where a
    button has to hold its own against a busy page, or sit next to something
    already drawn with an edge of its own.

    Which step of the tone follows what the edge sits against, exactly as it
    does on the alert. `subtle` and `outline` take
    `--shape-tone-border-strong` — the tone's answer to the neutral
    `--shape-tone-border` an outlined thing takes by default, far enough along
    the ramp to read as an edge someone chose. `primary` cannot use it: a pale
    edge on a saturated fill reads as a highlight, so it takes
    `--shape-tone-hover`, the step past the fill, which is darker in light mode
    and brighter in dark rather than a fixed darkening that would invert
    between them.

    `ghost` draws the edge under the pointer only, arriving with the tint it
    already paints there, so the button stays unpainted at rest. The border is
    reserved as a transparent one, which is what keeps the label from moving a
    pixel when it lands; `transition-colors` on the root already carries
    `border-color`, so it fades in with the fill.

    `outline` is the one variant the prop adds no border to — it has one
    already — so there it only decides whether that border carries the tone or
    the grey. Which is why that arm's border moved out of the fill match and
    into the one under it.
--}}

@props([
    'variant' => 'outline',
    'tone' => null,
    'size' => 'base',
    'type' => 'button',
    'icon' => null,
    'iconTrailing' => null,
    'iconSize' => 'sm',
    'square' => false,
    'border' => false,
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
        default => 'bg-[var(--shape-tone-surface)] text-[var(--shape-tone-ink)] [:where(&)]:shadow-sm hover:bg-[var(--shape-tone-surface-hover)]',
    })

    // The edge, arm for arm with the fill above, so the two are read together.
    // Three of the four draw nothing until `border` asks; `outline` is the
    // default here as it is above, which keeps the neutral edge on anything
    // unrecognised rather than dropping it.
    //
    // Every colour is a step of `--shape-tone`, not a border palette of its
    // own, so an edge follows a retheme with the fill it bounds.
    ->add(match ($variant) {
        'primary' => $border ? 'border border-[var(--shape-tone-hover)]' : null,
        'subtle' => $border ? 'border border-[var(--shape-tone-border-strong)]' : null,
        'ghost' => $border ? 'border border-transparent hover:border-[var(--shape-tone-border-strong)]' : null,
        default => $border
            ? 'border border-[var(--shape-tone-border-strong)]'
            : 'border border-[var(--shape-tone-border)]',
    });
@endphp

<x-shape::button.element
    :as="$as"
    :type="$type"
    {{ $attributes->class($classes) }}
    data-shape-button=""
    data-shape-variant="{{ $variant }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
>
    @if ($icon)
        <x-shape::icon :name="$icon" :size="$iconSize" />
    @endif

    {{ $slot }}

    @if ($iconTrailing)
        <x-shape::icon :name="$iconTrailing" :size="$iconSize" />
    @endif
</x-shape::button.element>
