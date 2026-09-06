<?php

declare(strict_types=1);

use Onelegstudios\Shape\IconSlots;
use Onelegstudios\Shape\Tests\TestCase;

beforeAll(function () {
    TestCase::$componentsPath = sys_get_temp_dir().'/shape-doctor-'.getmypid();
});

afterAll(function () {
    if (TestCase::$componentsPath !== null) {
        removeDirectory(TestCase::$componentsPath);
    }

    TestCase::$componentsPath = null;
});

beforeEach(function () {
    $this->path = (string) TestCase::$componentsPath;

    removeDirectory($this->path);

    mkdir($this->path, 0777, true);
});

function writeComponent(string $name, string $source): void
{
    file_put_contents((string) TestCase::$componentsPath.'/'.$name, $source);
}

/**
 * Stand in an ejected icon set of `$names`, as `shape:icon` would have left it.
 *
 * The notice is what the command writes verbatim from the set's config, and is
 * how the report knows which set is on the page.
 *
 * @param  list<string>  $names
 */
function writeIcons(array $names, string $notice = 'Lucide (https://lucide.dev), ISC licensed.'): void
{
    $directory = (string) TestCase::$componentsPath.'/icon';

    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    foreach ($names as $name) {
        file_put_contents(
            $directory.'/'.$name.'.blade.php',
            "@blaze(fold: true, memo: true)\n\n{{-- {$notice} Regenerate; don't hand-edit. --}}\n\n<svg />\n",
        );
    }
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

    removeDirectory($elsewhere);

    mkdir($elsewhere, 0777, true);

    file_put_contents($elsewhere.'/card.blade.php', "@blaze(fold: true)\n\n<div>{{ config('app.name') }}</div>");

    $this->artisan('shape:doctor', ['--path' => [$elsewhere]])
        ->expectsOutputToContain('config(')
        ->assertFailed();

    removeDirectory($elsewhere);
});

it('has nothing to check when nothing has been ejected', function () {
    removeDirectory($this->path);

    $this->artisan('shape:doctor')
        ->expectsOutputToContain('No components to check')
        ->assertSuccessful();
});

it('says which slots a replacement set left behind', function () {
    // Thirteen of fourteen renders perfectly. The fourteenth resolves to the
    // Heroicon this package ships and draws in the wrong set, on a page now
    // wearing two, with nothing anywhere to say so — which is exactly the kind
    // of mistake this command exists for.
    $slots = IconSlots::fromConfig()->names();

    writeIcons(array_values(array_diff($slots, ['shape-info'])));

    // One expectation per line written: the coverage header, the slot that is
    // missing, and the failure it adds up to.
    $this->artisan('shape:doctor')
        ->expectsOutputToContain('lucide, '.(count($slots) - 1).' of '.count($slots).' slots')
        ->expectsOutputToContain('shape-info')
        ->expectsOutputToContain('are not in your set')
        ->assertFailed();
});

it('passes a replacement set that fills every slot', function () {
    writeIcons(IconSlots::fromConfig()->names());

    $this->artisan('shape:doctor')
        ->expectsOutputToContain('lucide, 14 of 14 slots')
        ->assertSuccessful();
});

it('reports a packaged slot as packaged rather than as missing', function () {
    // Heroicons has nothing that reads as a loader, so a user on Heroicons is
    // permanently one file short of the list — and telling them to fix the
    // answer they already have is worse than saying nothing. It is not a
    // finding, and the "renders in Heroicons" line must not appear against it,
    // because there the packaged drawing is the right answer.
    $slots = IconSlots::fromConfig()->names();

    writeIcons(array_values(array_diff($slots, ['shape-loading'])));

    // One expectation per line written — two substrings of the same line would
    // match the same write and only one of them would be marked seen.
    $this->artisan('shape:doctor')
        ->expectsOutputToContain('lucide, 13 of 14 slots')
        ->expectsOutputToContain('shape-loading ')
        ->doesntExpectOutputToContain('falls back to Heroicons')
        ->assertSuccessful();
});

it('names both sets when an icon directory is wearing two', function () {
    // Arrived at from the other direction: full coverage, and still a page in
    // two icon sets. Worth printing on its own.
    $slots = IconSlots::fromConfig()->names();

    writeIcons(array_values(array_diff($slots, ['shape-warning'])));
    writeIcons(['shape-warning'], 'Heroicons (https://heroicons.com), MIT licensed.');

    $this->artisan('shape:doctor')
        ->expectsOutputToContain('lucide and hero, 14 of 14 slots')
        ->assertSuccessful();
});

it('reports the icons outside the slots without calling them a problem', function () {
    // The extras, and anything the application has generated for itself.
    // `shape:icon:replace` skips them correctly — nothing in Shape resolves
    // them — but they are in the same directory in whatever set drew them, and
    // silence about that reads as approval.
    writeIcons(IconSlots::fromConfig()->names());
    writeIcons(['trash', 'bell']);

    $this->artisan('shape:doctor')
        ->expectsOutputToContain('lucide, 14 of 14 slots')
        ->expectsOutputToContain('bell, trash')
        ->assertSuccessful();
});

it('has no opinion about icons until something has replaced a slot', function () {
    // An application on the packaged icons is not partially covered; it is
    // covered. A directory with icons in it that are none of the library's own
    // is a supplementary set, which is the other supported thing to do.
    writeIcons(['bell', 'sparkles']);

    $this->artisan('shape:doctor')
        ->doesntExpectOutputToContain('icon set')
        ->assertSuccessful();
});

it('fails on a slot a component draws that the library never declared', function () {
    // The property `Registry::icons()` inverted to hold. A component that starts
    // drawing an undeclared slot would otherwise resolve quietly to whatever the
    // package ships, which is the silence this whole command exists to break.
    writeIcons(IconSlots::fromConfig()->names());

    config()->set('shape.icon_slots', array_diff_key(
        (array) config('shape.icon_slots'),
        ['shape-warning' => null],
    ));

    $this->artisan('shape:doctor')
        ->expectsOutputToContain('shape-warning ')
        ->expectsOutputToContain('are not declared')
        ->assertFailed();
});
