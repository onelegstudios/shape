<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders its heading and description from props', function () {
    // Props rather than slots, because a condition on a prop is answered at
    // compile time and a condition on a slot is not.
    $html = Blade::render('<x-shape::empty heading="No invoices" description="They will show up here." />');

    expect($html)
        ->toContain('data-shape-empty')
        ->toContain('No invoices')
        ->toContain('They will show up here.');
});

it('renders the heading through the heading component at a sensible level', function () {
    expect(Blade::render('<x-shape::empty heading="No invoices" />'))
        ->toContain('<h3')
        ->toContain('data-shape-heading');
});

it('renders the description as muted body copy', function () {
    expect(Blade::render('<x-shape::empty description="Nothing yet." />'))
        ->toContain('data-shape-variant="muted"')
        ->toContain('text-[color:var(--shape-fg-muted)]');
});

it('omits the parts it was not given', function () {
    $html = Blade::render('<x-shape::empty heading="No invoices" />');

    expect($html)
        ->toContain('No invoices')
        ->not->toContain('data-shape-icon')
        ->not->toContain('data-shape-text');
});

it('renders an icon when named', function () {
    expect(Blade::render('<x-shape::empty icon="x-circle" heading="No invoices" />'))
        ->toContain('data-shape-icon');
});

it('takes actions through its slot', function () {
    $html = Blade::render(<<<'BLADE'
    <x-shape::empty heading="No invoices">
        <x-shape::button variant="primary">New invoice</x-shape::button>
    </x-shape::empty>
    BLADE);

    expect($html)
        ->toContain('data-shape-button')
        ->toContain('New invoice');
});

it('collapses the action row in CSS rather than by inspecting the slot', function () {
    // Slot inspection is a runtime question and would cost the component its
    // fold. `empty:hidden` answers the same question in the browser.
    expect(Blade::render('<x-shape::empty heading="No invoices" />'))
        ->toContain('empty:hidden');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::empty heading="No invoices" class="py-4" />'))
        ->toContain('[:where(&amp;)]:py-12')
        ->toContain('py-4');
});
