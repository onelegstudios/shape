@blaze(fold: true)

{{--
    A surface, not a box.

    Separation comes from the surface shift and a resting elevation, so there is
    no border by default — `border` is opt-in for the cases where a card sits on
    a surface too close to its own to read against. The elevation itself is
    `shadow`, opt-out for a card sitting inside a surface that already reads as
    raised, where a second shadow would just be noise.

    A card is the parent of whatever it contains, so it owns the space between
    its children. Nothing inside a card sets its own outer margin; that is what
    keeps "which element does this gap belong to" from ever becoming a question.

    Header and footer are separate components rather than named slots. Deciding
    whether a slot has content is a runtime question, and asking it would drop
    this component off the fold path for a convenience the call site can express
    perfectly well on its own.

    A card is pressable in one of two ways. `href` (or `as`) makes the whole
    card the control, which is right when it holds nothing else that can be
    pressed — an `<a>` or a `<button>` with a control inside it is markup the
    parser rewrites. A card that also holds buttons puts a `card.link` inside
    it instead: the link stretches over the card, and every other control in
    it is lifted above the stretch so it stays pressable.

    Either way the card answers the pointer with a tint rather than a colour of
    its own. The tint is a pseudo-element laid under the card's content, so it
    shifts whatever background the caller gave the card, in either theme, and
    never sets a property a caller class is also setting. That is why every
    card is positioned and isolated: the tint and the stretched link both need
    something to measure against, and the tint's negative layer needs a
    stacking context to stay inside.

    `orientation` lays the children in a row, for a picture beside the content
    rather than above it. `card.media` and `card.separator` read it off the
    card's attribute rather than taking a prop of their own.
--}}

@props([
    'padding' => 'base',
    'border' => false,
    'shadow' => true,
    'orientation' => 'vertical',
    'as' => null,
])

@php
// An `href` asks for the anchor without naming it, as it does on the button,
// the badge and the menu item; `as` wins where a call site means otherwise.
$control = $as ?? ($attributes->has('href') ? 'a' : null);

$classes = Shape::classes()
    ->add($orientation === 'horizontal' ? 'flex flex-row' : 'flex flex-col')
    ->add('relative isolate')
    ->add('[:where(&)]:rounded-shape-lg')
    ->add($shadow ? '[:where(&)]:shadow-sm' : '')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')

    // Padding and the gap between children move together. A roomier card wants
    // roomier gaps; letting them be set apart is how cards end up looking
    // accidentally cramped at one size and accidentally loose at another.
    //
    // The library's five steps, under the library's five words, with `none` on
    // the end of them. `none` is not a sixth step down from `xs`: it says the
    // card draws a surface and something inside it owns the inset — a table
    // bled to the edges, a picture — which is a different answer rather than a
    // smaller one, and it keeps the gap so the children still space themselves.
    //
    // The inset is also published as `--shape-card-inset`, which is how a
    // bleeding header or footer reaches the edge without knowing which arm the
    // card was called with.
    ->add(match ($padding) {
        'xs' => '[:where(&)]:gap-2 [:where(&)]:p-3 [:where(&)]:[--shape-card-inset:--spacing(3)]',
        'sm' => '[:where(&)]:gap-3 [:where(&)]:p-4 [:where(&)]:[--shape-card-inset:--spacing(4)]',
        'lg' => '[:where(&)]:gap-6 [:where(&)]:p-8 [:where(&)]:[--shape-card-inset:--spacing(8)]',
        'xl' => '[:where(&)]:gap-8 [:where(&)]:p-10 [:where(&)]:[--shape-card-inset:--spacing(10)]',
        'none' => '[:where(&)]:gap-4 [:where(&)]:[--shape-card-inset:0px]',
        default => '[:where(&)]:gap-4 [:where(&)]:p-6 [:where(&)]:[--shape-card-inset:--spacing(6)]',
    })

    ->add($border ? '[:where(&)]:border [:where(&)]:border-shape-200 dark:[:where(&)]:border-shape-800' : '')

    // The tint, at rest. `bg-current` makes it darken a light card and lighten
    // a dark one without a `dark:` arm.
    ->add('after:pointer-events-none after:absolute after:inset-0 after:-z-1 after:rounded-[inherit] after:bg-current after:opacity-0 after:transition-opacity after:duration-100')

    // The card as the control.
    ->add(match ($control) {
        'a', 'button' => 'cursor-pointer hover:after:opacity-[0.04] active:after:opacity-[0.07] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]',
        default => '',
    })

    // A `<button>` shrinks to its content and centres its text; a card does
    // neither.
    ->add($control === 'button' ? '[:where(&)]:w-full [:where(&)]:text-start' : '')

    // The card around a stretched `card.link`. Asked of the markup by `:has()`
    // rather than of a slot, so it stays on the fold path. The ring is drawn
    // here because the link's own box is only as big as its text.
    ->add('has-[[data-shape-card-link]]:cursor-pointer has-[[data-shape-card-link]]:hover:after:opacity-[0.04] has-[[data-shape-card-link]:active]:after:opacity-[0.07]')
    ->add('has-[[data-shape-card-link]:focus-visible]:outline-2 has-[[data-shape-card-link]:focus-visible]:outline-offset-2 has-[[data-shape-card-link]:focus-visible]:outline-[var(--shape-ring)]')

    // Every other control in that card sits above the stretch.
    ->add('[:where(&:has([data-shape-card-link])_:is(a,button,input,select,textarea,summary,label,[tabindex]):not([data-shape-card-link]))]:relative')
    ->add('[:where(&:has([data-shape-card-link])_:is(a,button,input,select,textarea,summary,label,[tabindex]):not([data-shape-card-link]))]:z-1');
@endphp

<x-shape::card.element
    :as="$control"
    {{ $attributes->class($classes) }}
    data-shape-card=""
    data-shape-padding="{{ $padding }}"
    data-shape-orientation="{{ $orientation }}"
>
    {{ $slot }}
</x-shape::card.element>
