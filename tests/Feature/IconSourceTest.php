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

/**
 * An archive shaped the way a set that files its drawings by category is,
 * wrapped in the one top-level directory GitHub's tarballs carry.
 *
 * Built here rather than committed: unlike the hostile fixture, there is nothing
 * about it `PharData` refuses to write, and a reader can see what is in it.
 *
 * @param  array<string, string>  $files
 */
function nestedArchive(array $files): string
{
    // A path of its own per archive: `PharData` caches by filename for the life
    // of the process, so a second archive written to a path already read would
    // hand back the first one.
    $path = sys_get_temp_dir().'/shape-icons-nested-'.getmypid().'-'.uniqid();

    $archive = new PharData($path.'.tar');

    foreach ($files as $entry => $contents) {
        $archive->addFromString('RemixIcon-master/'.$entry, $contents);
    }

    $archive->compress(Phar::GZ);

    $tarball = (string) file_get_contents($path.'.tar.gz');

    unset($archive);

    @unlink($path.'.tar');
    @unlink($path.'.tar.gz');

    return $tarball;
}

/**
 * A set whose drawings are filed under something their names do not say, which
 * is the layout no pattern can express and the reason `flatten` exists.
 *
 * Named afresh each time, because a set's name is its cache directory and
 * `PharData` holds every archive it has opened by filename for the life of the
 * process — so a second test unpacking a second archive at one path would be
 * handed the first one back.
 *
 * @param  array<string, array<string, string>>  $styles
 */
function nestedSet(bool $flatten = true, ?array $styles = null): string
{
    $name = 'remix-'.uniqid();

    config()->set('shape.icon_sets.'.$name, [
        'repo' => 'Remix-Design/RemixIcon',
        'ref' => 'master',
        'path' => 'icons',
        'flatten' => $flatten,
        'notice' => 'Remix Icon (https://remixicon.com), Remix Icon License v1.0.',
        'styles' => $styles ?? [
            'outline' => ['base' => '{name}-line.svg'],
            'solid' => ['base' => '{name}-fill.svg'],
        ],
    ]);

    return $name;
}

it('reads a set that files its drawings by category', function () {
    // The layout a pattern cannot express: `close-line` is under `System` and
    // nothing about the name says so. Flattening answers it on the way in, so
    // what the pattern reads is an ordinary flat set.
    $set = nestedSet();

    Http::fake(['codeload.github.com/*' => Http::response(nestedArchive([
        'icons/System/close-line.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" data-drawn="close-line" /></svg>',
        'icons/System/close-fill.svg' => '<svg viewBox="0 0 24 24"><path d="M1 1" data-drawn="close-fill" /></svg>',
        'icons/User & Faces/user-line.svg' => '<svg viewBox="0 0 24 24"><path d="M2 2" data-drawn="user-line" /></svg>',
        'License' => 'Remix Icon License v1.0',
    ]))]);

    $this->artisan('shape:icon', ['icons' => ['close'], '--set' => $set])->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/close.blade.php'))
        ->toContain('data-drawn="close-line"')
        ->toContain('data-drawn="close-fill"')
        ->toContain('Remix Icon');

    // The categories are gone from the cache, and the licence is not among the
    // drawings — it belongs to the repository rather than to the set. It is
    // kept whatever case the repository spells it in: Remix Icon's is `License`,
    // and it is the one file that states the terms the drawings arrive under.
    $root = $this->cache.'/'.$set.'/master';

    expect($root.'/icons/close-line.svg')->toBeFile()
        ->and($root.'/icons/user-line.svg')->toBeFile()
        ->and($root.'/License')->toBeFile()
        ->and(file_exists($root.'/icons/System/close-line.svg'))->toBeFalse();
});

it('fetches the whole of a flattening set even for one name', function () {
    // There is no path a raw fetch could ask for. Where a drawing sits is a
    // category the name says nothing about, so the archive has to be in hand
    // before anything can be found in it — whether the run wanted one icon or
    // all of them.
    $set = nestedSet();

    Http::fake(['codeload.github.com/*' => Http::response(nestedArchive([
        'icons/System/close-line.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
    ]))]);

    $this->artisan('shape:icon', ['icons' => ['close'], '--set' => $set])->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'raw.githubusercontent'));
});

it('walks a flattened set under its own names', function () {
    $set = nestedSet();

    Http::fake(['codeload.github.com/*' => Http::response(nestedArchive([
        'icons/System/close-line.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
        'icons/System/close-fill.svg' => '<svg viewBox="0 0 24 24"><path d="M1 1" /></svg>',
        'icons/User & Faces/user-line.svg' => '<svg viewBox="0 0 24 24"><path d="M2 2" /></svg>',
    ]))]);

    // `--all` reads the flattened directory the way it reads any flat set: the
    // style suffix is the set's own, so `close-line` and `close-fill` are one
    // icon in two styles rather than two icons.
    $this->artisan('shape:icon', ['--all' => true, '--set' => $set])->assertSuccessful();

    expect($this->destination.'/icon/close.blade.php')->toBeFile()
        ->and($this->destination.'/icon/user.blade.php')->toBeFile()
        ->and(file_exists($this->destination.'/icon/close-line.blade.php'))->toBeFalse();
});

it('refuses to flatten a set whose filenames are not unique', function () {
    // The one way flattening can lose a drawing, and it would lose it silently:
    // the second write wins and the set is quietly an icon short.
    $set = nestedSet();

    Http::fake(['codeload.github.com/*' => Http::response(nestedArchive([
        'icons/System/close-line.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
        'icons/Editor/close-line.svg' => '<svg viewBox="0 0 24 24"><path d="M1 1" /></svg>',
    ]))]);

    $this->artisan('shape:icon', ['icons' => ['close'], '--set' => $set])
        ->expectsOutputToContain('cannot be read flat')
        ->assertFailed();
});

it('keeps a set that says nothing about flattening as the repository lays it out', function () {
    // The default, and what every set configured today does: a layout a pattern
    // can express is read where it is, rather than moved.
    $set = nestedSet(flatten: false, styles: ['outline' => ['base' => 'System/{name}-line.svg']]);

    Http::fake(['codeload.github.com/*' => Http::response(nestedArchive([
        'icons/System/close-line.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
    ]))]);

    $this->artisan('shape:icon', ['--all' => true, '--set' => $set])->assertSuccessful();

    expect($this->cache.'/'.$set.'/master/icons/System/close-line.svg')->toBeFile()
        ->and($this->destination.'/icon/close.blade.php')->toBeFile();
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

it('keeps the fetched sets out of the consumer\'s history', function () {
    // Thousands of files nobody wrote, under a storage path Laravel's own
    // ignore rules do not reach. The pattern covers the file itself, so there
    // is nothing to commit rather than one stray `.gitignore` to explain.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon', ['--all' => true])->assertSuccessful();

    expect($this->cache.'/.gitignore')->toBeFile()
        ->and(file_get_contents($this->cache.'/.gitignore'))->toBe("*\n");
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
