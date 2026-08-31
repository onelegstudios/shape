<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a button element by default', function () {
    $html = Blade::render('<x-shape::button>Save</x-shape::button>');

    expect($html)
        ->toContain('<button')
        ->toContain('type="button"')
        ->toContain('Save')
        ->toContain('data-shape-button');
});

it('renders an anchor when asked to', function () {
    $html = Blade::render('<x-shape::button as="a" href="/settings">Settings</x-shape::button>');

    expect($html)
        ->toContain('<a ')
        ->toContain('href="/settings"')
        ->not->toContain('<button');
});

it('carries hierarchy on the variant attribute and semantics on the tone attribute', function () {
    $html = Blade::render('<x-shape::button variant="subtle" color="danger">Delete</x-shape::button>');

    expect($html)
        ->toContain('data-shape-variant="subtle"')
        ->toContain('data-shape-tone="danger"');
});

it('falls back to the neutral tone', function () {
    expect(Blade::render('<x-shape::button>Save</x-shape::button>'))
        ->toContain('data-shape-tone="neutral"');
});

it('reads its colours through tone variables rather than a variant colour matrix', function () {
    $primary = Blade::render('<x-shape::button variant="primary" color="danger">Delete</x-shape::button>');
    $neutral = Blade::render('<x-shape::button variant="primary">Save</x-shape::button>');

    // Same classes either way — only the tone attribute differs, which is what
    // keeps `color` a pass-through prop and the component foldable.
    expect($primary)->toContain('bg-[var(--shape-tone)]')
        ->and($neutral)->toContain('bg-[var(--shape-tone)]');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    $html = Blade::render('<x-shape::button class="rounded-full">Save</x-shape::button>');

    expect($html)
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('rounded-full');
});

it('renders leading and trailing icons', function () {
    $html = Blade::render('<x-shape::button icon="shape-plus" icon-trailing="shape-arrow-right">Add</x-shape::button>');

    expect(substr_count($html, 'data-shape-icon'))->toBe(2);
});

it('sizes itself squarely for icon only buttons', function () {
    $html = Blade::render('<x-shape::button square icon="shape-trash" aria-label="Delete" />');

    expect($html)
        ->toContain('size-10')
        ->toContain('aria-label="Delete"');
});

it('passes livewire and alpine attributes straight through', function () {
    $html = Blade::render('<x-shape::button wire:click="save" x-on:click="track()">Save</x-shape::button>');

    expect($html)
        ->toContain('wire:click="save"')
        ->toContain('x-on:click="track()"');
});

it('applies the requested size', function (string $size, string $expected) {
    expect(Blade::render("<x-shape::button size=\"{$size}\">Save</x-shape::button>"))
        ->toContain($expected);
})->with([
    ['sm', 'h-8'],
    ['base', 'h-10'],
    ['lg', 'h-12'],
]);

it('lets a caller change or remove its elevation', function () {
    // Elevation is applied at zero specificity, like the other defaults.
    expect(Blade::render('<x-shape::button variant="primary" class="shadow-none">Flat</x-shape::button>'))
        ->toContain('[:where(&amp;)]:shadow-sm')
        ->toContain('shadow-none');
});

it('uses tailwind\'s elevation scale rather than one of its own', function () {
    expect(Blade::render('<x-shape::button variant="primary">Save</x-shape::button>'))
        ->toContain('shadow-sm')
        ->not->toContain('shadow-shape');
});
