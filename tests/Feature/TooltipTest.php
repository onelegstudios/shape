<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('wraps its trigger and renders the tip as a sibling in the top layer', function () {
    $html = Blade::render(<<<'BLADE'
    <x-shape::tooltip name="archive-tip" text="Archive this project">
        <x-shape::button square icon="shape-checked" aria-label="Archive" />
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

it('moves the type, the inset and the measure together', function (string $size, string $classes) {
    // The measure is the one easy to forget: a tooltip set larger inside the
    // same 16rem box is a paragraph, and a tooltip is read in one glance.
    expect(Blade::render("<x-shape::tooltip name=\"tip\" text=\"Delete this project\" size=\"{$size}\"><button>x</button></x-shape::tooltip>"))
        ->toContain($classes)
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['xs', 'max-w-2xs [:where(&amp;)]:px-1.5 [:where(&amp;)]:py-0.5 [:where(&amp;)]:text-2xs'],
    ['sm', 'max-w-2xs [:where(&amp;)]:px-2 [:where(&amp;)]:py-0.5 [:where(&amp;)]:text-xs'],
    ['base', 'max-w-2xs [:where(&amp;)]:px-2 [:where(&amp;)]:py-1 [:where(&amp;)]:text-xs'],
    ['lg', 'max-w-xs [:where(&amp;)]:px-2.5 [:where(&amp;)]:py-1.5 [:where(&amp;)]:text-sm'],
    ['xl', 'max-w-sm [:where(&amp;)]:px-3 [:where(&amp;)]:py-2 [:where(&amp;)]:text-base'],
]);
