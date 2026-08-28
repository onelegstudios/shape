<?php

declare(strict_types=1);

use Onelegstudios\Shape\Tests\TestCase;

beforeAll(function () {
    TestCase::$componentsPath = sys_get_temp_dir().'/shape-doctor-'.getmypid();
});

afterAll(function () {
    if (TestCase::$componentsPath !== null) {
        exec('rm -rf '.escapeshellarg(TestCase::$componentsPath));
    }

    TestCase::$componentsPath = null;
});

beforeEach(function () {
    $this->path = (string) TestCase::$componentsPath;

    exec('rm -rf '.escapeshellarg($this->path));

    mkdir($this->path, 0777, true);
});

function writeComponent(string $name, string $source): void
{
    file_put_contents((string) TestCase::$componentsPath.'/'.$name, $source);
}

it('passes the components the package itself ships', function () {
    // The same assertion `ComponentConventionsTest` makes, made through the
    // command a consumer runs — so the library is held to the rule it publishes.
    $this->artisan('shape:doctor', ['--package' => true])
        ->expectsOutputToContain('folds cleanly')
        ->assertSuccessful();
});

it('finds global state in an ejected component', function () {
    writeComponent('greeting.blade.php', <<<'BLADE'
    @blaze(fold: true)

    <p>Hello, {{ auth()->user()->name }}</p>
    BLADE);

    // One assertion rather than two: the file, the line and the pattern are
    // written as a single line, and a substring expectation is matched against
    // one write at a time.
    $this->artisan('shape:doctor')
        ->expectsOutputToContain('greeting.blade.php:3 auth(')
        ->assertFailed();
});

it('leaves a component that never folds alone', function () {
    // Memoization caches a rendered component per prop set at run time, so it
    // sees the request it was rendered in. None of the fold rules apply.
    writeComponent('clock.blade.php', <<<'BLADE'
    @blaze(memo: true)

    <time>{{ now() }}</time>
    BLADE);

    $this->artisan('shape:doctor')->assertSuccessful();
});

it('allows global state inside the hole a component cuts on purpose', function () {
    writeComponent('error.blade.php', <<<'BLADE'
    @blaze(fold: true)

    @unblaze(scope: ['name' => $name])
        {{ $errors->first($scope['name']) }}
    @endunblaze
    BLADE);

    $this->artisan('shape:doctor')->assertSuccessful();
});

it('ignores a pattern named in a comment', function () {
    writeComponent('note.blade.php', <<<'BLADE'
    @blaze(fold: true)

    {{-- Deliberately does not call session(), which would be baked in. --}}

    <p>Fine.</p>
    BLADE);

    $this->artisan('shape:doctor')->assertSuccessful();
});

it('reports a component that never states a strategy', function () {
    writeComponent('quiet.blade.php', '<p>No annotation.</p>');

    $this->artisan('shape:doctor')
        ->expectsOutputToContain('no @blaze annotation')
        ->assertFailed();
});

it('checks a directory it is pointed at', function () {
    $elsewhere = sys_get_temp_dir().'/shape-doctor-elsewhere-'.getmypid();

    exec('rm -rf '.escapeshellarg($elsewhere));

    mkdir($elsewhere, 0777, true);

    file_put_contents($elsewhere.'/card.blade.php', "@blaze(fold: true)\n\n<div>{{ config('app.name') }}</div>");

    $this->artisan('shape:doctor', ['--path' => [$elsewhere]])
        ->expectsOutputToContain('config(')
        ->assertFailed();

    exec('rm -rf '.escapeshellarg($elsewhere));
});

it('has nothing to check when nothing has been ejected', function () {
    exec('rm -rf '.escapeshellarg($this->path));

    $this->artisan('shape:doctor')
        ->expectsOutputToContain('No components to check')
        ->assertSuccessful();
});
