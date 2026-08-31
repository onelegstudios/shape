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
