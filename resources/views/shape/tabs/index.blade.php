@blaze(fold: true, safe: ['label'])

{{--
    Two containers behind one component, and the difference between them is not
    cosmetic.

    A row of links that navigate to other pages is navigation. Giving it
    `role="tablist"` tells assistive technology that arrow keys move between
    these, that exactly one is selected, and that each controls a panel in this
    document — three claims that are all false of a link. So `as="nav"` renders a
    `<nav>` with no role and no `data-shape-tablist`, which is also what keeps
    shape.js from binding arrow keys to something that should answer to Tab.

    The default is the real widget: `role="tablist"`, panels found by id, and the
    keyboard behaviour in the script.

    `orientation` selects the class set and tells the script which arrow keys to
    answer, so it branches and is not safe. `label` is only interpolated, and a
    tab strip labelled from a variable still folds.
--}}

@props([
    'as' => null,
    'label' => null,
    'orientation' => 'horizontal',
])

@php
$classes = Shape::classes()
    ->add('flex [:where(&)]:gap-1')
    ->add($orientation === 'vertical' ? 'flex-col items-stretch' : 'items-center');
@endphp

<?php switch ($as): case ('nav'): ?>
<nav {{ $attributes->class($classes) }} aria-label="{{ $label }}" data-shape-tabs>{{ $slot }}</nav>
<?php break; default: ?>
<div
    {{ $attributes->class($classes) }}
    role="tablist"
    aria-label="{{ $label }}"
    aria-orientation="{{ $orientation }}"
    data-shape-tabs
    data-shape-tablist
>{{ $slot }}</div>
<?php endswitch; ?>
