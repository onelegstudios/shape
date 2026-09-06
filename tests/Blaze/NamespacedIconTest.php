<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\View\Component;
use Livewire\Blaze\Blaze;
use Livewire\Blaze\Events\ComponentFolded;
use Onelegstudios\Shape\Tests\TestCase;

/**
 * A set generated into a subdirectory of its own.
 *
 * Nesting is a path and nothing more — each generated file carries its own
 * `@blaze(fold: true, memo: true)`, and Blaze reads that front matter before it
 * reads any path configuration. These are the assertions that keep that true,
 * because "it happens to work today" and "it is guaranteed" are not the same
 * claim.
 */
beforeAll(function () {
    TestCase::$componentsPath = sys_get_temp_dir().'/shape-namespaced-icons-'.getmypid();

    // The provider registers `components_path` as an anonymous component path
    // only when the directory is there, and it reads that at boot — which
    // happens before `beforeEach` gets to generate anything into it. So the
    // directory has to exist before the first test does.
    if (! is_dir(TestCase::$componentsPath.'/icon')) {
        mkdir(TestCase::$componentsPath.'/icon', 0777, true);
    }
});

afterAll(function () {
    if (TestCase::$componentsPath !== null) {
        removeDirectory(TestCase::$componentsPath);
    }

    TestCase::$componentsPath = null;
});

beforeEach(function () {
    $this->destination = (string) TestCase::$componentsPath;

    removeDirectory($this->destination);

    mkdir($this->destination.'/icon', 0777, true);

    Artisan::call('shape:icon', [
        'icons' => ['spinner'],
        '--set' => 'lucide',
        '--from' => __DIR__.'/../fixtures/icons-flat',
        '--namespace' => 'lucide',
    ]);

    // Folding happens while Blade compiles, so every test here starts from a
    // cleared cache — and from a flushed component cache, which outlives it.
    Artisan::call('view:clear');
    Component::flushCache();

    Blaze::throw();
});

it('folds a namespaced icon exactly as it folds a flat one', function () {
    $folded = [];

    Event::listen(ComponentFolded::class, function (ComponentFolded $event) use (&$folded): void {
        $folded[] = $event->name;
    });

    view('namespaced-icon')->render();

    expect($folded)->toContain('shape::icon.lucide.spinner');
});

it('bakes the drawing into the compiled template, leaving nothing to resolve', function () {
    $fixture = __DIR__.'/../fixtures/views/namespaced-icon.blade.php';

    expect(Blaze::compile((string) file_get_contents($fixture), $fixture))
        ->toContain('data-shape-icon')
        ->toContain('size-5')
        ->not->toContain('$__blaze->compile(');
});

it('bakes a namespaced icon into the fold of the component around it', function () {
    // `icon="lucide.spinner"` on a button: the icon doesn't fold on its own, it
    // is rendered as part of the button's fold, and the SVG ends up in the
    // button's own compiled body.
    $fixture = __DIR__.'/../fixtures/views/namespaced-icon-button.blade.php';

    expect(Blaze::compile((string) file_get_contents($fixture), $fixture))
        ->toContain('<svg')
        ->toContain('data-shape-icon')
        ->not->toContain('$__blaze->compile(');
});

it('renders a namespaced icon identically folded and unfolded', function () {
    $call = '<x-shape::icon.lucide.spinner size="sm" />';

    $render = function (string $call): string {
        Artisan::call('view:clear');
        Component::flushCache();

        return trim(Blade::render($call));
    };

    $folded = $render($call);

    Blaze::disable();
    $unfolded = $render($call);
    Blaze::enable();

    expect($folded)->toBe($unfolded);
});
