<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a horizontal rule by default', function () {
    expect(Blade::render('<x-shape::separator />'))
        ->toContain('data-shape-separator')
        ->toContain('data-shape-orientation="horizontal"')
        ->toContain('h-px w-full');
});

it('renders a vertical rule that stretches to its row', function () {
    expect(Blade::render('<x-shape::separator orientation="vertical" />'))
        ->toContain('data-shape-orientation="vertical"')
        ->toContain('w-px self-stretch');
});

it('hides a bare rule from assistive technology', function () {
    // The spacing and the headings around it already say what it says.
    expect(Blade::render('<x-shape::separator />'))
        ->toContain('role="separator"')
        ->toContain('aria-hidden="true"');
});

it('exposes a labelled rule as a real separator', function () {
    $bare = Blade::render('<x-shape::separator />');
    $labelled = Blade::render('<x-shape::separator label="Archived" />');

    // The bare rule hides itself. The labelled one hides only its two rules,
    // leaving the separator and its name exposed.
    expect(substr_count($bare, 'aria-hidden="true"'))->toBe(1)
        ->and(substr_count($labelled, 'aria-hidden="true"'))->toBe(2)
        ->and($labelled)->toContain('role="separator"')->toContain('Archived');
});

it('sits its label in a gap between two rules', function () {
    $html = Blade::render('<x-shape::separator label="Archived" />');

    expect(substr_count($html, 'h-px flex-1'))->toBe(2);
});

it('takes its label colour from the surface contract', function () {
    expect(Blade::render('<x-shape::separator label="Archived" />'))
        ->toContain('text-[color:var(--shape-fg-muted)]');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::separator class="bg-shape-400" />'))
        ->toContain('[:where(&amp;)]:bg-shape-200')
        ->toContain('bg-shape-400');
});

it('sets the label\'s type and the gap it sits in from one word', function (string $size, string $gap, string $type) {
    expect(Blade::render("<x-shape::separator label=\"Archived\" size=\"{$size}\" />"))
        ->toContain("[:where(&amp;)]:{$gap}")
        ->toContain("{$type} font-medium")
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['xs', 'gap-2', 'text-2xs'],
    ['sm', 'gap-2.5', 'text-xs'],
    ['base', 'gap-3', 'text-xs'],
    ['lg', 'gap-4', 'text-sm'],
    ['xl', 'gap-5', 'text-base'],
]);

it('bottoms the rule out at a hairline and thickens only the top two steps', function (string $size, string $across, string $down) {
    // A hairline is one device pixel and there is nothing under it, so the three
    // small steps all draw one. Worth knowing before reaching for `size="xs"` on
    // a bare rule and watching nothing happen.
    expect(Blade::render("<x-shape::separator size=\"{$size}\" />"))
        ->toContain("{$across} w-full");

    expect(Blade::render("<x-shape::separator orientation=\"vertical\" size=\"{$size}\" />"))
        ->toContain("{$down} self-stretch");
})->with([
    ['xs', 'h-px', 'w-px'],
    ['sm', 'h-px', 'w-px'],
    ['base', 'h-px', 'w-px'],
    ['lg', 'h-0.5', 'w-0.5'],
    ['xl', 'h-1', 'w-1'],
]);
