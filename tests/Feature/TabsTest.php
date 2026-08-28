<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is a link when it navigates, with the claim a link makes', function () {
    // Middle-click, "open in new tab" and the status bar all work for a link and
    // none of them work for a button pretending to be one.
    $html = Blade::render('<x-shape::tabs.tab href="/billing" selected>Billing</x-shape::tabs.tab>');

    expect($html)
        ->toContain('<a ')
        ->toContain('href="/billing"')
        ->toContain('aria-current="page"')
        ->not->toContain('role="tab"')
        // The class string mentions the attribute; the element must not set it.
        ->not->toContain('aria-selected="');
});

it('is a tab when it controls a panel, with the claims a tab makes', function () {
    $html = Blade::render('<x-shape::tabs.tab for="plan" selected>Plan</x-shape::tabs.tab>');

    expect($html)
        ->toContain('<button')
        ->toContain('role="tab"')
        ->toContain('id="plan-tab"')
        ->toContain('aria-controls="plan"')
        ->toContain('aria-selected="true"')
        ->not->toContain('aria-current="');
});

it('drops the tablist role when the strip is navigation', function () {
    // `role="tablist"` on a row of links claims arrow-key movement, one
    // selection, and panels in this document. All three are false of a link, and
    // the missing `data-shape-tablist` is also what keeps the script off it.
    $nav = Blade::render('<x-shape::tabs as="nav" label="Settings">x</x-shape::tabs>');

    expect($nav)
        ->toContain('<nav')
        ->toContain('aria-label="Settings"')
        ->not->toContain('role="tablist"')
        ->not->toContain('data-shape-tablist');

    expect(Blade::render('<x-shape::tabs label="Settings">x</x-shape::tabs>'))
        ->toContain('role="tablist"')
        ->toContain('data-shape-tablist')
        ->toContain('aria-orientation="horizontal"');
});

it('leaves exactly one tab in the tab order', function () {
    // A roving tabindex, and the failure mode it guards: a strip where every tab
    // is a tab stop, or none is, both of which look fine and neither of which is.
    $html = Blade::render(<<<'BLADE'
    <x-shape::tabs label="Billing">
        <x-shape::tabs.tab for="plan" selected>Plan</x-shape::tabs.tab>
        <x-shape::tabs.tab for="invoices">Invoices</x-shape::tabs.tab>
        <x-shape::tabs.tab for="usage">Usage</x-shape::tabs.tab>
    </x-shape::tabs>
    BLADE);

    expect(substr_count($html, 'tabindex="0"'))->toBe(1)
        ->and(substr_count($html, 'tabindex="-1"'))->toBe(2);
});

it('hides an unselected panel with the attribute, not with a class', function () {
    // The script toggles `hidden`. A panel carrying its own display utility
    // would outrank it and never hide, which is the one thing to know before
    // styling one.
    expect(Blade::render('<x-shape::tabs.panel name="plan">Plan</x-shape::tabs.panel>'))
        ->toContain('hidden')
        ->and(Blade::render('<x-shape::tabs.panel name="plan" selected>Plan</x-shape::tabs.panel>'))
        ->not->toContain(' hidden');
});

it('joins the tab to its panel by id, in both directions', function () {
    $html = Blade::render(<<<'BLADE'
    <x-shape::tabs.tab for="plan" selected>Plan</x-shape::tabs.tab>
    <x-shape::tabs.panel name="plan" selected>Body</x-shape::tabs.panel>
    BLADE);

    expect($html)
        ->toContain('id="plan-tab"')
        ->toContain('aria-controls="plan"')
        ->toContain('id="plan"')
        ->toContain('aria-labelledby="plan-tab"')
        ->toContain('role="tabpanel"');
});

it('carries the active look on the tab rather than in a rule that reaches down from the parent', function () {
    // ARIA already requires the state to live on the child, so the variant has
    // everything it needs — and the siblings recede because muted is their
    // resting state, not because anything dims them.
    expect(Blade::render('<x-shape::tabs.tab for="plan">Plan</x-shape::tabs.tab>'))
        ->toContain('aria-selected:bg-[var(--shape-tone-tint)]')
        ->toContain('text-[color:var(--shape-fg-muted)]');

    expect(Blade::render('<x-shape::tabs label="x">y</x-shape::tabs>'))
        ->not->toContain(':has(');
});

it('shares one keyboard walker between menus and tabs', function () {
    // There is no JS test infrastructure in this package, so what the suite can
    // check is that the two behaviours have not drifted into two copies. The
    // keyboard behaviour itself — arrow keys, Home, End, the panel swap — is
    // verified by hand in the workbench and by nothing here. Said out loud in
    // the docs rather than left for a green suite to imply.
    $js = (string) file_get_contents(__DIR__.'/../../resources/js/shape.js');

    expect($js)
        ->toContain('function items(root, selector)')
        ->toContain('function roving(list, key, vertical)')
        ->toContain("items(strip, '[role=\"tab\"]')")
        ->toContain("items(menu, '[data-shape-menu-item]')");
});

it('writes the selection across every tab, not only the ones the arrows visit', function () {
    // A disabled tab is one the arrow keys skip, not one that keeps a tab stop
    // it was rendered with. Writing the selection over the filtered list leaves
    // a disabled-and-selected tab at `tabindex="0"` forever, and the strip has
    // two tab stops with nothing on screen to say so.
    $js = (string) file_get_contents(__DIR__.'/../../resources/js/shape.js');

    expect($js)
        ->toContain('function select(chosen, strip)')
        ->toContain('strip.querySelectorAll(\'[role="tab"]\')');
});

it('registers the tab behaviour beside the other keyboard one', function () {
    $js = (string) file_get_contents(__DIR__.'/../../resources/js/shape.js');

    expect(strpos($js, '    tabKeys()'))->toBeGreaterThan(strpos($js, '    menuKeys()'))
        ->and(strpos($js, '    tabKeys()'))->toBeLessThan(strpos($js, '    feedback()'));
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::tabs.tab for="plan" class="px-6">Plan</x-shape::tabs.tab>'))
        ->toContain('[:where(&amp;)]:px-3')
        ->toContain('px-6');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::tabs.tab for="plan" wire:click="show(\'plan\')">Plan</x-shape::tabs.tab>'))
        ->toContain('wire:click="show(\'plan\')"');
});
