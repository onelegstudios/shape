<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('puts the caller on the box, because that is where a height bound has to go', function () {
    // The wrapper is the scroll container, so `max-h-*` and everything else a
    // caller says about the table's shape belongs to it rather than to the
    // <table> element inside.
    $html = Blade::render('<x-shape::table class="max-h-96" wire:key="invoices" />');

    expect($html)
        ->toContain('data-shape-table')
        ->toContain('max-h-96')
        ->toContain('wire:key="invoices"')
        ->toContain('[:where(&amp;)]:overflow-x-auto');
});

it('is content rather than a surface, so it brings no border or elevation of its own', function () {
    // A table that wants to sit on a surface is composed inside a card.
    expect(Blade::render('<x-shape::table />'))
        ->not->toContain('shadow-')
        ->not->toContain('rounded-shape');
});

it('renders its empty state without ever inspecting the slot', function () {
    // The empty state is in the markup even when there are rows; the stylesheet
    // is what removes it. Asking Blade whether the slot has rows would be a
    // runtime question and would take the table off the fold path.
    $html = Blade::render(<<<'BLADE'
    <x-shape::table>
        <x-shape::table.body>
            <x-shape::table.row><x-shape::table.cell value="Ada" /></x-shape::table.row>
        </x-shape::table.body>
    </x-shape::table>
    BLADE);

    expect($html)
        ->toContain('data-shape-table-empty')
        ->toContain('data-shape-empty')
        ->toContain('Nothing here yet');
});

it('keeps the empty state beside the table, not inside it', function () {
    // A <div> written inside a <tbody> is foster-parented back out of the table
    // by the parser, so an empty state placed there would render above the table
    // in the wrong order and outside the rule that hides it.
    expect(Blade::render('<x-shape::table />'))
        ->toMatch('/<\/table>\s*<div data-shape-table-empty>/');
});

it('separates rows on the body rather than bordering each one', function () {
    $html = Blade::render('<x-shape::table.body><tr></tr></x-shape::table.body>');

    expect($html)
        ->toContain('data-shape-table-body')
        ->toContain('[:where(&amp;)]:divide-y')
        ->and(Blade::render('<x-shape::table.row>x</x-shape::table.row>'))
        ->not->toContain('border-b');
});

it('renders the header row itself, so a header can never be counted as data', function () {
    // The stylesheet hides the empty state when it finds a `data-shape-table-row`
    // inside a tbody. A header row written with `table.row` would be one, so the
    // head renders its own <tr> and there is nothing to write.
    $html = Blade::render('<x-shape::table.head><x-shape::table.heading label="Name" /></x-shape::table.head>');

    expect($html)
        ->toContain('<thead')
        ->toContain('<tr>')
        ->toContain('scope="col"')
        ->not->toContain('data-shape-table-row');
});

it('draws the rule under the head with an inset shadow rather than a border', function () {
    // Under collapsed borders — which preflight sets on every table — a border
    // belongs to the table box, so a stuck <th> scrolls away and leaves its own
    // border behind. A shadow is painted by the cell and travels with it.
    $html = Blade::render('<x-shape::table.head sticky>x</x-shape::table.head>');

    expect($html)
        ->toContain('[&amp;&gt;tr&gt;th]:shadow-[inset_0_-1px_0_var(--color-shape-200)]')
        ->not->toContain('border-b');
});

it('sticks the head without a z-index', function () {
    // A sticky box with `z-index: auto` creates no stacking context and paints
    // with the positioned descendants, which is already above the in-flow cells
    // sliding under it. Anything else in this library that floats is in the top
    // layer, and the stylesheet is asserted to hold no z-index at all.
    $html = Blade::render('<x-shape::table.head sticky>x</x-shape::table.head>');

    expect($html)
        ->toContain('[&amp;&gt;tr&gt;th]:sticky')
        ->toContain('[&amp;&gt;tr&gt;th]:top-0')
        ->toContain('[&amp;&gt;tr&gt;th]:bg-white')
        ->not->toContain('z-');

    expect(Blade::render('<x-shape::table.head>x</x-shape::table.head>'))
        ->not->toContain(':sticky');
});

it('takes a cell value as a prop and its contents as a slot', function () {
    // Both, deliberately: the prop is what folds on a per-row value, the slot is
    // what a cell holding a badge needs.
    expect(Blade::render('<x-shape::table.cell value="1,204" />'))
        ->toContain('data-shape-table-cell')
        ->toContain('1,204')
        ->and(Blade::render('<x-shape::table.cell><x-shape::badge label="Paid" /></x-shape::table.cell>'))
        ->toContain('data-shape-badge');
});

it('lines up the figures in a column it has right-aligned', function () {
    // A right-aligned column is a number column, and figures that do not line up
    // are the reason it was right-aligned.
    expect(Blade::render('<x-shape::table.cell value="1,204" align="end" />'))
        ->toContain('[:where(&amp;)]:text-end')
        ->toContain('[:where(&amp;)]:tabular-nums')
        ->and(Blade::render('<x-shape::table.cell value="Ada" />'))
        ->not->toContain('tabular-nums');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::table.cell value="Ada" class="px-6" />'))
        ->toContain('[:where(&amp;)]:px-3')
        ->toContain('px-6');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::table.row wire:key="row-1">x</x-shape::table.row>'))
        ->toContain('wire:key="row-1"')
        ->and(Blade::render('<x-shape::table.heading label="Amount" colspan="2" />'))
        ->toContain('colspan="2"');
});

it('sets its density from the wrapper and leaves the cells alone', function (string $size, string $rules) {
    // Density belongs to the table: a table whose rows were set at four
    // densities is not a table anyone is trying to build. It reaches the cells
    // as descendant utilities, so nothing extra is rendered per row — which
    // matters in the component here that renders most often.
    $html = Blade::render("<x-shape::table size=\"{$size}\"><x-shape::table.body><x-shape::table.row><x-shape::table.cell value=\"1\" /></x-shape::table.row></x-shape::table.body></x-shape::table>");

    expect($html)
        ->toContain($rules)
        ->toContain("data-shape-size=\"{$size}\"")
        ->toContain('[:where(&amp;)]:px-3 [:where(&amp;)]:py-3');
})->with([
    ['xs', '[&amp;_th]:px-2 [&amp;_th]:py-1 [&amp;_td]:px-2 [&amp;_td]:py-1.5'],
    ['sm', '[&amp;_th]:px-2.5 [&amp;_th]:py-1.5 [&amp;_td]:px-2.5 [&amp;_td]:py-2'],
    ['lg', '[&amp;_th]:px-4 [&amp;_th]:py-3 [&amp;_th]:text-xs [&amp;_td]:px-4 [&amp;_td]:py-4'],
    ['xl', '[&amp;_th]:px-5 [&amp;_th]:py-4 [&amp;_th]:text-sm [&amp;_td]:px-5 [&amp;_td]:py-5'],
]);

it('renders the markup it always did at the default step', function () {
    expect(Blade::render('<x-shape::table><x-shape::table.body /></x-shape::table>'))
        ->not->toContain('[&amp;_td]:')
        ->not->toContain('[&amp;&gt;table]:');
});

it('hands its size to the empty state it renders', function () {
    // So that a tight table does not sit above a full screen of white space.
    expect(Blade::render('<x-shape::table size="xs"><x-shape::table.body /></x-shape::table>'))
        ->toContain('[:where(&amp;)]:gap-1 [:where(&amp;)]:px-4 [:where(&amp;)]:py-6')
        ->toContain('data-shape-empty');
});
