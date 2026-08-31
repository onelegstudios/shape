<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Onelegstudios\Shape\IconSet;
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

    // A flat set spelling every name Shape draws the way Lucide spells it, so
    // that a replacement can actually be checked end to end.
    $this->lucide = __DIR__.'/../fixtures/icons-lucide';
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

it('generates an icon that renders across the whole matrix', function () {
    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])->assertSuccessful();

    expect(Blade::render('<x-shape::icon.check />'))
        ->toContain('data-shape-icon')
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('stroke="currentColor"');

    // The size reaches for the style, so this is the 16px solid drawing and not
    // the 24px outline one shrunk to fit.
    expect(Blade::render('<x-shape::icon.check size="xs" />'))
        ->toContain('viewBox="0 0 16 16"')
        ->toContain('fill="currentColor"')
        ->toContain('size-4');

    // ...and the cell Heroicons does not draw is that outline drawing, scaled.
    expect(Blade::render('<x-shape::icon.check variant="outline" size="xs" />'))
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
    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])->assertSuccessful();
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
        ->toBe($arms('check'));
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

it('needs somewhere to read a set that says nothing about where it is drawn', function () {
    // A set with no `repo` has no upstream to fetch, which is the normal case
    // for a folder of drawings somebody made themselves. `--from` is the only
    // way to read one, and saying so beats fetching nothing from nowhere.
    config()->set('shape.icon_sets.homemade', [
        'notice' => '',
        'styles' => ['outline' => ['base' => '{name}.svg']],
    ]);

    $this->artisan('shape:icon', ['icons' => ['check'], '--set' => 'homemade'])
        ->expectsOutputToContain('Pass --from')
        ->assertFailed();
});

it('records what each icon was drawn from, beside the icons', function () {
    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])->assertSuccessful();

    $lock = json_decode((string) file_get_contents($this->destination.'/icon/shape-icons.json'), true);

    expect($lock)->toHaveKey('heroicons')
        ->and($lock['heroicons']['icons'])->toHaveKey('check');

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

    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $from])->assertSuccessful();

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
    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])->assertSuccessful();

    $this->artisan('shape:icon', ['--status' => true])
        ->expectsOutputToContain('pass --from to check')
        ->assertSuccessful();
});

it('has nothing to report before anything has been generated', function () {
    $this->artisan('shape:icon', ['--status' => true])
        ->expectsOutputToContain('No icons have been generated')
        ->assertSuccessful();
});

