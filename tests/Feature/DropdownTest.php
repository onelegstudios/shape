<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is a popover with menu semantics', function () {
    $html = Blade::render('<x-shape::dropdown name="row-actions">Items</x-shape::dropdown>');

    expect($html)
        ->toContain('popover')
        ->toContain('id="row-actions"')
        ->toContain('role="menu"')
        ->toContain('data-shape-menu')
        ->toContain('data-shape-dropdown');
});

it('is findable from its trigger, which is all the positioner needs', function () {
    // Placement is JavaScript's: shape.js finds the trigger by the popover's own
    // id and measures it. There is no anchor name in the markup, because CSS
    // anchor positioning ships in halves and a half-supported declarative path
    // is worse than one path that always runs.
    $html = Blade::render(<<<'BLADE'
    <x-shape::dropdown.trigger for="row-actions">Actions</x-shape::dropdown.trigger>
    <x-shape::dropdown name="row-actions">Items</x-shape::dropdown>
    BLADE);

    expect($html)
        ->toContain('popovertarget="row-actions"')
        ->toContain('id="row-actions"')
        ->toContain('data-shape-placement="bottom-start"')
        ->not->toContain('anchor-name');
});

it('opens from popovertarget rather than from a click handler', function () {
    expect(Blade::render('<x-shape::dropdown.trigger for="menu">Actions</x-shape::dropdown.trigger>'))
        ->toContain('popovertarget="menu"')
        ->not->toContain('onclick')
        ->not->toContain('x-on:click');
});

it('announces a menu, which is the promise arrow keys will work', function () {
    expect(Blade::render('<x-shape::dropdown.trigger for="menu">Actions</x-shape::dropdown.trigger>'))
        ->toContain('aria-haspopup="menu"')
        ->toContain('aria-expanded="false"')
        ->toContain('aria-controls="menu"');
});

it('announces a dialog for a plain popover', function () {
    expect(Blade::render('<x-shape::popover.trigger for="usage">Usage</x-shape::popover.trigger>'))
        ->toContain('aria-haspopup="dialog"');
});

it('renders an item as a button, and as a link when it is one', function () {
    expect(Blade::render('<x-shape::dropdown.item>Approve</x-shape::dropdown.item>'))
        ->toContain('<button')
        ->toContain('role="menuitem"')
        ->toContain('data-shape-menu-item');

    // Middle-click, open-in-new-tab and the status bar all work for a link and
    // none of them work for a button pretending to be one.
    expect(Blade::render('<x-shape::dropdown.item href="/invoices/1">Open</x-shape::dropdown.item>'))
        ->toContain('<a ')
        ->toContain('href="/invoices/1"')
        ->toContain('role="menuitem"');
});

it('carries a destructive item\'s colour on the tone attribute rather than in a second variant table', function () {
    expect(Blade::render('<x-shape::dropdown.item color="danger" icon="shape-trash">Delete</x-shape::dropdown.item>'))
        ->toContain('data-shape-tone="danger"')
        ->toContain('data-shape-icon');
});

it('keeps the menu tighter than a popover through a prop, not a class override', function () {
    // Two package defaults for one property both carry zero specificity, so
    // which wins would be decided by Tailwind's ordering rather than by intent.
    expect(Blade::render('<x-shape::dropdown name="m">Items</x-shape::dropdown>'))
        ->toContain('[:where(&amp;)]:p-1.5')
        ->not->toContain('[:where(&amp;)]:p-3');

    expect(Blade::render('<x-shape::popover name="p">Anything</x-shape::popover>'))
        ->toContain('[:where(&amp;)]:p-3')
        ->not->toContain('[:where(&amp;)]:p-1.5');
});

it('sets no z-index, because a popover is in the top layer', function () {
    expect(Blade::render('<x-shape::dropdown name="m">Items</x-shape::dropdown>'))
        ->not->toContain('z-');
});
