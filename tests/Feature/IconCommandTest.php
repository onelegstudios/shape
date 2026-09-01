<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Onelegstudios\Shape\IconSet;
use Onelegstudios\Shape\IconSlots;
use Onelegstudios\Shape\Registry;
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

    // A flat set filling every slot the way Lucide fills it, so that a
    // replacement can actually be checked end to end.
    $this->lucide = __DIR__.'/../fixtures/icons-lucide';

    // A set that puts the style in the filename as well as the directory, the
    // way Phosphor does.
    $this->phosphor = __DIR__.'/../fixtures/icons-phosphor';

    // A flat set whose style marker is a suffix on most names and an infix on
    // the ones that hang a badge off a glyph, the way Bootstrap does.
    $this->bootstrap = __DIR__.'/../fixtures/icons-bootstrap';
});

/**
 * A Heroicons-shaped directory holding every drawing the shipped set names.
 *
 * Committing four files per slot to assert a count would be a lot of bytes for
 * one fact. The drawings are stand-ins; what is being checked is which files the
 * run decides to write, not what is in them.
 */
function heroiconsFixture(): string
{
    $directory = sys_get_temp_dir().'/shape-icons-heroicons-'.getmypid();

    exec('rm -rf '.escapeshellarg($directory));

    $set = IconSet::fromArray('heroicons', config('shape.icon_sets')['heroicons'], config('shape.icon_sizes'));

    foreach (IconSlots::fromConfig()->names() as $slot) {
        $drawn = $set->sourceName($slot);

        if ($drawn === null) {
            continue;
        }

        foreach (['16/solid', '20/solid', '24/solid', '24/outline'] as $cell) {
            @mkdir($directory.'/'.$cell, 0777, true);

            file_put_contents(
                $directory.'/'.$cell.'/'.$drawn.'.svg',
                '<svg viewBox="0 0 24 24"><path d="M0 0" data-drawn="'.$drawn.'" /></svg>',
            );
        }
    }

    return $directory;
}

/**
 * The icons this run left in the destination, by name, sorted.
 *
 * @return list<string>
 */
function written(): array
{
    $names = array_map(
        fn (string $path): string => basename($path, '.blade.php'),
        glob((string) TestCase::$componentsPath.'/icon/*.blade.php') ?: [],
    );

    sort($names);

    return $names;
}

it('reproduces a shipped icon from the SVGs it was drawn from', function () {
    // The header on every icon says "Regenerate; don't hand-edit". This is the
    // assertion that makes that sentence true rather than aspirational: the
    // fixture holds Heroicons' four source files for `check`, which is what
    // fills `shape-checked`, and what comes out has to be the file this package
    // ships, byte for byte. Only the filename moved in the rename to slots; the
    // drawing behind it did not.
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-checked.blade.php'))
        ->toBe(file_get_contents(__DIR__.'/../../resources/views/shape/icon/shape-checked.blade.php'));
});

it('generates an icon that renders across the whole matrix', function () {
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])->assertSuccessful();

    expect(Blade::render('<x-shape::icon.shape-checked />'))
        ->toContain('data-shape-icon')
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('stroke="currentColor"');

    // The size reaches for the style, so this is the 16px solid drawing and not
    // the 24px outline one shrunk to fit.
    expect(Blade::render('<x-shape::icon.shape-checked size="xs" />'))
        ->toContain('viewBox="0 0 16 16"')
        ->toContain('fill="currentColor"')
        ->toContain('size-4');

    // ...and the cell Heroicons does not draw is that outline drawing, scaled.
    expect(Blade::render('<x-shape::icon.shape-checked variant="outline" size="xs" />'))
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('size-4');
});

