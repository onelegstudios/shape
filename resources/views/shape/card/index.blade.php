@blaze(fold: true)

{{--
    A surface, not a box.

    Separation comes from the surface shift and a resting elevation, so there is
    no border by default — `border` is opt-in for the cases where a card sits on
    a surface too close to its own to read against.

    A card is the parent of whatever it contains, so it owns the space between
    its children. Nothing inside a card sets its own outer margin; that is what
    keeps "which element does this gap belong to" from ever becoming a question.

    Header and footer are separate components rather than named slots. Deciding
    whether a slot has content is a runtime question, and asking it would drop
    this component off the fold path for a convenience the call site can express
    perfectly well on its own.
--}}

@props([
    'padding' => 'base',
    'border' => false,
])

@php
$classes = Shape::classes()
    ->add('flex flex-col')
    ->add('[:where(&)]:rounded-shape-lg [:where(&)]:shadow-sm')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')

    // Padding and the gap between children move together. A roomier card wants
    // roomier gaps; letting them be set apart is how cards end up looking
    // accidentally cramped at one size and accidentally loose at another.
    ->add(match ($padding) {
        'sm' => '[:where(&)]:gap-3 [:where(&)]:p-4',
        'lg' => '[:where(&)]:gap-6 [:where(&)]:p-8',
        'none' => '[:where(&)]:gap-4',
        default => '[:where(&)]:gap-4 [:where(&)]:p-6',
    })

    ->add($border ? '[:where(&)]:border [:where(&)]:border-shape-200 dark:[:where(&)]:border-shape-800' : '');
@endphp

<div {{ $attributes->class($classes) }} data-shape-card data-shape-padding="{{ $padding }}">
    {{ $slot }}
</div>
