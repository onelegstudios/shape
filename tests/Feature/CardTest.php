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

it('opts out of the shadow when asked', function () {
    expect(Blade::render('<x-shape::card :shadow="false">Body</x-shape::card>'))
        ->not->toContain('[:where(&amp;)]:shadow-sm');
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
    ['xs', '[:where(&amp;)]:gap-2', '[:where(&amp;)]:p-3'],
    ['sm', '[:where(&amp;)]:gap-3', '[:where(&amp;)]:p-4'],
    ['base', '[:where(&amp;)]:gap-4', '[:where(&amp;)]:p-6'],
    ['lg', '[:where(&amp;)]:gap-6', '[:where(&amp;)]:p-8'],
    ['xl', '[:where(&amp;)]:gap-8', '[:where(&amp;)]:p-10'],
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

it('publishes its inset for a bleeding region to cancel', function (string $padding, string $inset) {
    expect(Blade::render("<x-shape::card padding=\"{$padding}\">Body</x-shape::card>"))
        ->toContain("[:where(&amp;)]:[--shape-card-inset:{$inset}]");
})->with([
    ['xs', '--spacing(3)'],
    ['sm', '--spacing(4)'],
    ['base', '--spacing(6)'],
    ['lg', '--spacing(8)'],
    ['xl', '--spacing(10)'],
    ['none', '0px'],
]);

it('keeps the header and footer inside the inset unless they bleed', function (string $part) {
    expect(Blade::render("<x-shape::card.{$part}>Content</x-shape::card.{$part}>"))
        ->not->toContain('--shape-card-inset')
        ->not->toContain('data-shape-bleed');
})->with(['header', 'footer']);

it('bleeds a footer to the sides and bottom edge of the card', function () {
    expect(Blade::render('<x-shape::card.footer bleed>Actions</x-shape::card.footer>'))
        ->toContain('data-shape-bleed')
        ->toContain('[:where(&amp;)]:-mx-(--shape-card-inset)')
        ->toContain('[:where(&amp;)]:-mb-(--shape-card-inset)')
        ->toContain('[:where(&amp;)]:p-(--shape-card-inset)')
        ->toContain('[:where(&amp;)]:rounded-b-[inherit]')
        ->not->toContain('-mt-');
});

it('bleeds a header to the sides and top edge of the card', function () {
    expect(Blade::render('<x-shape::card.header bleed>Title</x-shape::card.header>'))
        ->toContain('data-shape-bleed')
        ->toContain('[:where(&amp;)]:-mx-(--shape-card-inset)')
        ->toContain('[:where(&amp;)]:-mt-(--shape-card-inset)')
        ->toContain('[:where(&amp;)]:p-(--shape-card-inset)')
        ->toContain('[:where(&amp;)]:rounded-t-[inherit]')
        ->not->toContain('-mb-');
});

it('bleeds media to the sides and to whichever edge it sits against', function () {
    // Position is read by the selector rather than passed as a prop, so there
    // is nothing to keep in step with the markup and nothing to ask at runtime.
    expect(Blade::render('<x-shape::card.media><img src="/a.jpg" alt=""></x-shape::card.media>'))
        ->toContain('data-shape-card-media')
        ->toContain('<img src="/a.jpg" alt="">')
        ->toContain('[:where(&amp;)]:-mx-(--shape-card-inset)')
        ->toContain('[:where(&amp;:first-child)]:-mt-(--shape-card-inset)')
        ->toContain('[:where(&amp;:first-child)]:rounded-t-[inherit]')
        ->toContain('[:where(&amp;:last-child)]:-mb-(--shape-card-inset)')
        ->toContain('[:where(&amp;:last-child)]:rounded-b-[inherit]')
        ->toContain('[:where(&amp;)]:overflow-hidden');
});

it('makes the media child a full-width block and leaves its height to the caller', function () {
    expect(Blade::render('<x-shape::card.media class="h-40">Picture</x-shape::card.media>'))
        ->toContain('[:where(&amp;&gt;*)]:block')
        ->toContain('[:where(&amp;&gt;*)]:w-full')
        ->toContain('h-40')
        ->not->toContain('aspect-');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::card class="p-0 shadow-none">Body</x-shape::card>'))
        ->toContain('[:where(&amp;)]:p-6')
        ->toContain('p-0')
        ->toContain('shadow-none');
});
