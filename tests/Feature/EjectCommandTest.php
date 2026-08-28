<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Onelegstudios\Shape\Tests\TestCase;

/**
 * The ejected components live in a process-unique directory, for the reason
 * `EjectedComponentTest` gives: the suite runs in parallel, and a shared path
 * would let one worker's ejected button answer another worker's render.
 */
beforeAll(function () {
    TestCase::$componentsPath = sys_get_temp_dir().'/shape-eject-'.getmypid();
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

    mkdir($this->destination, 0777, true);
});

function ejected(string $file): string
{
    return (string) TestCase::$componentsPath.'/'.$file;
}

/**
 * @return array<string, array<string, string>>
 */
function ejectManifest(): array
{
    /** @var array<string, array<string, string>> */
    return json_decode((string) file_get_contents(ejected('shape-eject.json')), true);
}

/**
 * @param  array<string, array<string, string>>  $manifest
 */
function writeEjectManifest(array $manifest): void
{
    file_put_contents(ejected('shape-eject.json'), json_encode($manifest));
}

it('ejects a component and everything it composes', function () {
    $this->artisan('shape:eject', ['components' => ['modal']])->assertSuccessful();

    // The promise section 11 of the architecture makes: a modal arrives with the
    // close button inside it, and the icon inside that.
    expect(ejected('modal/index.blade.php'))->toBeFile()
        ->and(ejected('overlay/close.blade.php'))->toBeFile()
        ->and(ejected('button/index.blade.php'))->toBeFile()
        ->and(ejected('icon/check.blade.php'))->toBeFile();
});

it('says which component asked for each dependency', function () {
    $this->artisan('shape:eject', ['components' => ['modal']])
        ->expectsOutputToContain('requested')
        ->expectsOutputToContain('required by')
        ->assertSuccessful();
});

it('ejects only what was named when told to', function () {
    $this->artisan('shape:eject', ['components' => ['modal'], '--bare' => true])->assertSuccessful();

    expect(ejected('modal/index.blade.php'))->toBeFile()
        ->and(file_exists(ejected('button/index.blade.php')))->toBeFalse();
});

it('ejects the whole library', function () {
    $this->artisan('shape:eject', ['--all' => true])->assertSuccessful();

    expect(ejected('table/cell.blade.php'))->toBeFile()
        ->and(ejected('tooltip/index.blade.php'))->toBeFile();
});

it('resolves an ejected component ahead of the packaged one', function () {
    // The whole point of the destination, proven end to end rather than assumed:
    // the file the command wrote is the file that renders.
    $this->artisan('shape:eject', ['components' => ['separator']])->assertSuccessful();

    file_put_contents(ejected('separator.blade.php'), '<hr data-ejected-separator>');

    expect(Blade::render('<x-shape::separator />'))->toContain('data-ejected-separator');
});

it('keeps a component that has already been ejected', function () {
    $this->artisan('shape:eject', ['components' => ['separator']])->assertSuccessful();

    file_put_contents(ejected('separator.blade.php'), 'mine');

    $this->artisan('shape:eject', ['components' => ['separator']])
        ->expectsOutputToContain('exists, kept')
        ->assertSuccessful();

    expect(file_get_contents(ejected('separator.blade.php')))->toBe('mine');
});

it('overwrites an ejected component when forced', function () {
    $this->artisan('shape:eject', ['components' => ['separator']])->assertSuccessful();

    file_put_contents(ejected('separator.blade.php'), 'mine');

    $this->artisan('shape:eject', ['components' => ['separator'], '--force' => true])->assertSuccessful();

    expect(file_get_contents(ejected('separator.blade.php')))->toContain('@blaze');
});

it('records what the package held at the moment of ejection', function () {
    $this->artisan('shape:eject', ['components' => ['separator']])->assertSuccessful();

    expect(ejectManifest())->toBe([
        'separator' => [
            'separator.blade.php' => sha1_file(__DIR__.'/../../resources/views/shape/separator.blade.php'),
        ],
    ]);
});

it('refuses a component it does not have', function () {
    $this->artisan('shape:eject', ['components' => ['accordion']])
        ->expectsOutputToContain('No such component: accordion.')
        ->assertFailed();
});

it('asks for a component when given none', function () {
    $this->artisan('shape:eject')
        ->expectsOutputToContain('Name at least one component')
        ->assertFailed();
});

describe('status', function () {
    it('has nothing to say before anything is ejected', function () {
        $this->artisan('shape:eject', ['--status' => true])
            ->expectsOutputToContain('Nothing has been ejected')
            ->assertSuccessful();
    });

    it('reports an untouched component as level with the package', function () {
        $this->artisan('shape:eject', ['components' => ['separator']])->assertSuccessful();

        $this->artisan('shape:eject', ['--status' => true])
            ->expectsOutputToContain('unchanged')
            ->expectsOutputToContain('level with the package')
            ->assertSuccessful();
    });

    it('separates a component the application edited from one the package moved', function () {
        $this->artisan('shape:eject', ['components' => ['separator']])->assertSuccessful();

        file_put_contents(ejected('separator.blade.php'), '@blaze(fold: true) mine');

        // Locally edited: the file differs from what was recorded, and the
        // package still holds exactly what was recorded.
        $this->artisan('shape:eject', ['--status' => true])
            ->expectsOutputToContain('edited here')
            ->assertSuccessful();

        // The same file, now recorded as having been ejected from a version of
        // the package that no longer exists — which is what an upgrade does to
        // an untouched component, and the only case worth reading a diff for.
        writeEjectManifest(['separator' => ['separator.blade.php' => sha1_file(ejected('separator.blade.php'))]]);

        $this->artisan('shape:eject', ['--status' => true])
            ->expectsOutputToContain('the package moved')
            ->expectsOutputToContain('Compare them before re-ejecting')
            ->assertSuccessful();
    });

    it('notices an ejected file that has since been deleted', function () {
        $this->artisan('shape:eject', ['components' => ['separator']])->assertSuccessful();

        unlink(ejected('separator.blade.php'));

        $this->artisan('shape:eject', ['--status' => true])
            ->expectsOutputToContain('gone')
            ->assertSuccessful();
    });
});
