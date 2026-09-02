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

    // The shipped `hero` entry is read from its published package now, and
    // the fixtures here are archives of a *repository*. What these tests are
    // about is that source — the tarball, the raw fetch, the unpacking, the
    // refusal to write outside itself — so the set is put back into the form
    // that reads one. The npm tests below configure sets of their own.
    config()->set('shape.icon_sets.hero.npm', null);
    config()->set('shape.icon_sets.hero.repo', 'tailwindlabs/heroicons');
    config()->set('shape.icon_sets.hero.ref', 'master');
    config()->set('shape.icon_sets.hero.path', 'optimized');
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
function packageArchive(array $files): string
{
    return archiveWith('package/', $files);
}

/**
 * @param  array<string, string>  $files
 */
function nestedArchive(array $files): string
{
    return archiveWith('RemixIcon-master/', $files);
}

/**
 * @param  array<string, string>  $files
 */
function archiveWith(string $prefix, array $files): string
{
    // A path of its own per archive: `PharData` caches by filename for the life
    // of the process, so a second archive written to a path already read would
    // hand back the first one.
    $path = sys_get_temp_dir().'/shape-icons-nested-'.getmypid().'-'.uniqid();

    $archive = new PharData($path.'.tar');

    foreach ($files as $entry => $contents) {
        $archive->addFromString($prefix.$entry, $contents);
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
 * And named as the library's own, so that what it generates is written flat: a
 * set that is not the one `icon_set` names goes into a subdirectory of its own,
 * which is a fact about where these tests look rather than about the source they
 * are exercising.
 *
 * @param  array<string, array<string, string>>  $styles
 */
function nestedSet(bool $flatten = true, ?array $styles = null): string
{
    $name = 'remix-'.uniqid();

    config()->set('shape.icon_set', $name);
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
    $this->artisan('shape:icon:all', ['--set' => $set])->assertSuccessful();

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

    $this->artisan('shape:icon:all', ['--set' => $set])->assertSuccessful();

    expect($this->cache.'/'.$set.'/master/icons/System/close-line.svg')->toBeFile()
        ->and($this->destination.'/icon/close.blade.php')->toBeFile();
});

it('generates from a set it fetched rather than one somebody had to clone', function () {
    // The whole point of the change. Heroicons is not a Composer dependency of
    // this package, so before this, "Regenerate; don't hand-edit" asked for a
    // checkout nobody had been told to make.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon:all')->assertSuccessful();

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

    $this->artisan('shape:icon:all')->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertSent(fn ($request): bool => $request->url() === 'https://codeload.github.com/tailwindlabs/heroicons/tar.gz/master');
});

it('pins the resolved commit into every file it writes', function () {
    // A ref is usually a branch, so a header that recorded `master` would say
    // nothing about which drawing is in the file underneath it. The SHA comes
    // out of the archive's own pax header, which costs no second request.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon:all')->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))
        ->toContain('Heroicons (https://heroicons.com), MIT licensed.')
        ->toContain('tailwindlabs/heroicons@'.substr($this->commit, 0, 12))
        ->toContain("Regenerate; don't hand-edit.");

    $lock = json_decode((string) file_get_contents($this->destination.'/icon/shape-icons.json'), true);

    expect($lock['hero'])
        ->toMatchArray([
            'repo' => 'tailwindlabs/heroicons',
            'ref' => 'master',
            'commit' => $this->commit,
        ])
        ->and($lock['hero']['icons'])->toHaveKey('check');
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

    $this->artisan('shape:icon:all')->assertSuccessful();

    $root = $this->cache.'/hero/master';

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

    $this->artisan('shape:icon:all')->assertSuccessful();

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
    expect($this->cache.'/hero/master/optimized/24/solid/check.svg')->toBeFile()
        ->and(is_link($this->cache.'/hero/master/optimized/link.svg'))->toBeFalse();
});

/**
 * A set whose repository is too large to unpack, which is the other thing a
 * pattern cannot rescue: `PharData` reads an archive into memory whole, so past
 * a certain size the run is killed rather than slowed.
 */
function unpackableSet(): string
{
    $name = 'huge-'.uniqid();

    config()->set('shape.icon_sets.'.$name, [
        'repo' => 'google/material-design-icons',
        'ref' => 'master',
        'path' => 'symbols',
        'archive' => false,
        'notice' => 'Material Symbols (https://fonts.google.com/icons), Apache 2.0 licensed.',
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'slots' => array_fill_keys(array_keys((array) config('shape.icon_slots')), 'check'),
    ]);

    return $name;
}

it('refuses shape:icon:all for a set that cannot be pulled whole, before fetching anything', function () {
    // What this used to do was pull a repository measured in gigabytes and be
    // killed unpacking it, with a stack trace inside `PharData::__construct`
    // where the reason should be. `TestCase` forbids stray requests, so a run
    // that reached for the archive would fail here rather than pass.
    Http::fake();

    $this->artisan('shape:icon:all', ['--set' => unpackableSet()])
        ->expectsOutputToContain('one drawing at a time')
        ->assertFailed();

    Http::assertNothingSent();
});

it('replaces from a set that cannot be pulled whole, a drawing at a time', function () {
    // The half that still works, and the reason the flag is not simply "this
    // set is unsupported": fourteen slots is twenty-eight raw fetches, against
    // an archive PHP cannot open at all.
    Http::fake([
        'raw.githubusercontent.com/*' => Http::response('<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>'),
        'api.github.com/*' => Http::response($this->commit),
    ]);

    $this->artisan('shape:icon:replace', ['--set' => unpackableSet()])->assertSuccessful();

    expect($this->destination.'/icon/shape-close.blade.php')->toBeFile();

    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'codeload'));
});

