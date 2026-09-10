@blaze(fold: true, safe: ['name', 'text'])

{{--
    A short description of the control it wraps.

    The slot is the trigger — a button, a link, an icon — and `text` is the
    tooltip, which is the opposite arrangement to every other component here.
    It has to be: the tooltip is a sibling in the top layer, and the thing it
    describes is what the caller already has in hand.

    `popover="manual"` rather than the auto kind, because an auto popover
    light-dismisses on any click anywhere — including on the control it belongs
    to. Manual means shape.js decides, which is also where the open delay and the
    show-immediately-on-keyboard-focus rule live.

    `aria-describedby` is set by shape.js on the first focusable element inside
    the wrapper, rather than rendered on the wrapper here. A tooltip describes a
    control; a `<span>` around a control is not the thing a screen reader lands
    on.

    A tooltip is never the only place information appears. It is unreachable on
    touch, and it disappears the moment attention moves — put anything a person
    must read in the interface itself.

    `size` moves the type, the inset around it and the measure the text wraps at,
    which is the one of the three that is easy to forget: a tooltip set at 16px
    inside a 16rem box is four words a line, and a tooltip is read in one glance
    or not at all.
--}}

@props([
    'name',
    'text',
    'placement' => 'top',
    'size' => 'base',
])

@php
$classes = Shape::classes()
    ->add('pointer-events-none w-max')

    // Type, inset and measure together. `w-max` above caps at the measure rather
    // than filling it, so a short tooltip is short at every step and the width
    // named here is only the point at which a long one wraps.
    ->add(match ($size) {
        'xs' => 'max-w-2xs [:where(&)]:px-1.5 [:where(&)]:py-0.5 [:where(&)]:text-2xs',
        'sm' => 'max-w-2xs [:where(&)]:px-2 [:where(&)]:py-0.5 [:where(&)]:text-xs',
        'lg' => 'max-w-xs [:where(&)]:px-2.5 [:where(&)]:py-1.5 [:where(&)]:text-sm',
        'xl' => 'max-w-sm [:where(&)]:px-3 [:where(&)]:py-2 [:where(&)]:text-base',
        default => 'max-w-2xs [:where(&)]:px-2 [:where(&)]:py-1 [:where(&)]:text-xs',
    })

    ->add('[:where(&)]:rounded-shape [:where(&)]:font-medium')
    ->add('[:where(&)]:bg-shape-900 [:where(&)]:text-shape-50')
    ->add('dark:[:where(&)]:bg-shape-100 dark:[:where(&)]:text-shape-900')
    ->add('[:where(&)]:shadow-md');
@endphp

<span class="inline-flex" data-shape-tooltip-for="{{ $name }}">{{ $slot }}</span>

<div
    id="{{ $name }}"
    popover="manual"
    role="tooltip"
    {{ $attributes->class($classes) }}
    data-shape-popover
    data-shape-tooltip
    data-shape-placement="{{ $placement }}"
    data-shape-size="{{ $size }}"
>{{ $text }}</div>
