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

it('renders a trailing icon after the label when one is named', function () {
    $html = Blade::render('<x-shape::badge label="Overdue" icon-trailing="shape-arrow-right" />');

    expect($html)->toContain('data-shape-icon')
        ->and(strpos($html, 'Overdue'))->toBeLessThan(strpos($html, 'data-shape-icon'));
});

it('resolves nothing into the trailing slot from the tone', function () {
    // The state glyph leads; a second copy behind the label would say the same
    // thing twice.
    expect(Blade::render('<x-shape::badge label="Paid" tone="success" />'))
        ->toContain('data-shape-icon')
        ->and(substr_count(Blade::render('<x-shape::badge label="Paid" tone="success" />'), 'data-shape-icon'))->toBe(1);
});

it('draws a leading and a trailing icon together, in that order', function () {
    $html = Blade::render('<x-shape::badge label="Paid" tone="success" icon-trailing="shape-arrow-right" />');

    expect(substr_count($html, 'data-shape-icon'))->toBe(2)
        ->and(strpos($html, 'data-shape-icon'))->toBeLessThan(strpos($html, 'Paid'))
        ->and(strrpos($html, 'data-shape-icon'))->toBeGreaterThan(strpos($html, 'Paid'));
});

it('keeps the trailing icon when the resolved one is opted out of', function () {
    $html = Blade::render('<x-shape::badge label="Paid" tone="success" :icon="false" icon-trailing="shape-arrow-right" />');

    expect(substr_count($html, 'data-shape-icon'))->toBe(1)
        ->and(strpos($html, 'Paid'))->toBeLessThan(strpos($html, 'data-shape-icon'));
});

it('sizes both icons with the same icon-size', function () {
    $html = Blade::render('<x-shape::badge label="Paid" tone="success" icon-trailing="shape-arrow-right" icon-size="sm" />');

    expect(substr_count($html, 'size-5'))->toBe(2);
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
    ['xs', '[:where(&amp;)]:px-1.5 [:where(&amp;)]:py-0 [:where(&amp;)]:text-2xs'],
    ['sm', '[:where(&amp;)]:px-2 [:where(&amp;)]:py-0.5 [:where(&amp;)]:text-2xs'],
    ['base', '[:where(&amp;)]:px-2.5 [:where(&amp;)]:py-1 [:where(&amp;)]:text-xs'],
    ['lg', '[:where(&amp;)]:px-3 [:where(&amp;)]:py-1 [:where(&amp;)]:text-sm'],
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
