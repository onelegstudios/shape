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

/**
 * Every `match` in the library that branches on a scale, as the words its arms
 * name.
 *
 * Read out of the source rather than out of rendered markup, because what is
 * being checked is the vocabulary: a component that quietly answered to `2xl`
 * or to `tight` would render perfectly well and still leave a caller guessing
 * which words this library takes.
 *
 * @return array<string, list<string>>
 */
function shapeScaleArms(string $prop): array
{
    $arms = [];

    foreach (shapeComponentViews() as $name => $source) {
        $lines = explode("\n", $source);
        $depth = 0;

        foreach ($lines as $number => $line) {
            if ($depth === 0) {
                if (! str_contains($line, "match (\${$prop})")) {
                    continue;
                }

                $depth = 1;

                continue;
            }

            // The arm bodies here are single-line strings, so the first line
            // that closes a brace closes the match.
            if (preg_match('/^\s*[}\]]/', $line) === 1) {
                $depth = 0;

                continue;
            }

            if (preg_match('/^\s*((?:\'[a-z0-9]+\'\s*,\s*)*\'[a-z0-9]+\')\s*=>/', $line, $found) !== 1) {
                continue;
            }

            foreach (explode(',', $found[1]) as $word) {
                $arms[$name.':'.($number + 1)][] = trim($word, " '");
            }
        }
    }

    return $arms;
}

it('answers to one size scale across the whole library', function () {
    // The point of the scale: `size="lg"` is a word a caller learns once. A
    // component with a step of its own — the heading's old `2xl`, the icon's
    // old three — is one the caller has to look up, and looking it up is the
    // cost this test exists to keep at zero.
    $arms = shapeScaleArms('size');

    // Guarded, because a reader that found nothing would pass this silently.
    expect($arms)->not->toBeEmpty();

    $offences = [];

    foreach ($arms as $where => $words) {
        foreach (array_diff($words, ['xs', 'sm', 'base', 'lg', 'xl']) as $word) {
            $offences[] = "{$where}: {$word}";
        }
    }

    expect($offences)->toBe([], 'Every size arm must name one of xs, sm, base, lg, xl.');
});

it('pads from the same scale it sizes from, with room for none at all', function () {
    // `padding` is a scale under another name, so it takes the same words.
    // `none` is the exception and not a sixth step: it says something else owns
    // the inset, which is a different answer rather than a smaller one.
    $arms = shapeScaleArms('padding');

    expect($arms)->not->toBeEmpty();

    $offences = [];

    foreach ($arms as $where => $words) {
        foreach (array_diff($words, ['xs', 'sm', 'base', 'lg', 'xl', 'none']) as $word) {
            $offences[] = "{$where}: {$word}";
        }
    }

    expect($offences)->toBe([], 'Every padding arm must name one of xs, sm, base, lg, xl, none.');
});
