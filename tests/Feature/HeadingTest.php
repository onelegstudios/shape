<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders an h2 by default', function () {
    expect(Blade::render('<x-shape::heading>Invoices</x-shape::heading>'))
        ->toContain('<h2')
        ->toContain('</h2>')
        ->toContain('Invoices')
        ->toContain('data-shape-heading');
});

it('picks its element from the level', function (int $level) {
    expect(Blade::render("<x-shape::heading level=\"{$level}\">Invoices</x-shape::heading>"))
        ->toContain("<h{$level}")
        ->toContain("</h{$level}>");
})->with([1, 2, 3, 4, 5, 6]);

it('keeps document hierarchy and visual hierarchy independent', function () {
    // The point of the prop pair: a page's h1 is often not its largest text.
    $small = Blade::render('<x-shape::heading level="1" size="sm">Section</x-shape::heading>');
    $large = Blade::render('<x-shape::heading level="6" size="2xl">Total</x-shape::heading>');

    expect($small)->toContain('<h1')->toContain('[:where(&amp;)]:text-sm')
        ->and($large)->toContain('<h6')->toContain('[:where(&amp;)]:text-2xl');
});

it('binds leading and tracking to the size rather than letting them be set apart', function (string $size, string $text, string $tracking) {
    $html = Blade::render("<x-shape::heading size=\"{$size}\">Invoices</x-shape::heading>");

    expect($html)->toContain($text)->toContain($tracking);
})->with([
    ['sm', '[:where(&amp;)]:text-sm', '[:where(&amp;)]:tracking-normal'],
    ['base', '[:where(&amp;)]:text-base', '[:where(&amp;)]:tracking-normal'],
    ['lg', '[:where(&amp;)]:text-lg', '[:where(&amp;)]:tracking-tight'],
    ['xl', '[:where(&amp;)]:text-xl', '[:where(&amp;)]:tracking-tight'],
    ['2xl', '[:where(&amp;)]:text-2xl', '[:where(&amp;)]:tracking-tighter'],
]);

it('reads its colour from the surface contract', function () {
    expect(Blade::render('<x-shape::heading>Invoices</x-shape::heading>'))
        ->toContain('text-[color:var(--shape-fg)]');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::heading class="text-4xl">Invoices</x-shape::heading>'))
        ->toContain('[:where(&amp;)]:text-base')
        ->toContain('text-4xl');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::heading id="title" wire:key="t">Invoices</x-shape::heading>'))
        ->toContain('id="title"')
        ->toContain('wire:key="t"');
});
