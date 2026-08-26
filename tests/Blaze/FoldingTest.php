<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Livewire\Blaze\Blaze;
use Livewire\Blaze\Events\ComponentFolded;

/**
 * Folding happens during Blade compilation, so every test here starts from a
 * cleared view cache — otherwise a previously compiled fixture would render
 * without ever going through the Blaze pipeline.
 */
beforeEach(function () {
    Artisan::call('view:clear');

    // Surface fold failures instead of silently falling back to the compiled path.
    Blaze::throw();
});

/**
 * @return list<string>
 */
function foldedComponentsWhileRendering(string $view, array $data = []): array
{
    $folded = [];

    Event::listen(ComponentFolded::class, function (ComponentFolded $event) use (&$folded): void {
        $folded[] = $event->name;
    });

    view($view, $data)->render();

    return $folded;
}

it('folds a button when every prop that drives logic is static', function () {
    expect(foldedComponentsWhileRendering('static-button'))
        ->toContain('shape::button');
});

it('folds icons', function () {
    expect(foldedComponentsWhileRendering('static-icon'))
        ->toContain('shape::icon.check');
});

it('bakes a nested icon into the compiled template', function () {
    // The nested icon doesn't fold on its own — it is rendered as part of the
    // button's fold. What matters is the result: the SVG ends up in the
    // compiled PHP, so nothing resolves an icon component at runtime.
    $fixture = __DIR__.'/../fixtures/views/static-button.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->toContain('<svg')
        ->toContain('data-shape-icon');
});

it('abandons folding when a prop that drives logic is bound dynamically', function () {
    // `variant` selects a match arm, so its value has to be known at compile
    // time. Blaze aborts on its own here — the component still renders, just
    // through the compiled path rather than the folded one.
    expect(foldedComponentsWhileRendering('dynamic-variant-button', ['variant' => 'primary']))
        ->not->toContain('shape::button');
});

it('keeps folding when a pass-through prop is bound dynamically', function () {
    // `color` is only ever interpolated into `data-shape-tone`, never branched
    // on, which is what `safe: ['color']` in the component declares.
    expect(foldedComponentsWhileRendering('dynamic-color-button', ['color' => 'danger']))
        ->toContain('shape::button');
});

it('renders identical markup whether or not the component folded', function () {
    Artisan::call('view:clear');
    $folded = view('dynamic-color-button', ['color' => 'danger'])->render();

    Blaze::disable();
    Artisan::call('view:clear');
    $unfolded = view('dynamic-color-button', ['color' => 'danger'])->render();
    Blaze::enable();

    expect($folded)->toBe($unfolded);
});

it('folds every call site in the typography and surfaces set', function () {
    // Counted rather than merely present. The fixture calls `heading` and
    // `text` more than once, so asserting that the set *contains* each name
    // would pass while one of the two call sites had quietly stopped folding.
    $folded = array_count_values(foldedComponentsWhileRendering('static-typography'));

    expect($folded)->toBe([
        'shape::heading' => 2,
        'shape::text' => 2,
        'shape::card.header' => 1,
        'shape::separator' => 1,
        'shape::badge' => 1,
        'shape::button' => 2,
        'shape::card.footer' => 1,
        'shape::card' => 1,
        'shape::empty' => 1,
    ]);
});

it('leaves nothing in the typography fixture to resolve at runtime', function () {
    // The collective failure this guards against: one unsafe call added to a
    // shared partial drops everything below it off the fold path, and nothing
    // else in the suite notices. A component that did not fold leaves a
    // `$__blaze->compile(` call behind; a fully folded template is just markup.
    $fixture = __DIR__.'/../fixtures/views/static-typography.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('$__blaze->compile(')
        ->toContain('data-shape-card')
        ->toContain('data-shape-icon');
});

it('keeps folding a heading whose level is bound dynamically', function () {
    // `level` only ever reaches the tag name, which is what `safe: ['level']`
    // declares — and what lets document hierarchy be computed at the call site
    // without costing the fold.
    expect(foldedComponentsWhileRendering('dynamic-heading-level', ['level' => 3]))
        ->toContain('shape::heading');
});

it('keeps folding a badge whose label is bound dynamically', function () {
    // `label` is interpolated and nothing more, so it is safe. This is the call
    // site that matters most: a badge in a table almost always has a dynamic
    // label, and without this it would drop to the memo path and miss on every
    // row whose label was unique.
    expect(foldedComponentsWhileRendering('dynamic-badge-label', ['label' => 'Invoice #1042']))
        ->toContain('shape::badge');
});

it('abandons folding a badge whose colour is bound dynamically', function () {
    // Unlike the button, the badge branches on `color` to resolve its state
    // icon, so colour cannot be declared safe here. This is the documented
    // cost of the "never rely on colour alone" rule.
    expect(foldedComponentsWhileRendering('dynamic-badge-color', ['color' => 'danger']))
        ->not->toContain('shape::badge');
});

it('memoizes the slotless components when they cannot fold', function () {
    // Fold and memo are alternatives, not a stack: a folded component is
    // already inlined and has nothing left to cache. Memo is what catches the
    // badge and separator on the call sites where folding gives up.
    $fixture = __DIR__.'/../fixtures/views/dynamic-badge-color.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)->toContain('Memo::key("shape::badge"');
});

it('bakes a state icon into a folded badge', function () {
    $fixture = __DIR__.'/../fixtures/views/static-badge.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->toContain('<svg')
        ->toContain('data-shape-icon');
});

it('renders a self closing component that has been folded', function () {
    // Folding inlines the component's body into its parent, which is where an
    // unguarded `{{ $slot }}` would go looking for a variable that never
    // existed. Self-closing calls have to keep working.
    expect(view('self-closing-button')->render())->toContain('data-shape-button');
});
