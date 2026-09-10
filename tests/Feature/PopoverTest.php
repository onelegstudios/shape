<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is an auto popover, which is where light dismiss and Escape come from', function () {
    $html = Blade::render('<x-shape::popover name="usage">Anything</x-shape::popover>');

    expect($html)
        ->toContain('popover')
        ->toContain('id="usage"')
        ->toContain('data-shape-popover')
        // `popover="manual"` would opt out of both.
        ->not->toContain('popover="manual"');
});

it('places itself from an attribute both the stylesheet and the fallback read', function (string $placement) {
    expect(Blade::render("<x-shape::popover name=\"p\" placement=\"{$placement}\">Body</x-shape::popover>"))
        ->toContain("data-shape-placement=\"{$placement}\"");
})->with(['bottom-start', 'bottom-end', 'top-start', 'top-end', 'top', 'bottom']);

it('passes a caller\'s style straight through', function () {
    // It used to have to concatenate the caller's style with an anchor name.
    // With placement in JavaScript there is nothing of ours on this attribute.
    expect(Blade::render('<x-shape::popover.trigger for="p" style="width: 10rem">Usage</x-shape::popover.trigger>'))
        ->toContain('width: 10rem');
});

it('takes no role unless it is given one', function () {
    expect(Blade::render('<x-shape::popover name="p">Body</x-shape::popover>'))
        ->not->toContain('role=');
});

it('pads itself from the library\'s scale rather than a pair of moods', function (string $padding, string $expected) {
    // `tight` and nothing else was the old pair, and a panel that wanted a step
    // between them had no word for it. Five steps, the same five every other
    // component answers to, and the menu takes `sm` like anything else would.
    expect(Blade::render("<x-shape::popover name=\"p\" padding=\"{$padding}\">Body</x-shape::popover>"))
        ->toContain($expected);
})->with([
    ['xs', '[:where(&amp;)]:p-1'],
    ['sm', '[:where(&amp;)]:p-1.5'],
    ['base', '[:where(&amp;)]:p-3'],
    ['lg', '[:where(&amp;)]:p-4'],
    ['xl', '[:where(&amp;)]:p-5'],
]);
