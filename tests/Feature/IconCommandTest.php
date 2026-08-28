<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Onelegstudios\Shape\Tests\TestCase;

beforeAll(function () {
    TestCase::$componentsPath = sys_get_temp_dir().'/shape-icons-'.getmypid();
});

afterAll(function () {
    if (TestCase::$componentsPath !== null) {
        exec('rm -rf '.escapeshellarg(TestCase::$componentsPath));
    }

    TestCase::$componentsPath = null;
});

beforeEach(function () {
    $this->destination = (string) TestCase::$componentsPath;

    exec('rm -rf '.escapeshellarg($this->destination));

    mkdir($this->destination.'/icon', 0777, true);

    $this->heroicons = __DIR__.'/../fixtures/icons';
    $this->flat = __DIR__.'/../fixtures/icons-flat';
});

it('reproduces a shipped icon from the SVGs it was drawn from', function () {
    // The header on every icon says "Regenerate; don't hand-edit". This is the
    // assertion that makes that sentence true rather than aspirational: the
    // fixture holds Heroicons' four source files for `check`, and what comes out
    // has to be the file this package ships, byte for byte.
    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))
        ->toBe(file_get_contents(__DIR__.'/../../resources/views/shape/icon/check.blade.php'));
});

it('generates an icon that renders at every variant', function () {
    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])->assertSuccessful();

    expect(Blade::render('<x-shape::icon.check />'))
        ->toContain('data-shape-icon')
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('stroke="currentColor"');

    expect(Blade::render('<x-shape::icon.check variant="micro" />'))
        ->toContain('viewBox="0 0 16 16"')
        ->toContain('size-4');
});

it('drops the attributes that describe how a drawing is used', function () {
    $this->artisan('shape:icon', ['icons' => ['spinner'], '--from' => $this->flat])->assertSuccessful();

    $source = (string) file_get_contents($this->destination.'/icon/spinner.blade.php');

    // `width`, `height` and `class` are this library's business, and they arrive
    // through the attribute bag rather than from the file it was drawn in.
    expect($source)
        ->not->toContain('width="24"')
        ->not->toContain('height="24"')
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('$attributes->merge');
});

it('writes no switch for a set that has one drawing per name', function () {
    $this->artisan('shape:icon', ['icons' => ['spinner'], '--from' => $this->flat])->assertSuccessful();

    $source = (string) file_get_contents($this->destination.'/icon/spinner.blade.php');

    expect($source)->not->toContain('switch');

    // Both elements survive, and the source's own indentation does not.
    expect(Blade::render('<x-shape::icon.spinner />'))
        ->toContain('<circle')
        ->toContain('<path');
});

it('finds every icon in the source directory', function () {
    $this->artisan('shape:icon', ['--from' => $this->heroicons, '--all' => true])
        ->expectsOutputToContain('check')
        ->assertSuccessful();

    expect($this->destination.'/icon/check.blade.php')->toBeFile();
});

it('says so when a name has no drawing', function () {
    $this->artisan('shape:icon', ['icons' => ['bicycle'], '--from' => $this->heroicons])
        ->expectsOutputToContain('no SVG found')
        ->assertSuccessful();

    expect(file_exists($this->destination.'/icon/bicycle.blade.php'))->toBeFalse();
});

it('keeps an icon that is already there unless forced', function () {
    file_put_contents($this->destination.'/icon/check.blade.php', 'mine');

    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])
        ->expectsOutputToContain('exists, kept')
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))->toBe('mine');

    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons, '--force' => true])
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))->toContain('@blaze');
});

it('needs a directory to read from', function () {
    $this->artisan('shape:icon', ['icons' => ['check']])
        ->expectsOutputToContain('Pass --from')
        ->assertFailed();
});
