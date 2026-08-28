<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Symfony\Component\Finder\Finder;

/**
 * The previews are the examples, rendered.
 *
 * A documentation page calls `@docs('preview', name: 'button')`, and the docs
 * site renders `docs/previews/button.blade.php` and prints that same file
 * underneath as the example. One file for the picture and the code — which is
 * only worth anything if the file still compiles, so that is asserted here
 * rather than discovered by a reader.
 */
function previewFiles(): array
{
    $files = [];

    foreach (Finder::create()->files()->in(__DIR__.'/../../docs/previews')->name('*.blade.php') as $file) {
        $name = substr($file->getFilename(), 0, -strlen('.blade.php'));

        $files[$name] = (string) file_get_contents($file->getPathname());
    }

    ksort($files);

    return $files;
}

/**
 * @return list<string>
 */
function previewCalls(): array
{
    $names = [];

    foreach (Finder::create()->files()->in(__DIR__.'/../../docs')->name('*.md') as $file) {
        preg_match_all("/@docs\('preview', name: '([^']+)'\)/", (string) file_get_contents($file->getPathname()), $matches);

        $names = [...$names, ...$matches[1]];
    }

    sort($names);

    return $names;
}

it('found the previews', function () {
    expect(previewFiles())->not->toBeEmpty();
});

it('renders every preview', function (string $name) {
    // Not a smoke test: rendering is the assertion. A prop renamed in a
    // component and not in the example fails here, which is the whole reason the
    // example is a Blade file rather than a fenced block of text.
    $html = Blade::render(previewFiles()[$name]);

    expect(trim($html))->not->toBeEmpty();
})->with(fn () => array_keys(previewFiles()));

it('has a file for every preview a page asks for', function () {
    expect(array_values(array_diff(previewCalls(), array_keys(previewFiles()))))->toBe([]);
});

it('has a page for every preview file', function () {
    // The other direction: a preview nothing renders is a file that will rot.
    expect(array_values(array_diff(array_keys(previewFiles()), previewCalls())))->toBe([]);
});
