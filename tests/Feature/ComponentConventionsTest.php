<?php

declare(strict_types=1);

use Onelegstudios\Shape\FoldSafety;
use Symfony\Component\Finder\Finder;

/**
 * @return array<string, string>
 */
function shapeComponentViews(): array
{
    $views = [];

    foreach (Finder::create()->files()->in(__DIR__.'/../../resources/views/shape')->name('*.blade.php') as $file) {
        $views[$file->getRelativePathname()] = (string) file_get_contents($file->getPathname());
    }

    return $views;
}

it('found the component views', function () {
    expect(shapeComponentViews())->not->toBeEmpty();
});

it('declares a blaze strategy on every component', function () {
    $safety = new FoldSafety;

    $missing = array_keys(array_filter(
        shapeComponentViews(),
        fn (string $source) => ! $safety->declaresStrategy($source),
    ));

    expect($missing)->toBe([], 'Every component must open with an @blaze directive stating its strategy.');
});

it('keeps global state out of components that fold', function () {
    // The rules live in `FoldSafety` because `shape:doctor` holds a consumer's
    // ejected components to them. This is the same check, run over the
    // components the package ships — one list, and the library is inside it.
    $safety = new FoldSafety;

    $offences = [];

    foreach (shapeComponentViews() as $name => $source) {
        foreach ($safety->inspect($source) as $offence) {
            $offences[] = "{$name}:{$offence['line']}: {$offence['pattern']}";
        }
    }

    expect($offences)->toBe([]);
});