it('keeps the fetched sets out of the consumer\'s history', function () {
    // Thousands of files nobody wrote, under a storage path Laravel's own
    // ignore rules do not reach. The pattern covers the file itself, so there
    // is nothing to commit rather than one stray `.gitignore` to explain.
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon:all')->assertSuccessful();

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
        ->expectsOutputToContain('[hero] at [master]')
        ->assertFailed();

    Http::assertNothingSent();
});

it('works from a warm cache without asking for anything', function () {
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon:all')->assertSuccessful();

    Http::fake();

    $this->artisan('shape:icon:all', ['--offline' => true, '--force' => true])
        ->assertSuccessful();

    Http::assertNothingSent();

    expect($this->destination.'/icon/check.blade.php')->toBeFile();
});

it('reads the ref it was asked for rather than the one the set declares', function () {
    Http::fake(['codeload.github.com/*' => Http::response($this->archive)]);

    $this->artisan('shape:icon:all', ['--ref' => 'v2.1.5'])->assertSuccessful();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://codeload.github.com/tailwindlabs/heroicons/tar.gz/v2.1.5');

    expect($this->cache.'/hero/v2.1.5/optimized/24/solid/check.svg')->toBeFile();
});

it('says so when GitHub will not answer', function () {
    Http::fake(['codeload.github.com/*' => Http::response('', 404)]);

    $this->artisan('shape:icon:all')
        ->expectsOutputToContain('404')
        ->assertFailed();
});

/**
 * A set read from the npm registry, and the two responses that serves it: the
 * package document, then the tarball it points at.
 *
 * Named as the library's own for the reason `nestedSet()` is: a set that is not
 * the one `icon_set` names is written into a subdirectory, and where the file
 * lands is not what these tests are about.
 *
 * @param  array<string, string>  $files
 * @return array{0: string, 1: array<string, mixed>}
 */
function published(array $files, string $version = '0.47.0'): array
{
    $name = 'published-'.uniqid();
    $tarball = packageArchive($files);

    config()->set('shape.icon_set', $name);
    config()->set('shape.icon_sets.'.$name, [
        'npm' => '@material-symbols/svg-400',
        'version' => 'latest',
        'path' => 'outlined',
        'notice' => 'Material Symbols (https://fonts.google.com/icons), Apache 2.0 licensed.',
        'styles' => [
            'outline' => ['base' => '{name}.svg'],
            'solid' => ['base' => '{name}-fill.svg'],
        ],
    ]);

    return [$name, [
        'dist-tags' => ['latest' => $version],
        'versions' => [$version => ['dist' => [
            'tarball' => 'https://registry.npmjs.org/@material-symbols/svg-400/-/svg-400-'.$version.'.tgz',
            'integrity' => 'sha512-'.base64_encode(hash('sha512', $tarball, true)),
        ]]],
    ], $tarball];
}