it('drops the attributes that describe how a drawing is used', function () {
    $this->artisan('shape:icon', ['icons' => ['spinner'], '--set' => 'lucide', '--from' => $this->flat])->assertSuccessful();

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
    $this->artisan('shape:icon', ['icons' => ['spinner'], '--set' => 'lucide', '--from' => $this->flat])->assertSuccessful();

    $source = (string) file_get_contents($this->destination.'/icon/spinner.blade.php');

    // One style at one size is one drawing, and a `switch` with a single arm
    // asks the reader to work out that it never branches. The `variant` prop
    // stays regardless, so a style named by a shared call site is ignored
    // rather than rendered onto the `<svg>`.
    expect($source)->not->toContain('switch')
        ->and($source)->toContain("'variant' => 'outline',")
        ->and($source)->toContain("'size' => 'base',")
        ->and($source)->toContain('Lucide');

    // Both elements survive, and the source's own indentation does not.
    expect(Blade::render('<x-shape::icon.spinner />'))
        ->toContain('<circle')
        ->toContain('<path');
});

it('sizes a one-style set by the same scale as every other', function () {
    // The reason the scale is the library's rather than each set's. Lucide draws
    // at one size and Heroicons at three, and both write the same size `match` —
    // so `size="sm"` is 20px whichever set the icon at a call site came from.
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])->assertSuccessful();
    $this->artisan('shape:icon', ['icons' => ['spinner'], '--set' => 'lucide', '--from' => $this->flat])->assertSuccessful();

    $arms = function (string $icon): string {
        preg_match(
            '/->add\(match \(\$size\) \{(.*?)\}\)/s',
            (string) file_get_contents($this->destination.'/icon/'.$icon.'.blade.php'),
            $matches,
        );

        return $matches[1] ?? '';
    };

    expect($arms('spinner'))
        ->toContain("'xs' => '[:where(&)]:size-4',")
        ->toContain("'sm' => '[:where(&)]:size-5',")
        ->toContain("default => '[:where(&)]:size-6',")
        ->toBe($arms('shape-checked'));
});

it('finds every icon in the source directory', function () {
    // Under its own name: `--all` walks the set's files, and a file is what the
    // set calls it. Filling a slot is the other operation.
    $this->artisan('shape:icon', ['--from' => $this->heroicons, '--all' => true])
        ->expectsOutputToContain('check')
        ->assertSuccessful();

    expect($this->destination.'/icon/check.blade.php')->toBeFile()
        ->and($this->destination.'/icon/shape-checked.blade.php')->not->toBeFile();
});

it('says so when a name has no drawing', function () {
    $this->artisan('shape:icon', ['icons' => ['bicycle'], '--from' => $this->heroicons])
        ->expectsOutputToContain('no SVG found')
        ->assertSuccessful();

    expect(file_exists($this->destination.'/icon/bicycle.blade.php'))->toBeFalse();
});

it('keeps an icon that is already there unless forced', function () {
    file_put_contents($this->destination.'/icon/shape-checked.blade.php', 'mine');

    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])
        ->expectsOutputToContain('exists, kept')
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-checked.blade.php'))->toBe('mine');

    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons, '--force' => true])
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-checked.blade.php'))->toContain('@blaze');
});

it('needs somewhere to read a set that says nothing about where it is drawn', function () {
    // A set with no `repo` has no upstream to fetch, which is the normal case
    // for a folder of drawings somebody made themselves. `--from` is the only
    // way to read one, and saying so beats fetching nothing from nowhere.
    config()->set('shape.icon_sets.homemade', [
        'notice' => '',
        'styles' => ['outline' => ['base' => '{name}.svg']],
    ]);

    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--set' => 'homemade'])
        ->expectsOutputToContain('Pass --from')
        ->assertFailed();
});

it('reads the set the config names when a run does not say', function () {
    // The reason the setting exists. An application that has moved the library
    // onto Lucide types `shape:icon shape-close` and gets Lucide's drawing —
    // rather than typing `--set=lucide` on every run forever, with the run that
    // forgets writing a Heroicon under a slot name that says nothing about who
    // drew it.
    config()->set('shape.icon_set', 'lucide');

    $this->artisan('shape:icon', ['icons' => ['shape-close'], '--from' => $this->lucide])
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-close.blade.php'))
        ->toContain('data-drawn="x"')
        ->toContain('Lucide');
});

