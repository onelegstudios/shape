<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is the modal with a side', function () {
    $html = Blade::render('<x-shape::drawer name="cart">Items</x-shape::drawer>');

    expect($html)
        ->toContain('<dialog')
        ->toContain('data-shape-drawer')
        ->toContain('data-shape-side="right"');
});

it('takes its edge from a key, and its rounding from the same one', function (string $side, string $rounding) {
    $html = Blade::render("<x-shape::drawer name=\"cart\" side=\"{$side}\">Items</x-shape::drawer>");

    expect($html)
        ->toContain("data-shape-side=\"{$side}\"")
        ->toContain($rounding);
})->with([
    ['right', '[:where(&amp;)]:rounded-l-shape-lg'],
    ['left', '[:where(&amp;)]:rounded-r-shape-lg'],
    ['bottom', '[:where(&amp;)]:rounded-t-shape-lg'],
]);

it('sizes a side drawer across the viewport and a bottom one down it', function () {
    expect(Blade::render('<x-shape::drawer name="c" side="right" size="lg">Items</x-shape::drawer>'))
        ->toContain('[:where(&amp;)]:max-w-xl');

    expect(Blade::render('<x-shape::drawer name="c" side="bottom" size="lg">Items</x-shape::drawer>'))
        ->toContain('[:where(&amp;)]:max-h-[85dvh]');
});

it('scrolls its body rather than the whole panel, so the heading stays put', function () {
    expect(Blade::render('<x-shape::drawer name="c" heading="Cart">Items</x-shape::drawer>'))
        ->toContain('data-shape-drawer-body')
        ->toContain('min-h-0 flex-1 overflow-y-auto');
});

it('shares the modal\'s trigger and close rather than aliasing them', function () {
    // The thing being opened is named in `for`, so one trigger covers both. A
    // second name for one component is a second thing to keep in sync.
    $html = Blade::render(<<<'BLADE'
    <x-shape::overlay.trigger for="cart">Cart</x-shape::overlay.trigger>
    <x-shape::drawer name="cart" heading="Your cart">Items</x-shape::drawer>
    BLADE);

    expect($html)
        ->toContain('commandfor="cart"')
        ->toContain('id="cart"')
        ->toContain('data-shape-overlay-close');
});

it('holds Escape off when it must be answered rather than dismissed', function () {
    expect(Blade::render('<x-shape::drawer name="c" heading="H" :dismissible="false">Body</x-shape::drawer>'))
        ->toContain('data-shape-persistent')
        ->not->toContain('data-shape-overlay-close');
});
