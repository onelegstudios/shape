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

it('needs a directory to read from', function () {
    $this->artisan('shape:icon', ['icons' => ['check']])
        ->expectsOutputToContain('Pass --from')
        ->assertFailed();
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
