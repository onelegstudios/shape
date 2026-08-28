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

it('places the toaster in that layer too, for the same reason', function () {
    // A toaster written inside a spaced section is a child like any other, and a
    // margin on a popover is an offset from the corner it was asked to sit in.
    $css = shapeStylesheet();

    $placement = substr($css, strpos($css, '@layer shape-overlay'));

    expect($placement)
        ->toContain('[data-shape-toaster] {')
        ->toContain("[data-shape-position='bottom-right']");
});

it('sets no z-index anywhere, because everything that floats is in the top layer', function () {
    // Every overlay surface in the library — dialogs, popovers, and now the
    // toaster — is in the top layer, which is above every stacking context and
    // cannot be reached by a z-index at all. The moment one appears here, some
    // component has stopped using the platform's own primitive.
    //
    // Comments are stripped first, as in the assertion below: the stylesheet
    // explains at length why it has no z-index, and saying so is not doing so.
    $css = (string) preg_replace('#/\*.*?\*/#s', '', shapeStylesheet());

    expect($css)->not->toContain('z-index');
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
