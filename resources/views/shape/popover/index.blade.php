@blaze(fold: true, safe: ['name'])

{{--
    A panel anchored to the thing that opened it.

    `popover` — the bare attribute, which means the auto kind — is what supplies
    light dismiss, Escape, and the top layer. Being in the top layer is what
    stops the oldest bug in this category: a menu clipped by the `overflow:
    hidden` of a card three ancestors up, or covered by a sticky header with a
    higher z-index. There is no z-index anywhere in this component, because
    there is no longer a stack to compete in.

    Placement is a data attribute read by CSS anchor positioning, with the
    fallbacks that flip it when the viewport runs out. Where anchor positioning
    is unsupported, shape.js positions it from that same attribute.

    Like the modal, `name` is required: an id generated inside a folded component
    is generated once, at compile time, and every instance on the page would
    share it.
--}}

@props([
    'name',
    'placement' => 'bottom-start',
    'padding' => 'base',
    'role' => null,
])

@php
$classes = Shape::classes()
    ->add('max-w-[min(22rem,calc(100vw-2rem))] overflow-y-auto')

    // Padding is a prop rather than something a caller overrides with a class,
    // and the menu below is why. Two package defaults for the same property both
    // carry zero specificity, so which of them wins is decided by Tailwind's own
    // ordering of the utilities rather than by which component is more specific
    // about its own layout. A `match` emits one class and the question never
    // arises.
    ->add(match ($padding) {
        'tight' => '[:where(&)]:min-w-48 [:where(&)]:gap-0.5 [:where(&)]:p-1.5',
        default => '[:where(&)]:min-w-52 [:where(&)]:gap-2 [:where(&)]:p-3',
    })

    ->add('[:where(&)]:rounded-shape [:where(&)]:shadow-lg')
    ->add('[:where(&)]:border [:where(&)]:border-shape-200 dark:[:where(&)]:border-shape-800')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]');
@endphp

<div
    id="{{ $name }}"
    popover
    @if ($role) role="{{ $role }}" @endif
    {{ $attributes->class($classes)->merge(['style' => "position-anchor: --shape-{$name};"]) }}
    data-shape-popover
    data-shape-placement="{{ $placement }}"
>{{ $slot }}</div>