it('lets --set read another set for the one run that asks', function () {
    // Which is what a supplementary set is: read once, beside a library wearing
    // something else. The flag still wins, and it wins for that run only.
    config()->set('shape.icon_set', 'lucide');

    $from = heroiconsFixture();

    $this->artisan('shape:icon', ['icons' => ['shape-close'], '--set' => 'heroicons', '--from' => $from])
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-close.blade.php'))
        ->toContain('data-drawn="x-mark"');

    exec('rm -rf '.escapeshellarg($from));
});

it('says which key to write when no set is configured to read', function () {
    // A published config from before this key existed has no `icon_set`, and
    // falling back to Heroicons for it would generate the one set the
    // application had most likely moved off.
    config()->set('shape.icon_set', null);

    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])
        ->expectsOutputToContain('[icon_set]')
        ->assertFailed();
});

it('reports a configured set that is not one of the sets', function () {
    // Named for something no set will ever be called, rather than for a set the
    // package has not shipped yet — the second is a test that starts passing for
    // the wrong reason on the day it does ship.
    config()->set('shape.icon_set', 'imaginary');

    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])
        ->expectsOutputToContain('No icon set named [imaginary]')
        ->assertFailed();
});

it('records what each icon was drawn from, beside the icons', function () {
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])->assertSuccessful();

    $lock = json_decode((string) file_get_contents($this->destination.'/icon/shape-icons.json'), true);

    expect($lock)->toHaveKey('heroicons')
        ->and($lock['heroicons']['icons'])->toHaveKey('shape-checked');

    // A `--from` directory was not fetched from the set's repository, so the
    // record does not claim it was. Pinning a component to a commit nobody read
    // it at would be worse than not pinning it.
    expect($lock['heroicons'])->not->toHaveKey('repo')
        ->and($lock['heroicons'])->not->toHaveKey('commit');
});

it('reports an icon whose drawing has moved since it was generated', function () {
    // Without the record, a component that differs from the current drawing
    // might have been hand-edited or might have been overtaken upstream, and
    // only the second is a reason to regenerate.
    $from = sys_get_temp_dir().'/shape-icons-upstream-'.getmypid();

    exec('rm -rf '.escapeshellarg($from));
    exec('cp -R '.escapeshellarg($this->heroicons).' '.escapeshellarg($from));

    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $from])->assertSuccessful();

    $this->artisan('shape:icon', ['--status' => true, '--from' => $from])
        ->expectsOutputToContain('unchanged')
        ->assertSuccessful();

    file_put_contents(
        $from.'/24/solid/check.svg',
        '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M1 1 L2 2" /></svg>',
    );

    $this->artisan('shape:icon', ['--status' => true, '--from' => $from])
        ->expectsOutputToContain('redrawn upstream')
        ->assertSuccessful();

    exec('rm -rf '.escapeshellarg($from));
});

it('will not go to the network to check icons that came from a directory', function () {
    // The lockfile records no upstream for a `--from` run, and reaching for the
    // set's repository instead would compare the icons against drawings they
    // never came from. `TestCase` forbids stray requests, so a run that tried
    // would fail here rather than quietly succeeding.
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])->assertSuccessful();

    $this->artisan('shape:icon', ['--status' => true])
        ->expectsOutputToContain('pass --from to check')
        ->assertSuccessful();
});

it('has nothing to report before anything has been generated', function () {
    $this->artisan('shape:icon', ['--status' => true])
        ->expectsOutputToContain('No icons have been generated')
        ->assertSuccessful();
});

