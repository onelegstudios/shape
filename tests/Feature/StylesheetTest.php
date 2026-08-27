<?php

declare(strict_types=1);

function shapeStylesheet(): string
{
    return (string) file_get_contents(__DIR__.'/../../resources/css/shape.css');
}

it('keeps overlay placement in a layer that outranks utilities', function () {
    // Cascade layers are resolved before specificity, so `@layer utilities`
    // beats every rule in `@layer components` however it is written. A parent's
    // `space-y-*` sets `margin-block-end` on its children — including a modal —
    // and no amount of specificity in the components layer can win that.
    //
    // `shape-overlay` is declared after the Tailwind import, so it sorts last.
    // Moving these declarations back into `@layer components` compiles fine and
    // silently un-centres every modal inside a spaced section.
    $css = shapeStylesheet();

    expect($css)->toContain('@layer shape-overlay');

    $placement = substr($css, strpos($css, '@layer shape-overlay'));

    expect($placement)
        ->toContain('[data-shape-modal] {')
        ->toContain('margin: auto;')
        ->toContain("[data-shape-side='right']");
});

it('declares that layer after the components layer it has to outrank', function () {
    $css = shapeStylesheet();

    expect(strpos($css, '@layer shape-overlay'))->toBeGreaterThan(strpos($css, '@layer components'));
});

it('scans the package views so utilities used in vendor blade survive purging', function () {
    expect(shapeStylesheet())->toContain('@source "../views"');
});

it('asks the same feature question in the stylesheet and in the script', function () {
    // Anchored placement is split across two files: the stylesheet does it
    // declaratively where the browser can, and shape.js does it by hand where it
    // cannot. Each gate decides whether *it* is responsible, so if they can
    // disagree, one day they will — and the failure is both standing down. A
    // popover then renders at its static position, which looks like a menu
    // opening upwards.
    //
    // The first version of this gated on `anchor-name`, which only names an
    // anchor. Safari understood that and not `position-area`, so the stylesheet
    // placed nothing while the script believed the stylesheet had it covered.
    $css = shapeStylesheet();
    $js = (string) file_get_contents(__DIR__.'/../../resources/js/shape.js');

    preg_match('/@supports \(([^)]+)\) and \(([^)]+)\)/', $css, $matches);

    $inCss = array_map(fn (string $test) => trim(explode(':', $test)[0]), [$matches[1], $matches[2]]);

    preg_match_all("/CSS\.supports\?\.\('([a-z-]+)'/", $js, $found);

    $inJs = array_values(array_unique($found[1]));

    expect($inCss)->toBe(['position-area', 'position-try-fallbacks'])
        ->and($inJs)->toBe($inCss);
});