it('reads a name through the alias and writes it under Shape\'s', function () {
    // The asymmetry that makes a replacement possible at all. Lucide has no
    // `x-mark.svg`; it has `x.svg`. The drawing comes from there, and the file
    // is still called what the close button asks for.
    $this->artisan('shape:icon', ['icons' => ['x-mark'], '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    expect($this->destination.'/icon/x-mark.blade.php')->toBeFile()
        ->and($this->destination.'/icon/x.blade.php')->not->toBeFile();

    // The fixture marks each drawing with the file it came from.
    expect(file_get_contents($this->destination.'/icon/x-mark.blade.php'))
        ->toContain('data-drawn="x"');
});

it('leaves a name the set spells the same way alone', function () {
    $this->artisan('shape:icon', ['icons' => ['check'], '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))
        ->toContain('data-drawn="check"');
});

it('generates exactly the icons the library draws itself', function () {
    $this->artisan('shape:icon', ['--replace' => true, '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    $written = array_map(
        fn (string $path): string => basename($path, '.blade.php'),
        glob($this->destination.'/icon/*.blade.php') ?: [],
    );

    sort($written);

    expect($written)->toBe((new Registry)->icons());
});

it('replaces every one of them from a set that spells them differently', function () {
    // The point of the whole feature: after this, nothing in the library is
    // still drawing a Heroicon. Each file has to have come from Lucide's own
    // spelling of the name it is written under.
    $this->artisan('shape:icon', ['--replace' => true, '--set' => 'lucide', '--from' => $this->lucide])
        ->assertSuccessful();

    $set = IconSet::fromArray('lucide', config('shape.icon_sets')['lucide'], config('shape.icon_sizes'));

    foreach ((new Registry)->icons() as $name) {
        expect(file_get_contents($this->destination.'/icon/'.$name.'.blade.php'))
            ->toContain('data-drawn="'.$set->sourceName($name).'"')
            ->toContain('Lucide');
    }
});

it('draws the twelve names the components actually ask for', function () {
    // Derived from the markup rather than typed out, so this guards the
    // derivation rather than restating it: a component gaining an icon fails
    // here, which is the moment to decide whether a replacement set has to
    // cover it.
    expect((new Registry)->icons())->toBe([
        'arrow-trending-down',
        'arrow-trending-up',
        'check',
        'check-circle',
        'chevron-down',
        'chevron-left',
        'chevron-right',
        'exclamation-triangle',
        'information-circle',
        'minus',
        'x-circle',
        'x-mark',
    ]);
});

it('will not be told which icons to replace', function () {
    // `--replace` already knows. Taking names as well would let one be quietly
    // dropped from the list that has to be complete to mean anything.
    $this->artisan('shape:icon', ['icons' => ['check'], '--replace' => true, '--from' => $this->lucide])
        ->expectsOutputToContain('already knows')
        ->assertFailed();

    $this->artisan('shape:icon', ['--replace' => true, '--all' => true, '--from' => $this->lucide])
        ->expectsOutputToContain('already knows')
        ->assertFailed();
});

it('reverses the files it discovers back through the alias map', function () {
    // `--all` walks files, and files carry the set's names. `x.svg` has to be
    // written as `x-mark.blade.php` or the component nothing renders it under.
    $this->artisan('shape:icon', ['--set' => 'lucide', '--from' => $this->lucide, '--all' => true])
        ->assertSuccessful();

    $written = array_map(
        fn (string $path): string => basename($path, '.blade.php'),
        glob($this->destination.'/icon/*.blade.php') ?: [],
    );

    sort($written);

    expect($written)->toBe((new Registry)->icons())
        ->and($this->destination.'/icon/circle-check.blade.php')->not->toBeFile();
});

it('generates a set\'s own name for a drawing Shape has no name for', function () {
    // The other half of `--all`: a file nothing aliases to is written under its
    // own name, which is how a supplementary set adds icons rather than
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
    $this->artisan('shape:icon', ['icons' => ['check'], '--from' => $this->heroicons])->assertSuccessful();

    $this->artisan('shape:icon', [
        'icons' => ['check'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => 'lucide',
    ])->doesntExpectOutputToContain('exists, kept')->assertSuccessful();

    expect(file_get_contents($this->destination.'/icon/check.blade.php'))
        ->not->toContain('data-drawn="check"')
        ->and(file_get_contents($this->destination.'/icon/lucide/check.blade.php'))
        ->toContain('data-drawn="check"');

    // And both are reachable, under names that don't compete.
    expect(Blade::render('<x-shape::icon.check />'))->toContain('viewBox="0 0 24 24"');
    expect(Blade::render('<x-shape::icon.lucide.check />'))->toContain('data-drawn="check"');
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
    // `shape::icon.x-mark`, and a file under `icon/lucide/` answers to
    // `shape::icon.lucide.x-mark`. The run would write twelve files and change
    // nothing at all.
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
    // is remembered for exactly one run: the next `shape:icon check
    // --set=lucide` without it wrote a second copy flat, into the namespace the
    // first was moved out of to avoid a collision, and pinned it in a second
    // lockfile — silently, because the "exists, kept" check only ever looks in
    // the directory the run resolved to.
    config()->set('shape.icon_sets.lucide.namespace', 'lucide');

    $this->artisan('shape:icon', [
        'icons' => ['check'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
    ])->assertSuccessful();

    expect($this->destination.'/icon/lucide/check.blade.php')->toBeFile()
        ->and($this->destination.'/icon/check.blade.php')->not->toBeFile();

    // And the run after it lands in the same place rather than beside it.
    $this->artisan('shape:icon', [
        'icons' => ['check'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
    ])->expectsOutputToContain('exists, kept')->assertSuccessful();

    expect($this->destination.'/icon/check.blade.php')->not->toBeFile()
        ->and($this->destination.'/icon/shape-icons.json')->not->toBeFile();
});

it('lets one run override the subdirectory its set declares', function () {
    config()->set('shape.icon_sets.lucide.namespace', 'lucide');

    $this->artisan('shape:icon', [
        'icons' => ['check'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => 'second',
    ])->assertSuccessful();

    expect($this->destination.'/icon/second/check.blade.php')->toBeFile()
        ->and($this->destination.'/icon/lucide/check.blade.php')->not->toBeFile();
});

it('writes flat for a run that says flat out loud', function () {
    // `--namespace=` with nothing after it. The one way to say "this run is
    // flat" about a set that normally is not, which is what `--replace` needs.
    config()->set('shape.icon_sets.lucide.namespace', 'lucide');

    $this->artisan('shape:icon', [
        'icons' => ['check'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => '',
    ])->assertSuccessful();

    expect($this->destination.'/icon/check.blade.php')->toBeFile()
        ->and($this->destination.'/icon/lucide')->not->toBeDirectory();

    $this->artisan('shape:icon', [
        '--replace' => true,
        '--set' => 'lucide',
        '--from' => $this->lucide,
        '--namespace' => '',
        '--force' => true,
    ])->assertSuccessful();

    expect($this->destination.'/icon/x-mark.blade.php')->toBeFile();
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
        'icons' => ['check'],
        '--set' => 'lucide',
        '--from' => $this->lucide,
    ])->assertSuccessful();

    $this->artisan('shape:icon', ['--status' => true, '--from' => $this->lucide])
        ->expectsOutputToContain('icon/lucide')
        ->expectsOutputToContain('check')
        ->doesntExpectOutputToContain('No icons have been generated')
        ->assertSuccessful();
});
