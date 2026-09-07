<?php

declare(strict_types=1);

use Orchestra\Testbench\Concerns\WithWorkbench;

/**
 * The gallery is a page of call sites, and call sites rot.
 *
 * `docs/previews` is asserted to compile because a renamed prop would otherwise
 * leave a page describing the old one. The workbench gallery is the same kind of
 * file and had no such assertion, which is how it went on naming `check-circle`
 * and `x-mark` after those icons became `shape-checked` and `shape-close` —
 * three hundred lines that only fail in a browser somebody remembered to open.
 */
uses(WithWorkbench::class);

it('renders the gallery the workbench serves', function () {
    $this->get('/')->assertOk();
});

it('renders it again with the seed layer', function () {
    $this->get('/seed')->assertOk();
});

/**
 * The avatar previews point at real files now.
 *
 * They used to point at a route that drew an SVG face for any name it was
 * given, which could not 404 — so the previews could not rot. Three
 * photographs on disk can: a renamed file leaves a preview whose whole subject
 * is the picture showing a broken image, and nothing else in the suite looks at
 * a `src` after it is rendered.
 */
it('serves each portrait the avatar previews ask for', function (string $name) {
    $this->get("/avatars/{$name}.webp")
        ->assertOk()
        ->assertHeader('Content-Type', 'image/webp');
})->with(['alex', 'gabriel', 'kim']);

it('does not invent a face for a name with no photograph', function () {
    $this->get('/avatars/nobody.webp')->assertNotFound();
});
