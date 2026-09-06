<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Onelegstudios\Shape\Tests\BlazeTestCase;
use Onelegstudios\Shape\Tests\TestCase;

uses(TestCase::class)->in(__DIR__.'/Feature', __DIR__.'/Unit');

// Blaze is a suggested dependency, so the package has two boot paths. Tests in
// tests/Blaze run with Blaze registered; everything else runs without it.
uses(BlazeTestCase::class)->in(__DIR__.'/Blaze');

/**
 * Remove a directory and everything under it, if it is there.
 *
 * Several suites write into a directory of their own under the system temporary
 * directory and clear it between cases. Shelling out to `rm -rf` for that is a
 * teardown that does nothing at all on Windows — silently, because a failed
 * `exec()` reports nothing — which leaves one case's files standing for the
 * next. Missing is not a failure here, the same as it is not for `rm -rf`.
 */
function removeDirectory(string $path): void
{
    (new Filesystem)->deleteDirectory($path);
}

/**
 * Copy a directory and everything under it, for the same reason.
 */
function copyDirectory(string $from, string $to): void
{
    (new Filesystem)->copyDirectory($from, $to);
}
