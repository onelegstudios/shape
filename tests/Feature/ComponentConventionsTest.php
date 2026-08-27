<?php

declare(strict_types=1);

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
    $missing = array_keys(array_filter(
        shapeComponentViews(),
        fn (string $source) => ! str_starts_with(ltrim($source), '@blaze'),
    ));

    expect($missing)->toBe([], 'Every component must open with an @blaze directive stating its strategy.');
});

it('keeps global state out of components that fold', function () {
    // The checklist Blaze publishes for fold safety. A folded component is
    // pre-rendered at compile time, so anything request-scoped inside it would
    // be baked in and served to everyone.
    $forbidden = [
        'auth(', 'Auth::', '@auth', '@guest',
        'session(', 'Session::',
        'request(', 'Request::',
        '$errors', '@error',
        'now(', 'Carbon::', 'today(',
        '@csrf', 'csrf_token(', 'csrf_field(',
        'config(', 'cache(', 'Cache::',
        // Not on Blaze's own list, and it belongs there: a folded component
        // resolves a translation once, at compile time, and serves that one
        // locale to everybody. Translate at the call site instead.
        '__(', 'trans(', 'trans_choice(', '@lang', 'Lang::',
        'DB::', '::where(', '::find(', '::first(', '::all(', '::count(',
        'app(', 'resolve(',
    ];

    $offences = [];

    foreach (shapeComponentViews() as $name => $source) {
        if (! str_contains($source, 'fold: true')) {
            continue;
        }

        // Anything inside @unblaze is excluded from the fold, so it is allowed.
        $folded = preg_replace('/@unblaze\b.*?@endunblaze/s', '', $source) ?? $source;

        // Blade comments are stripped before the search. Every component here
        // documents the reasoning behind its own annotation, and several of them
        // have to name the thing they are avoiding to explain why they avoid it.
        // A comment never executes, so a match inside one is never the bug this
        // is looking for.
        $folded = preg_replace('/\{\{--.*?--\}\}/s', '', $folded) ?? $folded;

        foreach ($forbidden as $needle) {
            if (str_contains($folded, $needle)) {
                $offences[] = "{$name}: {$needle}";
            }
        }
    }

    expect($offences)->toBe([]);
});
