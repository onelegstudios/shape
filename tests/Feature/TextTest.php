<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a paragraph by default', function () {
    expect(Blade::render('<x-shape::text>Due on the first.</x-shape::text>'))
        ->toContain('<p')
        ->toContain('</p>')
        ->toContain('Due on the first.')
        ->toContain('data-shape-text');
});

it('picks its element from the as prop', function (string $as) {
    expect(Blade::render("<x-shape::text as=\"{$as}\">Inline</x-shape::text>"))
        ->toContain("<{$as}")
        ->toContain("</{$as}>");
})->with(['p', 'span', 'div']);

it('takes its muted colour from the surface rather than a global grey', function () {
    // This is what makes "don't use grey text on coloured backgrounds"
    // structural: on a tinted surface the variable resolves to that surface's
    // own dialled-down foreground, not to a neutral.
    expect(Blade::render('<x-shape::text variant="muted">Optional</x-shape::text>'))
        ->toContain('text-[color:var(--shape-fg-muted)]')
        ->toContain('data-shape-variant="muted"');
});

it('emphasises without reaching for a colour', function () {
    expect(Blade::render('<x-shape::text variant="strong">Important</x-shape::text>'))
        ->toContain('text-[color:var(--shape-fg)]')
        ->toContain('[:where(&amp;)]:font-medium');
});

it('applies the requested size', function (string $size, string $expected) {
    expect(Blade::render("<x-shape::text size=\"{$size}\">Copy</x-shape::text>"))->toContain($expected);
})->with([
    ['xs', '[:where(&amp;)]:text-xs'],
    ['sm', '[:where(&amp;)]:text-sm'],
    ['base', '[:where(&amp;)]:text-base'],
    ['lg', '[:where(&amp;)]:text-lg'],
    ['xl', '[:where(&amp;)]:text-xl'],
]);

it('never lets the caller set line height apart from size', function () {
    // Every size arm ships its own leading, so there is no combination that
    // produces a size without one.
    foreach (['xs', 'sm', 'base', 'lg', 'xl'] as $size) {
        expect(Blade::render("<x-shape::text size=\"{$size}\">Copy</x-shape::text>"))
            ->toContain('[:where(&amp;)]:leading-');
    }
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::text class="text-lg">Copy</x-shape::text>'))
        ->toContain('[:where(&amp;)]:text-base')
        ->toContain('text-lg');
});
