<?php

declare(strict_types=1);

beforeEach(function () {
    $this->app_path = sys_get_temp_dir().'/shape-install-'.getmypid();

    exec('rm -rf '.escapeshellarg($this->app_path));

    mkdir($this->app_path, 0777, true);

    $this->css = $this->app_path.'/app.css';
    $this->js = $this->app_path.'/app.js';
});

afterEach(function () {
    exec('rm -rf '.escapeshellarg($this->app_path));
});

it('imports the tokens after tailwind', function () {
    file_put_contents($this->css, "@import \"tailwindcss\";\n\n.something { color: red; }\n");

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js])->assertSuccessful();

    // Order matters: tokens declared before Tailwind's own import would be
    // overwritten by the theme they exist to override.
    expect(file_get_contents($this->css))->toBe(implode("\n", [
        '@import "tailwindcss";',
        '@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape.css";',
        '',
        '.something { color: red; }',
        '',
    ]));
});

it('registers the script', function () {
    file_put_contents($this->js, "import './bootstrap';\n");

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js])->assertSuccessful();

    expect(file_get_contents($this->js))
        ->toContain("import shape from '../../vendor/onelegstudios/laravel-shape/resources/js/shape.js'")
        ->toContain('shape()');
});

it('changes nothing on a second run', function () {
    file_put_contents($this->css, "@import \"tailwindcss\";\n");
    file_put_contents($this->js, "import './bootstrap';\n");

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js])->assertSuccessful();

    $css = (string) file_get_contents($this->css);
    $js = (string) file_get_contents($this->js);

    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js])
        ->expectsOutputToContain('already imports the tokens')
        ->expectsOutputToContain('already registers the script')
        ->assertSuccessful();

    expect(file_get_contents($this->css))->toBe($css)
        ->and(file_get_contents($this->js))->toBe($js);
});

it('prints what it could not place rather than creating a file it invented', function () {
    $this->artisan('shape:install', ['--css' => $this->css, '--js' => $this->js])
        ->expectsOutputToContain('not found')
        ->assertSuccessful();

    expect(file_exists($this->css))->toBeFalse()
        ->and(file_exists($this->js))->toBeFalse();
});
