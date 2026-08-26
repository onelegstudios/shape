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

it('keeps a caller\'s style alongside the anchor it needs', function () {
    // Forwarding into a component is not merging onto an element: the last value
    // for a key wins, so the two are concatenated by hand.
    expect(Blade::render('<x-shape::popover.trigger for="p" style="width: 10rem">Usage</x-shape::popover.trigger>'))
        ->toContain('anchor-name: --shape-p')
        ->toContain('width: 10rem');
});

it('takes no role unless it is given one', function () {
    expect(Blade::render('<x-shape::popover name="p">Body</x-shape::popover>'))
        ->not->toContain('role=');
});
