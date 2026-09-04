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

it('answers the empty-state question in CSS, because asking it in Blade costs the fold', function () {
    // A table and a list both promise an empty state when they have nothing in
    // them. Inspecting a slot to find out is a runtime question, and both
    // components would stop folding for it. So the empty state is always
    // rendered and `:has()` removes it — which is a `display` gated on state,
    // and the only kind of thing this file is allowed to contain.
    $css = shapeStylesheet();

    expect($css)
        ->toContain('[data-shape-table]:has(tbody [data-shape-table-row]) [data-shape-table-empty]')
        ->toContain('[data-shape-list]:has([data-shape-list-item]) [data-shape-list-empty]');
});

it('keeps that rule in the components layer, where a caller can still override it', function () {
    // It is not placement, so it does not belong in the layer that outranks
    // utilities. A caller who wants to see their own empty state regardless
    // should be able to say so with a utility class.
    $css = shapeStylesheet();

    expect(strpos($css, '[data-shape-list-empty]'))->toBeLessThan(strpos($css, '@layer shape-overlay'));
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

it('gives info a ramp of its own rather than pointing it at the accent', function () {
    // The decision this guards: the accent is the one ramp an application is
    // invited to move — theming.md walks it to violet, and the seed layer
    // derives it from a single brand colour — so an informational message that
    // reads the accent stops being blue the moment somebody brands the product.
    // Info is aliased to Tailwind's blue for the reason danger is aliased to red.
    $css = shapeStylesheet();

    expect($css)
        ->toContain('--color-shape-info-700: var(--color-blue-700')
        ->toContain("[data-shape-tone='info']")
        ->toContain("[data-shape-surface='info']")
        ->and(preg_match('/--color-shape-info-[0-9]+: var\(--color-shape-accent/', $css))->toBe(0);
});

it('keeps info out of the seed layer, where the accent and the neutrals are derived', function () {
    // A seed that reached info would put the brand's hue back on the one tone
    // that exists to be independent of it — the same rule that keeps danger red
    // and success green under any seed.
    $seed = (string) file_get_contents(__DIR__.'/../../resources/css/shape-seed.css');

    expect(preg_replace('#/\*.*?\*/#s', '', $seed))->not->toContain('--color-shape-info-');
});
