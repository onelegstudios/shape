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

it('leaves anchored placement to the script, with no second path to disagree with', function () {
    // The bug this replaced: the stylesheet gated placement on one half of CSS
    // anchor positioning and shape.js gated its own on the other, so a browser
    // with partial support had both of them stand down and the popover rendered
    // wherever it happened to sit.
    // Comments are stripped first: both files explain at length why the
    // declarative path was abandoned, and naming a property is not using it.
    $css = preg_replace('#/\*.*?\*/#s', '', shapeStylesheet()) ?? '';
    $js = preg_replace('#/\*.*?\*/#s', '', (string) file_get_contents(__DIR__.'/../../resources/js/shape.js')) ?? '';

    expect($css)
        ->not->toContain('position-area')
        ->not->toContain('position-try-fallbacks')
        ->and($js)->not->toContain('CSS.supports');
});
