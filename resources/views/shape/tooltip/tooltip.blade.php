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
--}}

@props([
    'name',
    'text',
    'placement' => 'top',
])

@php
$classes = Shape::classes()
    ->add('pointer-events-none w-max max-w-2xs')
    ->add('[:where(&)]:rounded-shape [:where(&)]:px-2 [:where(&)]:py-1 [:where(&)]:text-xs [:where(&)]:font-medium')
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
>{{ $text }}</div>
