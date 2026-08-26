@blaze(fold: true, safe: ['name', 'as'])

{{--
    The wrapper that makes a label, a control and an error message one thing.

    A field states the name once. Everything inside it reads that name back with
    `@aware` — the label for its `for`, the control for its `id` and `name`, the
    description for the id it is referenced by, the error for the key it looks
    up. Passing it down as props instead would mean stating it four times and
    getting it wrong once.

    The field owns the space between its children, the way the card does. Nothing
    inside sets its own outer margin, so there is never a question about which
    element a gap belongs to.

    A disabled control dims its own label through `:has()`. That is a sibling
    coordinating with a sibling through CSS, with nothing passed down and nothing
    to keep in sync — the alternative is a `disabled` prop plumbed into three
    components, all of which then stop folding when it is bound dynamically.

    `as="fieldset"` is how a radio or checkbox group becomes a real group: a
    `<fieldset>` with a `<legend>` for its accessible name. The reset classes are
    unconditional so that `as` stays a pass-through prop — a `<div>` has no
    border or padding to remove, so applying them costs nothing and keeps
    `:as="$grouped ? 'fieldset' : 'div'"` on the fold path.
--}}

@props([
    'name' => null,
    'as' => 'div',
])

@php
$classes = Shape::classes()
    ->add('flex flex-col')
    ->add('[:where(&)]:gap-1.5')

    // `<fieldset>` arrives with a border, padding and a min-width the layout
    // does not want. `min-w-0` is the load-bearing one: without it a fieldset
    // refuses to shrink below its content and breaks any flex parent.
    ->add('min-w-0 [:where(&)]:border-0 [:where(&)]:p-0')

    // Dim the label of a disabled control, and the copy that explains it.
    //
    // Direct children only, and deliberately so. A checkbox carries its own
    // label inside itself; matching descendants would mean one disabled radio
    // in a group dimming the labels of the other four.
    ->add('[&:has(>[data-shape-control]:disabled)>[data-shape-label]]:opacity-50')
    ->add('[&:has(>[data-shape-control]:disabled)>[data-shape-description]]:opacity-50');
@endphp

<{{ $as }} {{ $attributes->class($classes) }} data-shape-field>{{ $slot }}</{{ $as }}>
