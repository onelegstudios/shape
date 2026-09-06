<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Onelegstudios\Shape\Tests\TestCase;

/**
 * An ejected component has to resolve ahead of the packaged one.
 *
 * The directory is process-unique and only this file's application knows about
 * it, so the override can't leak into the tests running beside it.
 */
beforeAll(function () {
    $path = sys_get_temp_dir().'/shape-ejected-'.getmypid();

    if (! is_dir($path.'/button')) {
        mkdir($path.'/button', 0777, true);
    }

    file_put_contents($path.'/button/button.blade.php', '<button data-ejected-button>{{ $slot }}</button>');

    TestCase::$componentsPath = $path;
});

afterAll(function () {
    if (TestCase::$componentsPath !== null) {
        removeDirectory(TestCase::$componentsPath);
    }

    TestCase::$componentsPath = null;
});

it('resolves an ejected component ahead of the packaged one', function () {
    $html = Blade::render('<x-shape::button>Save</x-shape::button>');

    expect($html)
        ->toContain('data-ejected-button')
        ->toContain('Save')
        ->not->toContain('data-shape-button');
});

it('leaves components that were not ejected coming from the package', function () {
    expect(Blade::render('<x-shape::icon.shape-checked />'))->toContain('data-shape-icon');
});
