<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Onelegstudios\Shape\Tests\TestCase;

beforeAll(function () {
    // Both are the skeleton's by default, and the skeleton lives in `vendor/`:
    // one `config/` and one `resources/` shared by every worker in a parallel
    // run. This command writes into both when it is told to draw the library in
    // another set, so both are moved somewhere this file owns.
    TestCase::$configPath = sys_get_temp_dir().'/shape-install-config-'.getmypid();
    TestCase::$componentsPath = sys_get_temp_dir().'/shape-install-icons-'.getmypid();

    // And the fetched set is cached under `storage/`, which the tests for the
    // sources empty between cases.
    TestCase::$storagePath = sys_get_temp_dir().'/shape-install-storage-'.getmypid();
});

afterAll(function () {
    removeDirectory((string) TestCase::$configPath);
    removeDirectory((string) TestCase::$componentsPath);
    removeDirectory((string) TestCase::$storagePath);

    TestCase::$configPath = null;
    TestCase::$componentsPath = null;
    TestCase::$storagePath = null;
});

beforeEach(function () {
    $this->app_path = sys_get_temp_dir().'/shape-install-'.getmypid();

    removeDirectory($this->app_path);

    mkdir($this->app_path, 0777, true);

    $this->css = $this->app_path.'/app.css';
    $this->js = $this->app_path.'/app.js';

    $this->config = (string) TestCase::$configPath;
    $this->icons = (string) TestCase::$componentsPath;

    removeDirectory($this->config);
    removeDirectory($this->icons);

    $this->storage = (string) TestCase::$storagePath;

    removeDirectory($this->storage);

    mkdir($this->config, 0777, true);
    mkdir($this->icons.'/icon', 0777, true);
    mkdir($this->storage.'/framework/views', 0777, true);

    // What the prompt offers. Written out rather than derived from the config,
    // so that a set added or a notice reworded has to be said here too — the
    // vendor's name is read out of `notice`, and this is where that is checked.
    $this->offered = [
        'hero' => 'Heroicons (hero)',
        'lucide' => 'Lucide (lucide)',
        'tabler' => 'Tabler Icons (tabler)',
        'phosphor' => 'Phosphor Icons (phosphor)',
        'bootstrap' => 'Bootstrap Icons (bootstrap)',
        'remix' => 'Remix Icon (remix)',
        'material' => 'Material Symbols (material)',
    ];
});

afterEach(function () {
    removeDirectory($this->app_path);
    removeDirectory($this->config);
    removeDirectory($this->icons);
    removeDirectory($this->storage);
});

/**
 * A set of the library's own invention, fetched from a faked repository.
 *
 * Named afresh per test, because a set's name is its cache directory under
 * `storage/` — which the skeleton shares with every other worker — and because
 * `PharData` holds every archive it has opened by filename for the life of the
 * process.
 *
 * Its layout is Lucide's: one style, one size, flat. What is under test here is
 * which set `shape:install` hands to the generator and what it records
 * afterwards, not a layout `shape:icon` already has its own tests for.
 */
function aFetchableSet(): string
{
    $name = 'installed'.bin2hex(random_bytes(6));

    $slots = [];
    $files = [];

    foreach ((array) config('shape.icon_slots') as $slot => $definition) {
        $drawn = str_replace('shape-', '', (string) $slot);

        $slots[$slot] = $drawn;
        $files[$drawn.'.svg'] = '<svg viewBox="0 0 24 24"><path d="M0 0h24" /></svg>';
    }

    config()->set('shape.icon_sets.'.$name, [
        'repo' => 'onelegstudios/'.$name,
        'ref' => 'main',
        'path' => 'icons',
        'notice' => 'A set that exists for this test.',
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'slots' => $slots,
    ]);

    $path = sys_get_temp_dir().'/shape-install-archive-'.getmypid().'-'.uniqid();

    $archive = new PharData($path.'.tar');

    foreach ($files as $entry => $contents) {
        $archive->addFromString($name.'-main/icons/'.$entry, $contents);
    }

    $archive->compress(Phar::GZ);

    Http::fake(['codeload.github.com/*' => Http::response((string) file_get_contents($path.'.tar.gz'))]);

    unset($archive);

    @unlink($path.'.tar');
    @unlink($path.'.tar.gz');

    return $name;
}

it('imports the tokens after tailwind', function () {
    file_put_contents($this->css, "@import 'tailwindcss';\n\n.something { color: red; }\n");

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => 'hero'])->assertSuccessful();

    // Order matters: tokens declared before Tailwind's own import would be
    // overwritten by the theme they exist to override.
    expect(file_get_contents($this->css))->toBe(implode("\n", [
        "@import 'tailwindcss';",
        "@import '../../vendor/onelegstudios/shape/resources/css/shape.css';",
        '',
        '.something { color: red; }',
        '',
    ]));
});

