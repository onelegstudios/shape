<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('wraps its trigger and renders the tip as a sibling in the top layer', function () {
    $html = Blade::render(<<<'BLADE'
    <x-shape::tooltip name="archive-tip" text="Archive this project">
        <x-shape::button square icon="check" aria-label="Archive" />
    </x-shape::tooltip>
    BLADE);

    expect($html)
        ->toContain('data-shape-tooltip-for="archive-tip"')
        ->toContain('id="archive-tip"')
        ->toContain('role="tooltip"')
        ->toContain('Archive this project')
        ->toContain('data-shape-button');
});

it('is a manual popover, because an auto one dismisses on the click it describes', function () {
    expect(Blade::render('<x-shape::tooltip name="t" text="Tip">x</x-shape::tooltip>'))
        ->toContain('popover="manual"');
});

it('does not render aria-describedby on the wrapper', function () {
    // A tooltip describes the control. shape.js puts the reference on the first
    // focusable element inside, which is what a screen reader lands on; a span
    // around a button is not.
    expect(Blade::render('<x-shape::tooltip name="t" text="Tip"><button>x</button></x-shape::tooltip>'))
        ->not->toContain('aria-describedby');
});

it('never receives the pointer it is describing', function () {
    expect(Blade::render('<x-shape::tooltip name="t" text="Tip">x</x-shape::tooltip>'))
        ->toContain('pointer-events-none');
});

it('sits above its trigger by default', function () {
    expect(Blade::render('<x-shape::tooltip name="t" text="Tip">x</x-shape::tooltip>'))
        ->toContain('data-shape-placement="top"');
});
