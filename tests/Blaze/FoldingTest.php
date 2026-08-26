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

it('renders a self closing component that has been folded', function () {
    // Folding inlines the component's body into its parent, which is where an
    // unguarded `{{ $slot }}` would go looking for a variable that never
    // existed. Self-closing calls have to keep working.
    expect(view('self-closing-button')->render())->toContain('data-shape-button');
});