it('registers the script', function () {
    file_put_contents($this->js, "import './bootstrap';\n");

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => 'hero'])->assertSuccessful();

    expect(file_get_contents($this->js))
        ->toContain("import shape from '../../vendor/onelegstudios/shape/resources/js/shape.js'")
        ->toContain('shape()');
});

it('changes nothing on a second run', function () {
    file_put_contents($this->css, "@import 'tailwindcss';\n");
    file_put_contents($this->js, "import './bootstrap';\n");

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => 'hero'])->assertSuccessful();

    $css = (string) file_get_contents($this->css);
    $js = (string) file_get_contents($this->js);

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => 'hero'])
        ->expectsOutputToContain('already imports the tokens')
        ->expectsOutputToContain('already registers the script')
        ->assertSuccessful();

    expect(file_get_contents($this->css))->toBe($css)
        ->and(file_get_contents($this->js))->toBe($js);
});

it('prints what it could not place rather than creating a file it invented', function () {
    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => 'hero'])
        ->expectsOutputToContain('not found')
        ->assertSuccessful();

    expect(file_exists($this->css))->toBeFalse()
        ->and(file_exists($this->js))->toBeFalse();
});

it('asks which set the library is drawn in, and does nothing more when the answer is the one that ships', function () {
    file_put_contents($this->css, "@import 'tailwindcss';\n");
    file_put_contents($this->js, "import './bootstrap';\n");

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js])
        ->expectsChoice('Which icon set should Shape be drawn in?', 'hero', $this->offered)
        ->expectsOutputToContain('drawn in [hero]')
        ->assertSuccessful();

    // The answer that changes nothing is also the answer with nothing to
    // publish: the slots ship drawn in Heroicons, so pressing enter leaves the
    // installation the two lines it has always been.
    expect(file_exists($this->config.'/shape.php'))->toBeFalse()
        ->and(glob($this->icons.'/icon/*.blade.php'))->toBe([]);
});

it('does not ask when there is no terminal to ask', function () {
    file_put_contents($this->css, "@import 'tailwindcss';\n");
    file_put_contents($this->js, "import './bootstrap';\n");

    // Which is what keeps a scripted install the install it has always been.
    $this->artisan('shape:install --no-interaction --css='.$this->css.' --js='.$this->js)
        ->expectsOutputToContain('drawn in [hero]')
        ->assertSuccessful();

    expect(file_exists($this->config.'/shape.php'))->toBeFalse();
});

it('draws the library in the set it is told to, and records it', function () {
    $set = aFetchableSet();

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => $set])
        ->assertSuccessful();

    // Every slot, under the role it plays rather than under whatever the set
    // that drew it calls the drawing.
    foreach (array_keys((array) config('shape.icon_slots')) as $slot) {
        expect(file_exists($this->icons.'/icon/'.$slot.'.blade.php'))->toBeTrue();
    }

    // And the answer recorded, so that the next `shape:icon` reads it rather
    // than writing a Heroicon into a directory of somebody else's drawings.
    expect(file_get_contents($this->config.'/shape.php'))
        ->toContain("'icon_set' => '{$set}'")
        ->not->toContain("'icon_set' => 'hero'");
});

it('refuses a set that is not configured', function () {
    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => 'nope'])
        ->expectsOutputToContain('No icon set named [nope] is configured.')
        ->assertFailed();

    expect(file_exists($this->config.'/shape.php'))->toBeFalse()
        ->and(glob($this->icons.'/icon/*.blade.php'))->toBe([]);
});

it('leaves icon_set naming the set the drawings on disk came from when the icons cannot be generated', function () {
    // A set with nothing to fetch it from, which is the offline run and the
    // unreachable repository arriving at the same place.
    config()->set('shape.icon_sets.nowhere', [
        'notice' => 'A set that says nothing about where it is drawn.',
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'slots' => ['shape-close' => 'x'],
    ]);

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js, '--icons' => 'nowhere'])
        ->expectsOutputToContain('still says [hero]')
        ->assertFailed();

    // The drawings are written before the config that names them, so a run that
    // could not write the first has not written the second either. A config
    // saying `nowhere` over a directory of Heroicons is the mixed-set page the
    // whole arrangement exists to prevent.
    expect(file_exists($this->config.'/shape.php'))->toBeFalse();
});

it('offers a set whose notice names no vendor under its own name', function () {
    config()->set('shape.icon_sets.nameless', [
        'notice' => '',
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'slots' => [],
    ]);

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js])
        ->expectsChoice(
            'Which icon set should Shape be drawn in?',
            'hero',
            [...$this->offered, 'nameless' => 'nameless'],
        )
        ->assertSuccessful();
});
