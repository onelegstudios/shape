<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Onelegstudios\Shape\ShapeServiceProvider;

it('publishes each resource group under its own tag', function (string $tag) {
    expect(ServiceProvider::pathsToPublish(ShapeServiceProvider::class, $tag))->not->toBeEmpty();
})->with([
    'laravel-shape',
    'laravel-shape-config',
    'laravel-shape-views',
    'laravel-shape-components',
    'laravel-shape-css',
    'laravel-shape-js',
]);

it('publishes nothing it has no use for', function (string $tag) {
    // A UI library on the fold path has no translations to ship — a folded
    // component resolves one at compile time and serves that locale to
    // everybody, which is why `__(` is a fold hazard. It has no tables, no
    // routes, and no compiled asset either: the stylesheet is imported from
    // `vendor/` so a consumer's own Tailwind build stays authoritative.
    expect(ServiceProvider::pathsToPublish(ShapeServiceProvider::class, $tag))->toBeEmpty();
})->with([
    'laravel-shape-lang',
    'laravel-shape-assets',
    'laravel-shape-migrations',
]);

it('ships the stylesheet it promises to publish', function () {
    $published = ServiceProvider::pathsToPublish(ShapeServiceProvider::class, 'laravel-shape-css');

    expect(array_key_first($published))->toBeFile();
});

it('ships the script it promises to publish', function () {
    $published = ServiceProvider::pathsToPublish(ShapeServiceProvider::class, 'laravel-shape-js');

    expect(array_key_first($published))->toBeFile();
});

it('ships a script that depends on nothing', function () {
    // It installs as an Alpine plugin because that is where consumers expect to
    // register it, but it neither imports Alpine nor uses the argument Alpine
    // hands it. An application without Alpine calls `shape()` and gets the same
    // behaviour — which is only true while this stays dependency-free.
    $source = (string) file_get_contents(__DIR__.'/../../resources/js/shape.js');

    // Matched at the start of a line: the file's own header shows a consumer how
    // to import it, and that sentence is not a dependency.
    expect(preg_match('/^import\s/m', $source))->toBe(0)
        ->and(preg_match('/\brequire\(/', $source))->toBe(0)
        ->and($source)->toContain('export default function shape');
});

describe('without blaze installed', function () {
    it('compiles the blaze directive away', function () {
        expect(Blade::compileString('@blaze(fold: true, safe: [\'tone\'])'))->toBe('');
    });

    it('renders unblaze blocks inline and binds their scope', function () {
        $html = Blade::render(<<<'BLADE'
        @unblaze(scope: ['name' => $name])
            [{{ $scope['name'] }}]
        @endunblaze
        BLADE, ['name' => 'email']);

        expect(trim($html))->toBe('[email]');
    });

    it('restores an outer scope after an unblaze block', function () {
        $html = Blade::render(<<<'BLADE'
        @php $scope = ['name' => 'outer']; @endphp
        @unblaze(scope: ['name' => 'inner'])
            [{{ $scope['name'] }}]
        @endunblaze
        [{{ $scope['name'] }}]
        BLADE);

        expect(preg_replace('/\s+/', '', $html))->toBe('[inner][outer]');
    });

    it('still renders components that are annotated for folding', function () {
        expect(Blade::render('<x-shape::button variant="primary">Save</x-shape::button>'))
            ->toContain('data-shape-button')
            ->toContain('Save');
    });
});
