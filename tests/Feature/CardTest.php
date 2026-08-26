<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders as a surface with no border', function () {
    // "Use fewer borders" — separation comes from the surface shift and the
    // resting elevation, so a border has to be asked for.
    $html = Blade::render('<x-shape::card>Body</x-shape::card>');

    expect($html)
        ->toContain('data-shape-card')
        ->toContain('Body')
        ->toContain('[:where(&amp;)]:shadow-sm')
        ->not->toContain('[:where(&amp;)]:border ');
});

it('opts into a border when asked', function () {
    expect(Blade::render('<x-shape::card border>Body</x-shape::card>'))
        ->toContain('[:where(&amp;)]:border ')
        ->toContain('[:where(&amp;)]:border-shape-200');
});

it('uses the raised step of the elevation scale rather than one of its own', function () {
    expect(Blade::render('<x-shape::card>Body</x-shape::card>'))
        ->toContain('shadow-sm')
        ->not->toContain('shadow-shape');
});

it('owns the space between its children so none of them set an outer margin', function () {
    // The gap belongs to the parent. This is the rule that kills the whole
    // class of "which element does this space belong to" bugs.
    expect(Blade::render('<x-shape::card>Body</x-shape::card>'))
        ->toContain('flex flex-col')
        ->toContain('[:where(&amp;)]:gap-4');
});

it('moves padding and gap together', function (string $padding, string $gap, string $pad) {
    $html = Blade::render("<x-shape::card padding=\"{$padding}\">Body</x-shape::card>");

    expect($html)->toContain($gap)->toContain($pad)
        ->and($html)->toContain("data-shape-padding=\"{$padding}\"");
})->with([
    ['sm', '[:where(&amp;)]:gap-3', '[:where(&amp;)]:p-4'],
    ['base', '[:where(&amp;)]:gap-4', '[:where(&amp;)]:p-6'],
    ['lg', '[:where(&amp;)]:gap-6', '[:where(&amp;)]:p-8'],
]);

it('drops its padding without losing its gap', function () {
    $html = Blade::render('<x-shape::card padding="none">Body</x-shape::card>');

    expect($html)
        ->toContain('[:where(&amp;)]:gap-4')
        ->not->toContain('[:where(&amp;)]:p-');
});

it('composes a header and a footer without inspecting a slot', function () {
    $html = Blade::render(<<<'BLADE'
    <x-shape::card>
        <x-shape::card.header>
            <x-shape::heading size="lg">Invoice</x-shape::heading>
        </x-shape::card.header>
        <x-shape::card.footer>
            <x-shape::button>Pay</x-shape::button>
        </x-shape::card.footer>
    </x-shape::card>
    BLADE);

    expect($html)
        ->toContain('data-shape-card-header')
        ->toContain('data-shape-card-footer')
        ->toContain('Invoice')
        ->toContain('data-shape-button');
});

it('draws no rule between the header and the body', function () {
    // A caller who wants one composes a separator; the header does not assume.
    expect(Blade::render('<x-shape::card.header>Title</x-shape::card.header>'))
        ->not->toContain('border-b');
});

it('wraps footer actions rather than overflowing them', function () {
    expect(Blade::render('<x-shape::card.footer>Actions</x-shape::card.footer>'))
        ->toContain('flex flex-wrap items-center');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::card class="p-0 shadow-none">Body</x-shape::card>'))
        ->toContain('[:where(&amp;)]:p-6')
        ->toContain('p-0')
        ->toContain('shadow-none');
});
