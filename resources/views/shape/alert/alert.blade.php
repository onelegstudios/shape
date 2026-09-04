@blaze(fold: true, safe: ['heading'])

{{--
    A message that stays on the page, in the flow of the content it belongs to.
    Other libraries call this a callout; it is the same component.

    The toast beside it is the same idea sent from the server and dismissed on a
    timer. The distinction worth keeping is where the message lives: an alert is
    part of the page and survives it being read twice; a toast is an event and
    does not.

    Tone rides on `data-shape-tone`, as everywhere else, and the tint is the
    surface. `data-shape-surface="tint"` is the part that matters and is easy to
    miss: it republishes the tone's ink as this element's foreground contract, so
    a nested `<x-shape::text variant="muted">` reads a dialled-back version of the
    tone rather than a global grey. Grey text on a coloured background is the one
    thing the surface contract exists to make impossible.

    `color` is branched on to resolve the glyph, exactly as the badge does, so it
    is *not* declared safe here. The same prop is safe on the button, which only
    ever interpolates it. Whatever a component does with a value decides that.

    `heading` is interpolated and nothing more, so an alert whose title comes from
    a variable still folds.

    Not a live region. This is markup that was on the page when it loaded;
    announcing it would repeat what a screen reader is about to read anyway. The
    toaster carries the live regions, because that is where content arrives after
    the fact.
--}}

@props([
    'color' => null,
    'heading' => null,
    'icon' => null,
    'iconSize' => 'sm',
    'dismissible' => false,
])

@php
// Never colour alone — every tone resolves a glyph, so an alert stays readable
// in greyscale. `:icon="false"` opts out; forgetting isn't possible.
$glyph = $icon ?? match ($color) {
    'success' => 'shape-success',
    'danger' => 'shape-danger',
    'warning' => 'shape-warning',
    'accent' => 'shape-info',
    default => null,
};

$classes = Shape::classes()
    ->add('flex items-start')
    ->add('[:where(&)]:gap-3 [:where(&)]:rounded-shape [:where(&)]:p-4')
    ->add('[:where(&)]:bg-[var(--shape-tone-tint)]')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]');
@endphp

<div
    {{ $attributes->class($classes) }}
    data-shape-alert
    data-shape-tone="{{ $color ?? 'neutral' }}"
    data-shape-surface="tint"
>
    @if ($glyph)
        <x-shape::icon :name="$glyph" :size="$iconSize" class="mt-0.5" />
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
             this is the one place feedback needs a handler of its own. --}}
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