it('reads a set from the package it is published as', function () {
    // The reason this source exists. Google's repository files each symbol as a
    // directory of 168 variants and is gigabytes; the package is the same
    // drawings and 1.8MB, because a published artefact holds the drawings and
    // not the project that produces them.
    [$set, $document, $tarball] = published([
        'outlined/check_circle.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" data-drawn="check_circle" /></svg>',
        'outlined/check_circle-fill.svg' => '<svg viewBox="0 0 24 24"><path d="M1 1" data-drawn="check_circle-fill" /></svg>',
        'LICENSE' => 'Apache License 2.0',
    ]);

    Http::fake([
        'registry.npmjs.org/@material-symbols%2fsvg-400' => Http::response($document),
        'registry.npmjs.org/*.tgz' => Http::response($tarball),
    ]);

    $this->artisan('shape:icon', ['icons' => ['check_circle'], '--set' => $set])->assertSuccessful();

    // The dist-tag is resolved to the release behind it, and it is the release
    // that is pinned — `latest` says nothing a year from now.
    expect(file_get_contents($this->destination.'/icon/check_circle.blade.php'))
        ->toContain('data-drawn="check_circle"')
        ->toContain('data-drawn="check_circle-fill"')
        ->toContain('@material-symbols/svg-400@0.47.0')
        ->not->toContain('@latest');

    $lock = json_decode((string) file_get_contents($this->destination.'/icon/shape-icons.json'), true);

    expect($lock[$set])->toMatchArray([
        'npm' => '@material-symbols/svg-400',
        'version' => '0.47.0',
    ]);

    // The licence travels with the drawings here as it does from a repository.
    expect($this->cache.'/'.$set.'/latest/LICENSE')->toBeFile();
});

it('checks the archive against the hash the registry states for it', function () {
    // The one thing a registry offers that a repository archive does not. What
    // is downloaded is written into somebody's application, so bytes that do not
    // match what was published are not unpacked.
    [$set, $document] = published([
        'outlined/check_circle.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
    ]);

    Http::fake([
        'registry.npmjs.org/@material-symbols%2fsvg-400' => Http::response($document),
        'registry.npmjs.org/*.tgz' => Http::response(packageArchive([
            'outlined/check_circle.svg' => '<svg viewBox="0 0 24 24"><path d="M9 9" data-drawn="not what was published" /></svg>',
        ])),
    ]);

    $this->artisan('shape:icon', ['icons' => ['check_circle'], '--set' => $set])
        ->expectsOutputToContain('integrity hash')
        ->assertFailed();

    expect(file_exists($this->destination.'/icon/check_circle.blade.php'))->toBeFalse();
});

it('says so when the registry has no such version of a package', function () {
    [$set, $document, $tarball] = published([
        'outlined/check_circle.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
    ]);

    Http::fake([
        'registry.npmjs.org/@material-symbols%2fsvg-400' => Http::response($document),
        'registry.npmjs.org/*.tgz' => Http::response($tarball),
    ]);

    $this->artisan('shape:icon', ['icons' => ['check_circle'], '--set' => $set, '--ref' => '9.9.9'])
        ->expectsOutputToContain('no [9.9.9]')
        ->assertFailed();
});

it('keeps the licence and not a drawing that is merely called one', function () {
    // Tabler draws `license.svg` — a document with a stamp on it — and the rule
    // that keeps a licence file whatever case it is written in matched that too,
    // pulling two drawings out of a directory the set does not read. A licence
    // is a file at the root of the archive; a drawing called `license` is not.
    [$set, $document, $tarball] = published([
        'outlined/check_circle.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
        'LICENSE' => 'Apache License 2.0',
        'categories/Document/license.svg' => '<svg viewBox="0 0 24 24"><path d="M9 9" /></svg>',
        'categories/Document/license-off.svg' => '<svg viewBox="0 0 24 24"><path d="M8 8" /></svg>',
    ]);

    Http::fake([
        'registry.npmjs.org/@material-symbols%2fsvg-400' => Http::response($document),
        'registry.npmjs.org/*.tgz' => Http::response($tarball),
    ]);

    $this->artisan('shape:icon', ['icons' => ['check_circle'], '--set' => $set])->assertSuccessful();

    $root = $this->cache.'/'.$set.'/latest';

    expect($root.'/LICENSE')->toBeFile()
        ->and(file_exists($root.'/categories/Document/license.svg'))->toBeFalse()
        ->and(file_exists($root.'/categories/Document/license-off.svg'))->toBeFalse();
});
