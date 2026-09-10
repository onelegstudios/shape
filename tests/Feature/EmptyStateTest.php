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
    expect(Blade::render('<x-shape::empty icon="shape-danger" heading="No invoices" />'))
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

it('grows the room, and the mark and the type with it', function (string $size, string $inset, string $heading, string $glyph) {
    // An empty state is mostly room, so that is mostly what the prop moves — but
    // a 24px glyph over 20 pixels of padding reads as a mark that outgrew its
    // box, so the mark and the words go with it.
    $html = Blade::render("<x-shape::empty icon=\"shape-info\" heading=\"No invoices yet\" size=\"{$size}\" />");

    expect($html)
        ->toContain($inset)
        ->toContain("data-shape-heading data-shape-size=\"{$heading}\"")
        ->toContain("[:where(&amp;)]:size-{$glyph}")
        ->toContain('data-shape-empty');
})->with([
    ['xs', '[:where(&amp;)]:gap-1 [:where(&amp;)]:px-4 [:where(&amp;)]:py-6', 'sm', '5'],
    ['sm', '[:where(&amp;)]:gap-1.5 [:where(&amp;)]:px-5 [:where(&amp;)]:py-8', 'base', '6'],
    ['base', '[:where(&amp;)]:gap-2 [:where(&amp;)]:px-6 [:where(&amp;)]:py-12', 'lg', '6'],
    ['lg', '[:where(&amp;)]:gap-3 [:where(&amp;)]:px-8 [:where(&amp;)]:py-16', 'xl', '8'],
    ['xl', '[:where(&amp;)]:gap-4 [:where(&amp;)]:px-10 [:where(&amp;)]:py-20', 'xl', '10'],
]);

it('lets a named icon size beat the one the step resolved', function () {
    expect(Blade::render('<x-shape::empty icon="shape-info" icon-size="xs" size="xl" />'))
        ->toContain('[:where(&amp;)]:size-4')
        ->not->toContain('[:where(&amp;)]:size-10');
});