it('reads a slot through the set and writes it under the slot\'s name', function () {
    // The asymmetry that makes a replacement possible at all. Lucide has no
    // `x-mark.svg`; it has `x.svg`. The drawing comes from there, and the file
    // is called what the close button asks for — a role, so neither vendor's
    // vocabulary is impersonated by the filename.
    $this->artisan('shape:icon', ['icons' => ['shape-close'], '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    expect($this->destination.'/icon/shape-close.blade.php')->toBeFile()
        ->and($this->destination.'/icon/x.blade.php')->not->toBeFile()
        ->and($this->destination.'/icon/x-mark.blade.php')->not->toBeFile();

    // The fixture marks each drawing with the file it came from.
    expect(file_get_contents($this->destination.'/icon/shape-close.blade.php'))
        ->toContain('data-drawn="x"');
});

it('leaves the vendor\'s own vocabulary free for the user', function () {
    // The failure the rename exists to remove. A user who knows Lucide looks for
    // `triangle-alert`, and after this run there is one file called that and one
    // called `shape-warning`, each saying what it is.
    $this->artisan('shape:icon', [
        'icons' => ['shape-warning', 'triangle-alert'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
    ])->assertSuccessful();

    expect($this->destination.'/icon/shape-warning.blade.php')->toBeFile()
        ->and($this->destination.'/icon/triangle-alert.blade.php')->toBeFile();
});

it('fills a slot the set spells the same way from that spelling', function () {
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-checked.blade.php'))
        ->toContain('data-drawn="check"');
});

it('generates exactly the slots the library declares', function () {
    $this->artisan('shape:icon', ['--replace' => true, '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    $slots = IconSlots::fromConfig()->names();

    sort($slots);

    expect(written())->toBe($slots)->toHaveCount(14);
});

it('leaves the three examples alone, because they are not the library\'s', function () {
    // `shape-arrow-right`, `shape-plus` and `shape-trash` ship so the README and
    // the previews render. Nothing resolves them and the documentation does not
    // offer them as a catalogue, so `--replace` skipping them is the design: an
    // application that wants a trash can generates its own.
    $this->artisan('shape:icon', ['--replace' => true, '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    foreach (['shape-arrow-right', 'shape-plus', 'shape-trash'] as $example) {
        expect($this->destination.'/icon/'.$example.'.blade.php')->not->toBeFile();
    }
});

it('writes thirteen slots from heroicons and leaves the packaged one alone', function () {
    // Heroicons has nothing that reads as a loader, says so with `null`, and the
    // spinner this package draws goes on resolving. That is the state a
    // Heroicons user is in permanently, and it is not a gap.
    $from = heroiconsFixture();

    $this->artisan('shape:icon', ['--replace' => true, '--from' => $from])
        ->expectsOutputToContain('packaged by Shape')
        ->assertSuccessful();

    expect(written())->toHaveCount(13)
        ->and($this->destination.'/icon/shape-loading.blade.php')->not->toBeFile();

    exec('rm -rf '.escapeshellarg($from));
});

it('spins the loading slot, and nothing else', function () {
    // The spin belongs to the slot rather than to the set that drew it — every
    // set's loader spins — so it is declared once in `icon_slots` and baked into
    // whichever drawing fills it.
    $this->artisan('shape:icon', ['--replace' => true, '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-loading.blade.php'))
        ->toContain("Shape::classes('shrink-0 animate-spin')")
        ->toContain('data-drawn="loader-circle"');

    foreach (array_diff(written(), ['shape-loading']) as $slot) {
        expect(file_get_contents($this->destination.'/icon/'.$slot.'.blade.php'))
            ->toContain("Shape::classes('shrink-0')");
    }
});

it('answers every slot from every set the package ships', function () {
    // A set in the shipped config is a promise that `--replace --set=…`
    // completes, and the way that promise breaks quietly is an oversight rather
    // than a gap: `null` is an answer and a missing key is not, and only the
    // second fails the run — on the machine of whoever typed it first.
    $missing = [];

    /** @var array<string, mixed> $sets */
    $sets = config('shape.icon_sets');

    foreach ($sets as $name => $definition) {
        $set = IconSet::fromArray((string) $name, $definition, config('shape.icon_sizes'));

        foreach (IconSlots::fromConfig()->names() as $slot) {
            if (! $set->declares($slot)) {
                $missing[] = "{$name}.{$slot}";
            }
        }
    }

    expect($missing)->toBe([]);
});

it('ships every set with somewhere to fetch it and a licence to carry', function () {
    // The two things a set has to have to be worth shipping: `shape:icon` can
    // reach it without a checkout the reader was never told to make, and every
    // component it writes states whose drawing it is. A set with neither belongs
    // in the docs as an example rather than in the config as an entry.
    /** @var array<string, mixed> $sets */
    $sets = config('shape.icon_sets');

    foreach ($sets as $name => $definition) {
        $set = IconSet::fromArray((string) $name, $definition, config('shape.icon_sizes'));

        expect($set->repo)->not->toBeNull()
            ->and($set->notice)->not->toBe('');
    }
});

it('names the set and the config key for a slot the set says nothing about', function () {
    // The error is where a user finds out that repointing a slot is a thing they
    // can do, so it has to say where — a bare "no SVG found" per name sent them
    // looking for a missing file instead.
    config()->set('shape.icon_sets.lucide.slots', ['shape-checked' => 'check']);

    $this->artisan('shape:icon', ['--replace' => true, '--set' => 'lucide', '--from' => $this->lucide])
        ->expectsOutputToContain('says nothing about slot [shape-close]')
        ->expectsOutputToContain('shape.icon_sets.lucide.slots')
        ->assertFailed();
});

it('reports a slot the set fills from a drawing it has not got', function () {
    config()->set('shape.icon_sets.lucide.slots.shape-close', 'octagon-x');

    $this->artisan('shape:icon', ['icons' => ['shape-close'], '--set' => 'lucide', '--from' => $this->lucide])
        ->expectsOutputToContain('fills slot [shape-close] from [octagon-x]')
        ->assertFailed();
});

it('says a slot is a decision rather than a hole when the set answers null', function () {
    // `null` is an answer. It does not fail the run, and `shape:doctor` is where
    // the consequence — a component still drawing a Heroicon — is reported.
    config()->set('shape.icon_sets.lucide.slots.shape-trend-flat', null);

    $this->artisan('shape:icon', ['icons' => ['shape-trend-flat'], '--set' => 'lucide', '--from' => $this->lucide])
        ->expectsOutputToContain('no drawing in [lucide]')
        ->assertSuccessful();

    expect($this->destination.'/icon/shape-trend-flat.blade.php')->not->toBeFile();
});

it('fills every slot from a set that spells them differently', function () {
    // The point of the whole feature: after this, nothing in the library is
    // still drawing a Heroicon. Each file has to have come from Lucide's own
    // spelling of whatever fills the slot it is written under, and to say so in
    // its header — the filename says the role and the notice says the vendor.
    $this->artisan('shape:icon', ['--replace' => true, '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    $set = IconSet::fromArray('lucide', config('shape.icon_sets')['lucide'], config('shape.icon_sizes'));

    foreach (IconSlots::fromConfig()->names() as $slot) {
        expect(file_get_contents($this->destination.'/icon/'.$slot.'.blade.php'))
            ->toContain('data-drawn="'.$set->sourceName($slot).'"')
            ->toContain('Lucide');
    }
});

it('resolves thirteen of the fourteen slots in components of its own', function () {
    // Derived from the markup rather than typed out, so this guards the
    // derivation rather than restating it. `shape-loading` is the fourteenth and
    // is not here: nothing in the library renders a spinner, which is exactly
    // why the list `--replace` works from is declared rather than scanned.
    expect((new Registry)->icons())->toBe([
        'shape-checked',
        'shape-close',
        'shape-danger',
        'shape-expand',
        'shape-indeterminate',
        'shape-info',
        'shape-next',
        'shape-prev',
        'shape-success',
        'shape-trend-down',
        'shape-trend-flat',
        'shape-trend-up',
        'shape-warning',
    ]);
});

it('will not be told which icons to replace', function () {
    // `--replace` already knows. Taking names as well would let one be quietly
    // dropped from the list that has to be complete to mean anything.
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--replace' => true, '--from' => $this->lucide])
        ->expectsOutputToContain('already knows')
        ->assertFailed();

    $this->artisan('shape:icon', ['--replace' => true, '--all' => true, '--from' => $this->lucide])
        ->expectsOutputToContain('already knows')
        ->assertFailed();
});

it('writes every file it discovers flat, under its own name', function () {
    // `--all` used to run the slot map backwards, and the hairy case was a file
    // whose name an entry had already spoken for: it was written under nothing,
    // because generating it would have shadowed the entry with the wrong glyph.
    // Slots live in a namespace no set uses, so nothing is reversed and nothing
    // is dropped — every file arrives under the name the set gave it.
    $this->artisan('shape:icon', ['--set' => 'lucide', '--from' => $this->lucide, '--all' => true])
        ->assertSuccessful();

    $files = array_map(
        fn (string $path): string => basename($path, '.svg'),
        glob($this->lucide.'/*.svg') ?: [],
    );

    sort($files);

    expect(written())->toBe($files)
        ->and($this->destination.'/icon/circle-check.blade.php')->toBeFile()
        ->and($this->destination.'/icon/shape-success.blade.php')->not->toBeFile();
});

it('reads a suffixed filename as the drawing it is, not as a name of its own', function () {
    // Phosphor's `fill/check-fill.svg` is the fill drawing of `check`. Walking
    // the listing for names would write it as `check-fill`, whose outline cell
    // resolves to `regular/check-fill.svg` and is never there — so `--all` runs
    // the pattern backwards instead, and a file no pattern accounts for is
    // skipped rather than named.
    // Read through the shipped `phosphor` entry rather than a set written for
    // the test, so this covers the config the package promises as well as the
    // command that reads it.
    $this->artisan('shape:icon', ['--set' => 'phosphor', '--from' => $this->phosphor, '--all' => true])
        ->assertSuccessful();

    expect(written())->toBe(['check', 'heart']);

    // And the two drawings land in the one component, which is the point of
    // reading them as the same name: the small sizes get the fill and the
    // default size gets the regular.
    expect(file_get_contents($this->destination.'/icon/check.blade.php'))
        ->toContain('data-drawn="check-fill"')
        ->toContain('data-drawn="check"')
        ->and(file_get_contents($this->destination.'/icon/heart.blade.php'))
        ->toContain('data-drawn="heart"');
});

it('reads a marker inside a filename as the drawing it is, not as a name of its own', function () {
    // Bootstrap hangs a badge off a glyph and fills the glyph, so `person-fill-x`
    // is the fill of `person-x`. Read as a name it would be an icon called
    // `person-fill-x` whose *outline* cell holds a filled drawing — the fill
    // leaking into the outline style under a name that says so.
    //
    // Read through the shipped `bootstrap-icons` entry rather than a set written
    // for the test, so this covers the config the package promises as well as
    // the command that reads it.
    $this->artisan('shape:icon', ['--set' => 'bootstrap-icons', '--from' => $this->bootstrap, '--all' => true])
        ->assertSuccessful();

    expect(written())->toBe(['heart', 'person-check', 'person-x']);

    // The pair lands in one component, outline and fill in their own cells.
    expect(file_get_contents($this->destination.'/icon/person-x.blade.php'))
        ->toContain('data-drawn="person-x"')
        ->toContain('data-drawn="person-fill-x"');

    // And where a name is spelled both ways the suffix wins, because that is the
    // drawing that is filled through: `person-check-fill` is solid, where
    // `person-fill-check` is a filled person wearing an outline tick.
    expect(file_get_contents($this->destination.'/icon/person-check.blade.php'))
        ->toContain('data-drawn="person-check"')
        ->toContain('data-drawn="person-check-fill"')
        ->not->toContain('data-drawn="person-fill-check"');
});

it('generates a set\'s own name for a drawing outside the slots', function () {
    // The ordinary case, and how a supplementary set adds icons rather than
    // replacing them.
    $this->artisan('shape:icon', ['--set' => 'lucide', '--from' => $this->flat, '--all' => true])
        ->assertSuccessful();

    expect($this->destination.'/icon/spinner.blade.php')->toBeFile();
});

it('writes a namespaced set into a subdirectory of its own', function () {
    // Two sets written flat share one namespace, and the second to spell a name
    // the first already holds is refused. A subdirectory is how both are kept.
    $this->artisan('shape:icon', [
        'icons' => ['spinner'],
        '--set' => 'lucide',
        '--from' => $this->flat,
        '--namespace' => 'lucide',
    ])->assertSuccessful();

    expect($this->destination.'/icon/lucide/spinner.blade.php')->toBeFile()
        ->and($this->destination.'/icon/spinner.blade.php')->not->toBeFile();

    // All three ways of naming an icon reach it, and the nesting is the only
    // thing that changes about any of them.
    expect(Blade::render('<x-shape::icon.lucide.spinner />'))->toContain('data-shape-icon');
    expect(Blade::render('<x-shape::icon name="lucide.spinner" />'))->toContain('data-shape-icon');
    expect(Blade::render('<x-shape::button icon="lucide.spinner">Save</x-shape::button>'))
        ->toContain('data-shape-icon');
});

it('joins a namespace onto an explicit destination too', function () {
    $this->artisan('shape:icon', [
        'icons' => ['spinner'],
        '--set' => 'lucide',
        '--from' => $this->flat,
        '--to' => $this->destination.'/icon',
        '--namespace' => 'lucide',
    ])->assertSuccessful();

    expect($this->destination.'/icon/lucide/spinner.blade.php')->toBeFile();
});

it('does not treat a flat name and a namespaced one as a collision', function () {
    // The reason the flag exists. Without the subdirectory the second run would
    // print "exists, kept" and the second set would have nowhere to go.
    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])->assertSuccessful();

    $this->artisan('shape:icon', [
        'icons' => ['shape-checked'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => 'lucide',
    ])->doesntExpectOutputToContain('exists, kept')->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/shape-checked.blade.php'))
        ->not->toContain('data-drawn="check"')
        ->and(file_get_contents($this->destination.'/icon/lucide/shape-checked.blade.php'))
        ->toContain('data-drawn="check"');

    // And both are reachable, under names that don't compete.
    expect(Blade::render('<x-shape::icon.shape-checked />'))->toContain('viewBox="0 0 24 24"');
    expect(Blade::render('<x-shape::icon.lucide.shape-checked />'))->toContain('data-drawn="check"');
});

it('refuses a namespace that would write outside the components path', function () {
    // The one way this flag can do damage, so it is checked rather than trusted.
    foreach (['../escape', 'lucide/nested', 'Lucide', '.'] as $namespace) {
        $this->artisan('shape:icon', [
            'icons' => ['spinner'],
            '--set' => 'lucide',
            '--from' => $this->flat,
            '--namespace' => $namespace,
        ])->expectsOutputToContain('is not a namespace')->assertFailed();
    }

    expect(dirname($this->destination).'/escape')->not->toBeDirectory();
});

it('will not namespace the icons it replaces', function () {
    // A namespaced icon replaces nothing: the library asks for
    // `shape::icon.shape-close`, and a file under `icon/lucide/` answers to
    // `shape::icon.lucide.shape-close`. The run would write fourteen files and
    // change nothing at all.
    $this->artisan('shape:icon', [
        '--replace' => true,
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => 'lucide',
    ])->expectsOutputToContain('Pass --namespace= to write this run flat')->assertFailed();

    expect($this->destination.'/icon/lucide')->not->toBeDirectory();
});

it('writes a set into the subdirectory the set itself declares', function () {
    // The regression this key exists for. A namespace typed on the command line
    // is remembered for exactly one run: the next `shape:icon shape-checked
    // --set=lucide` without it wrote a second copy flat, into the namespace the
    // first was moved out of to avoid a collision, and pinned it in a second
    // lockfile — silently, because the "exists, kept" check only ever looks in
    // the directory the run resolved to.
    config()->set('shape.icon_sets.lucide.namespace', 'lucide');

    $this->artisan('shape:icon', [
        'icons' => ['shape-checked'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
    ])->assertSuccessful();

    expect($this->destination.'/icon/lucide/shape-checked.blade.php')->toBeFile()
        ->and($this->destination.'/icon/shape-checked.blade.php')->not->toBeFile();

    // And the run after it lands in the same place rather than beside it.
    $this->artisan('shape:icon', [
        'icons' => ['shape-checked'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
    ])->expectsOutputToContain('exists, kept')->assertSuccessful();

    expect($this->destination.'/icon/shape-checked.blade.php')->not->toBeFile()
        ->and($this->destination.'/icon/shape-icons.json')->not->toBeFile();
});

it('lets one run override the subdirectory its set declares', function () {
    config()->set('shape.icon_sets.lucide.namespace', 'lucide');

    $this->artisan('shape:icon', [
        'icons' => ['shape-checked'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => 'second',
    ])->assertSuccessful();

    expect($this->destination.'/icon/second/shape-checked.blade.php')->toBeFile()
        ->and($this->destination.'/icon/lucide/shape-checked.blade.php')->not->toBeFile();
});

it('writes flat for a run that says flat out loud', function () {
    // `--namespace=` with nothing after it. The one way to say "this run is
    // flat" about a set that normally is not, which is what `--replace` needs.
    config()->set('shape.icon_sets.lucide.namespace', 'lucide');

    $this->artisan('shape:icon', [
        'icons' => ['shape-checked'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => '',
    ])->assertSuccessful();

    expect($this->destination.'/icon/shape-checked.blade.php')->toBeFile()
        ->and($this->destination.'/icon/lucide')->not->toBeDirectory();

    $this->artisan('shape:icon', [
        '--replace' => true,
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => '',
        '--force' => true,
    ])->assertSuccessful();

    expect($this->destination.'/icon/shape-close.blade.php')->toBeFile();
});

it('refuses a set whose declared namespace would write outside the components path', function () {
    // Checked where the set is parsed rather than where it is used, because this
    // is the only value in a set definition that decides where a file is written.
    foreach (['../escape', 'lucide/nested', 'Lucide'] as $namespace) {
        config()->set('shape.icon_sets.lucide.namespace', $namespace);

        $this->artisan('shape:icon', [
            'icons' => ['spinner'],
            '--set' => 'lucide',
            '--from' => $this->flat,
        ])->expectsOutputToContain('which is not one')->assertFailed();
    }

    expect(dirname($this->destination).'/escape')->not->toBeDirectory();
});

it('reports on a namespaced set that the run did not name', function () {
    // `--status` is asked what is stale, not what is stale in one directory. A
    // set keeps its lockfile beside its own components, so reading only the flat
    // one answered "nothing has been generated" for a set sitting right there —
    // which reads like a clean bill of health rather than like a blind spot.
    config()->set('shape.icon_sets.lucide.namespace', 'lucide');

    $this->artisan('shape:icon', [
        'icons' => ['shape-checked'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
    ])->assertSuccessful();

    $this->artisan('shape:icon', ['--status' => true, '--from' => $this->lucide])
        ->expectsOutputToContain('icon/lucide')
        ->expectsOutputToContain('shape-checked')
        ->doesntExpectOutputToContain('No icons have been generated')
        ->assertSuccessful();
});

it('throws away the compiled views after writing an icon', function () {
    // A generated component is a file, and a compiled view is a cached answer
    // about a file. Leaving the second behind after writing the first is how a
    // component that no longer exists goes on rendering — and, under a folding
    // compiler, how a fragment of a template that was never meant to be output
    // reaches a page. Neither raises an error, so neither is noticed.
    $compiled = (string) config('view.compiled');

    file_put_contents($compiled.'/stale.php', 'stale');

    $this->artisan('shape:icon', ['icons' => ['shape-checked'], '--from' => $this->heroicons])
        ->expectsOutputToContain('Compiled views cleared')
        ->assertSuccessful();

    expect($compiled.'/stale.php')->not->toBeFile();
});

it('leaves the compiled views alone when it wrote nothing', function () {
    // Nothing was written, so nothing cached about the components can have gone
    // stale, and throwing the cache away would be a cost with no reason.
    $compiled = (string) config('view.compiled');

    file_put_contents($compiled.'/stale.php', 'stale');

    $this->artisan('shape:icon', ['icons' => ['bicycle'], '--from' => $this->heroicons])
        ->doesntExpectOutputToContain('Compiled views cleared')
        ->assertSuccessful();

    expect(file_get_contents($compiled.'/stale.php'))->toBe('stale');

    unlink($compiled.'/stale.php');
});
