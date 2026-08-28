<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('leads with the value and de-emphasizes the label', function () {
    // "Labels are a last resort." The label still comes first in the DOM so the
    // number has a subject when it is read aloud; it is the styling that recedes.
    $html = Blade::render('<x-shape::stat label="Invoices sent" value="1,204" />');

    expect($html)
        ->toContain('data-shape-stat')
        ->toContain('1,204')
        ->toContain('Invoices sent')
        ->toContain('text-2xl font-semibold')
        ->and(strpos($html, 'Invoices sent'))->toBeLessThan(strpos($html, '1,204'));
});

it('swaps the emphasis when the label is the point', function () {
    $html = Blade::render('<x-shape::stat label="Plan" value="Team" emphasis="label" />');

    expect($html)
        ->toContain('data-shape-emphasis="label"')
        ->not->toContain('text-2xl');
});

it('lines up the figures, because a column of stats is a column of numbers', function () {
    expect(Blade::render('<x-shape::stat value="1,204" />'))->toContain('tabular-nums');
});

it('draws every trend as well as tinting it', function (string $trend, string $tone) {
    // Never rely on colour alone. Opting out of the glyph is possible; forgetting
    // it is not, because the component resolves it rather than asking for it.
    $html = Blade::render("<x-shape::stat value=\"1,204\" delta=\"12%\" trend=\"{$trend}\" />");

    expect($html)
        ->toContain('data-shape-icon')
        ->toContain("data-shape-tone=\"{$tone}\"");
})->with([
    ['up', 'success'],
    ['down', 'danger'],
    ['flat', 'neutral'],
]);

it('gives each trend its own drawing rather than one glyph in three colours', function () {
    $glyphs = collect(['up', 'down', 'flat'])
        ->map(fn (string $trend) => Blade::render("<x-shape::stat delta=\"12%\" trend=\"{$trend}\" />"))
        ->map(fn (string $html) => preg_match('/<path[^>]*d="([^"]+)"/', $html, $m) ? $m[1] : null);

    expect($glyphs->filter())->toHaveCount(3)
        ->and($glyphs->unique())->toHaveCount(3);
});

it('lets a metric say that up is the bad direction', function () {
    // Churn, refunds, error rate. The trend picks the arrow; the colour is a
    // separate claim about whether the direction is good news.
    expect(Blade::render('<x-shape::stat value="4.1%" delta="0.6pp" trend="up" color="danger" />'))
        ->toContain('data-shape-tone="danger"')
        ->toContain('data-shape-icon');
});

it('collapses the delta row in CSS rather than behind a condition', function () {
    // This is what makes the memo tier real. An `@if ($delta)` would make `delta`
    // a prop that drives a branch, and a prop that drives a branch cannot be
    // safe — so every stat with a computed delta, which is all of them, would
    // have stopped folding.
    $html = Blade::render('<x-shape::stat label="Invoices" value="1,204" />');

    expect($html)
        ->toContain('data-shape-stat-delta')
        ->toContain('empty:hidden')
        ->toMatch('/data-shape-tone="neutral">(<\/p>)/');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::stat value="1,204" class="gap-4" />'))
        ->toContain('[:where(&amp;)]:gap-1')
        ->toContain('gap-4');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::stat value="1,204" wire:key="sent" />'))
        ->toContain('wire:key="sent"');
});
