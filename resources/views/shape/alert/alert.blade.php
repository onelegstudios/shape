@blaze(fold: true, safe: ['heading'])

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

    Which is why `variant` and the surface are decided together. `subtle`,
    `outline` and `ghost` all put the tone's ink on a light background, so all
    three publish `tint`; `solid` fills with the tone and publishes `solid`,
    where the readable foreground is the tone's own `-fg` instead. Nothing is
    passed down in either case — the nested text finds it.

    `tone` is branched on to resolve the glyph, exactly as the badge does, so it
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
    'tone' => null,
    'variant' => 'subtle',
    'heading' => null,
    'icon' => null,
    'iconSize' => 'sm',
    'dismissible' => false,
])

@php
// Never colour alone — every state tone resolves a glyph, so an alert stays
// readable in greyscale. `:icon="false"` opts out; forgetting isn't possible.
//
// `brand` and `accent` are not among them. Both are emphasis: the brand is the
// product's colour, which an application is free to move, and the accent is the
// one kept for "look here". A glyph on either would make it a state under
// another name — the one `info` now is, in a blue that stays blue whatever the
// brand becomes.
$glyph = $icon ?? match ($tone) {
    'success' => 'shape-success',
    'danger' => 'shape-danger',
    'warning' => 'shape-warning',
    'info' => 'shape-info',
    default => null,
};

// Variant is how loud the alert is; tone is what it means. The two never
// multiply into a class matrix, because every arm below paints with the same
// tone variables and the foreground comes from the surface rather than from
// here — one `text-` class serves all four.
//
// The badge's set, and the badge's recipe: `outline` takes the neutral border
// and leaves the colour to the ink and the glyph, which is what keeps `brand`
// and `accent` — the two tones that draw no glyph — visibly toned. `ghost` is
// that arm with the border dropped: the quietest of the four, for a message
// that belongs in the flow of a form or a panel that is already boxed.
//
// Only the paint changes. The padding above stays with it, so swapping a
// variant never moves the text, and the dismiss control's negative margins go
// on pulling it back into the same corner.
//
// Ghost is the one arm that paints on hover, and it resolves to `subtle`: the
// tint is the fill this alert would have had at rest, so pointing at one shows
// its bounds — the block a dismiss control belongs to — rather than promising
// a click. It is the ghost button's recipe and the same variable, which is why
// the two agree without either knowing about the other. The transition rides
// in this arm rather than on the root, since the other three never move.
$classes = Shape::classes()
    ->add('flex items-start')
    ->add('[:where(&)]:gap-3 [:where(&)]:rounded-shape [:where(&)]:p-4')
    ->add(match ($variant) {
        'solid' => '[:where(&)]:bg-[var(--shape-tone)]',
        'outline' => '[:where(&)]:border [:where(&)]:border-[var(--shape-tone-border)]',
        'ghost' => 'transition-colors duration-100 hover:bg-[var(--shape-tone-tint)]',
        default => '[:where(&)]:bg-[var(--shape-tone-tint)]',
    })
    ->add('[:where(&)]:text-[color:var(--shape-fg)]');
@endphp

<div
    {{ $attributes->class($classes) }}
    data-shape-alert
    data-shape-variant="{{ $variant }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
    data-shape-surface="{{ $variant === 'solid' ? 'solid' : 'tint' }}"
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
             this is the one place feedback needs a handler of its own.

             `data-shape-dismiss` is also what the paint rule at the foot of
             shape.css keys on. A button declares a `data-shape-tone` of its own,
             so left alone this control resolves the neutral ink and the neutral
             tint whatever the tone above it; the rule hands it the surface's
             foreground instead. Nothing is passed down here either — the
             attribute the listener already needed is the whole hook. --}}
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
