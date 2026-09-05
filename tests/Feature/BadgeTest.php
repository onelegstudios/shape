<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders its label from a prop rather than a slot', function () {
    // Slotless is what makes the badge memoizable, which matters because it is
    // the component that repeats most in a table.
    $html = Blade::render('<x-shape::badge label="Active" />');

    expect($html)
        ->toContain('<span')
        ->toContain('Active')
        ->toContain('data-shape-badge');
});

it('falls back to the neutral tone', function () {
    expect(Blade::render('<x-shape::badge label="Draft" />'))
        ->toContain('data-shape-tone="neutral"');
});

it('carries semantics on the tone attribute', function () {
    expect(Blade::render('<x-shape::badge label="Paid" tone="success" />'))
        ->toContain('data-shape-tone="success"');
});

it('resolves an icon for every state so colour is never the only signal', function (string $tone) {
    expect(Blade::render("<x-shape::badge label=\"State\" tone=\"{$tone}\" />"))
        ->toContain('data-shape-icon');
})->with(['success', 'danger', 'warning', 'info']);

it('gives each state a glyph of its own rather than reusing one', function () {
    // Asserting the drawings differ, rather than asserting any particular path,
    // is what actually enforces the rule: a badge has to stay readable in
    // greyscale, which it doesn't if two states share an icon.
    $glyphs = collect(['success', 'danger', 'warning', 'info'])
        ->map(fn (string $tone) => Blade::render("<x-shape::badge label=\"State\" tone=\"{$tone}\" />"))
        ->map(fn (string $html) => preg_match('/<path[^>]*d="([^"]+)"/', $html, $m) ? $m[1] : null);

    expect($glyphs->filter())->toHaveCount(4)
        ->and($glyphs->unique())->toHaveCount(4);
});

it('renders no icon for the neutral tone', function () {
    expect(Blade::render('<x-shape::badge label="Draft" />'))
        ->not->toContain('data-shape-icon');
});

it('renders none for the emphasis tones either, which are not states', function (string $tone) {
    expect(Blade::render("<x-shape::badge label=\"New\" tone=\"{$tone}\" />"))
        ->toContain("data-shape-tone=\"{$tone}\"")
        ->not->toContain('data-shape-icon');
})->with(['brand', 'accent']);

it('lets a caller opt out of the icon', function () {
    expect(Blade::render('<x-shape::badge label="Paid" tone="success" :icon="false" />'))
        ->not->toContain('data-shape-icon')
        ->and(Blade::render('<x-shape::badge label="Paid" tone="success" />'))
        ->toContain('data-shape-icon');
});

it('lets a caller name an icon of its own', function () {
    expect(Blade::render('<x-shape::badge label="New" icon="shape-plus" />'))
        ->toContain('data-shape-icon');
});

it('reads its colours through the same tone variables the button does', function () {
    $badge = Blade::render('<x-shape::badge label="Paid" tone="success" variant="solid" />');

    expect($badge)
        ->toContain('bg-[var(--shape-tone)]')
        ->toContain('text-[var(--shape-tone-fg)]');
});

it('keeps every variant reading the same variables rather than a colour matrix', function (string $variant, string $expected) {
    expect(Blade::render("<x-shape::badge label=\"Paid\" tone=\"success\" variant=\"{$variant}\" />"))
        ->toContain($expected)
        ->toContain('data-shape-variant="'.$variant.'"');
})->with([
    ['solid', 'bg-[var(--shape-tone)]'],
    ['subtle', 'bg-[var(--shape-tone-tint)]'],
    ['outline', 'border-[var(--shape-tone-border)]'],
]);

it('applies the requested size', function (string $size, string $expected) {
    expect(Blade::render("<x-shape::badge label=\"Paid\" size=\"{$size}\" />"))->toContain($expected);
})->with([
    ['sm', '[:where(&amp;)]:text-2xs'],
    ['base', '[:where(&amp;)]:text-xs'],
]);

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::badge label="Paid" class="rounded-full" />'))
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('rounded-full');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::badge label="Paid" wire:key="b" title="Paid in full" />'))
        ->toContain('wire:key="b"')
        ->toContain('title="Paid in full"');
});
