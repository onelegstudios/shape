<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Onelegstudios\Shape\Tests\TestCase;

/**
 * The two fixtures these tests serve are real archives, shaped the way GitHub's
 * are — one top-level `{repo}-{ref}/` directory, and a pax global header
 * carrying the commit the archive was cut from.
 *
 * `icons-heroicons.tar.gz` holds Heroicons' four drawings of `check` under
 * `optimized/`, the repository's LICENSE, and two files that are the repository
 * rather than the set. `icons-hostile.tar.gz` holds one legitimate drawing
 * beside three entries that try to leave the directory they are unpacked into.
 * It is committed rather than built, because `PharData` refuses to write an
 * entry that names a parent — which is the first half of why extraction is safe,
 * and no help at all in testing the second half.
 */
beforeAll(function () {
    TestCase::$componentsPath = sys_get_temp_dir().'/shape-icon-source-'.getmypid();
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

    $this->cache = storage_path('framework/shape/icons');

    exec('rm -rf '.escapeshellarg($this->cache));

    $this->archive = (string) file_get_contents(__DIR__.'/../fixtures/icons-heroicons.tar.gz');
    $this->hostile = (string) file_get_contents(__DIR__.'/../fixtures/icons-hostile.tar.gz');

    // The commit the valid fixture records in its pax global header.
    $this->commit = '9c8f0a1de2b34c5d6e7f8091a2b3c4d5e6f70819';

    $this->drawings = [
        '16/solid/check.svg', '20/solid/check.svg',
        '24/solid/check.svg', '24/outline/check.svg',
    ];
});

it('generates from a set it fetched rather than one somebody had to clone', function () {
    // The whole point of the change. Heroicons is not a Composer dependency of
    // this package, so before this, "Regenerate; don't hand-edit" asked for a
    // checkout nobody had been told to make.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon', ['--all' => true])->assertSuccessful();

    expect($this->destination.'/icon/check.blade.php')->toBeFile();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))
        ->toContain('viewBox="0 0 16 16"')
        ->toContain('viewBox="0 0 24 24"');
});

it('asks for the whole set in one request', function () {
    // Rate limits are the reason. `--all` over a set of a thousand drawings is
    // one request as a tarball and a thousand as raw files, and the second one
    // does not finish.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon', ['--all' => true])->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertSent(fn ($request): bool => $request->url() === 'https://codeload.github.com/tailwindlabs/heroicons/tar.gz/master');
});

it('pins the resolved commit into every file it writes', function () {
    // A ref is usually a branch, so a header that recorded `master` would say
    // nothing about which drawing is in the file underneath it. The SHA comes
    // out of the archive's own pax header, which costs no second request.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon', ['--all' => true])->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))
        ->toContain('Heroicons (https://heroicons.com), MIT licensed.')
        ->toContain('tailwindlabs/heroicons@'.substr($this->commit, 0, 12))
        ->toContain("Regenerate; don't hand-edit.");

    $lock = json_decode((string) file_get_contents($this->destination.'/icon/shape-icons.json'), true);

    expect($lock['heroicons'])
        ->toMatchArray([
            'repo' => 'tailwindlabs/heroicons',
            'ref' => 'master',
            'commit' => $this->commit,
        ])
        ->and($lock['heroicons']['icons'])->toHaveKey('check');
});

it('fetches one drawing at a time for the few icons that were named', function () {
    // Downloading a repository to answer `shape:icon bell` is the wrong trade,
    // and a 404 on a raw file is the same answer the matrix already models.
    $stubs = ['api.github.com/*' => Http::response($this->commit)];

    foreach ($this->drawings as $drawing) {
        $stubs['raw.githubusercontent.com/tailwindlabs/heroicons/master/optimized/'.$drawing]
            = Http::response(file_get_contents(__DIR__.'/../fixtures/icons/'.$drawing));
    }

    Http::fake($stubs);

    $this->artisan('shape:icon', ['icons' => ['check']])->assertSuccessful();

    expect($this->destination.'/icon/check.blade.php')->toBeFile();

    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'codeload'));
});

it('reads a name the set does not draw as a name the set does not draw', function () {
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response('', 404),
        'api.github.com/*' => Http::response($this->commit),
    ]);

    $this->artisan('shape:icon', ['icons' => ['bicycle']])
        ->expectsOutputToContain('no SVG found')
        ->assertSuccessful();

    expect(file_exists($this->destination.'/icon/bicycle.blade.php'))->toBeFalse();
});

it('unpacks the set and the licence, and not the rest of the repository', function () {
    // A repository is a whole project and a set is one directory of it. The
    // licence travels with the drawings regardless: a consumer redistributing
    // generated components inherits whatever attribution it asks for, and the
    // repository's own words are worth more than a paraphrase.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon', ['--all' => true])->assertSuccessful();

    $root = $this->cache.'/heroicons/master';

    expect($root.'/optimized/24/solid/check.svg')->toBeFile()
        ->and($root.'/LICENSE')->toBeFile()
        ->and(file_exists($root.'/README.md'))->toBeFalse()
        ->and(file_exists($root.'/src/24/solid/check.svg'))->toBeFalse();
});

it('writes nothing outside the directory it unpacks into', function () {
    // Extraction is the one input to this command that can name where it wants
    // to be put. The archive holds `../../../../escaped.svg`, an absolute
    // `/etc/shape-escaped.svg`, and a symlink pointing at `/etc/passwd`.
    Http::fake(['codeload.github.com/*' => Http::response($this->hostile)]);

    $this->artisan('shape:icon', ['--all' => true])->assertSuccessful();

    $escaped = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(storage_path(), FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($files as $file) {
        if (str_contains($file->getFilename(), 'escaped')) {
            $escaped[] = $file->getPathname();
        }
    }

    expect($escaped)->toBe([])
        ->and(file_exists('/etc/shape-escaped.svg'))->toBeFalse();

    // The one legitimate drawing still came through, and the symlink did not
    // survive as a symlink.
    expect($this->cache.'/heroicons/master/optimized/24/solid/check.svg')->toBeFile()
        ->and(is_link($this->cache.'/heroicons/master/optimized/link.svg'))->toBeFalse();
});

it('fails loudly when --offline is asked to work from a cache that is cold', function () {
    // Failing is the entire point of the flag. A run that quietly generated
    // nothing because the cache happened to be empty would be exactly the thing
    // it exists to prevent.
    Http::fake();

    // Naming both the set and the ref, because "nothing cached" is useless
    // without saying what was looked for.
    $this->artisan('shape:icon', ['icons' => ['check'], '--offline' => true])
        ->expectsOutputToContain('[heroicons] at [master]')
        ->assertFailed();

    Http::assertNothingSent();
});

it('works from a warm cache without asking for anything', function () {
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon', ['--all' => true])->assertSuccessful();

    Http::fake();

    $this->artisan('shape:icon', ['--all' => true, '--offline' => true, '--force' => true])
        ->assertSuccessful();

    Http::assertNothingSent();

    expect($this->destination.'/icon/check.blade.php')->toBeFile();
});

it('reads the ref it was asked for rather than the one the set declares', function () {
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon', ['--all' => true, '--ref' => 'v2.1.5'])->assertSuccessful();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://codeload.github.com/tailwindlabs/heroicons/tar.gz/v2.1.5');

    expect($this->cache.'/heroicons/v2.1.5/optimized/24/solid/check.svg')->toBeFile();
});

it('says so when GitHub will not answer', function () {
    Http::fake(['codeload.github.com/*' => Http::response('', 404)]);

    $this->artisan('shape:icon', ['--all' => true])
        ->expectsOutputToContain('404')
        ->assertFailed();
});
