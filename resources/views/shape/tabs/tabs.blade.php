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

    `size` is the strip's and not the tab's, which is the one prop here that is
    deliberately not where you might reach for it. Tabs in one strip are one
    control — a strip of tabs at three sizes is not a thing anyone is trying to
    build — so the word is said once, where the strip is, rather than repeated on
    every tab and got wrong on the fourth.

    It reaches them as descendant utilities rather than as a prop handed down.
    Every measurement a tab draws itself with carries the library's zero
    specificity, so a rule from here outranks it without `!important` and without
    the strip knowing anything about the tab beyond its being a child. `@aware`
    would have been the other way, and it walks the whole ancestor stack: a
    `size` read that way would answer to any component above the strip that
    happened to have one, which for a word this common is not a risk worth
    running. `field-name` gets away with it because nothing else is called that.

    `base` emits none of them and leaves the tab's own defaults standing, so the
    strip almost every page has renders exactly the markup it did before the prop
    existed.

    `orientation` selects the class set and tells the script which arrow keys to
    answer, so it branches and is not safe. `label` is only interpolated, and a
    tab strip labelled from a variable still folds.
--}}

@props([
    'as' => null,
    'label' => null,
    'orientation' => 'horizontal',
    'size' => 'base',
])

@php
$classes = Shape::classes()
    ->add('flex')
    ->add($orientation === 'vertical' ? 'flex-col items-stretch' : 'items-center')

    // The gap between tabs and the tabs themselves, in one arm, because they are
    // one decision: a strip set tighter wants its tabs tighter with it.
    //
    // The glyph is named too. An icon's size class is zero specificity like
    // everything else here, so the strip can reach past the `icon-size` a tab
    // resolved for itself — which is what stops a 20px mark sitting in a 24px
    // tab at `xs`.
    ->add(match ($size) {
        'xs' => '[:where(&)]:gap-0.5 [&>*]:gap-1 [&>*]:px-2 [&>*]:py-1 [&>*]:text-xs [&>*>svg]:size-4',
        'sm' => '[:where(&)]:gap-0.5 [&>*]:gap-1.5 [&>*]:px-2.5 [&>*]:py-1 [&>*]:text-sm [&>*>svg]:size-4',
        'lg' => '[:where(&)]:gap-1.5 [&>*]:gap-2.5 [&>*]:px-4 [&>*]:py-2 [&>*]:text-base [&>*>svg]:size-6',
        'xl' => '[:where(&)]:gap-2 [&>*]:gap-3 [&>*]:px-5 [&>*]:py-2.5 [&>*]:text-lg [&>*>svg]:size-6',
        default => '[:where(&)]:gap-1',
    });
@endphp

<?php switch ($as): case ('nav'): ?>
<nav {{ $attributes->class($classes) }} aria-label="{{ $label }}" data-shape-tabs data-shape-size="{{ $size }}">{{ $slot }}</nav>
<?php break; default: ?>
<div
    {{ $attributes->class($classes) }}
    role="tablist"
    aria-label="{{ $label }}"
    aria-orientation="{{ $orientation }}"
    data-shape-tabs
    data-shape-tablist
    data-shape-size="{{ $size }}"
>{{ $slot }}</div>
<?php endswitch; ?>
