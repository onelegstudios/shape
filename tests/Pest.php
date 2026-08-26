<?php

declare(strict_types=1);

use Onelegstudios\Shape\Tests\BlazeTestCase;
use Onelegstudios\Shape\Tests\TestCase;

uses(TestCase::class)->in(__DIR__.'/Feature', __DIR__.'/Unit');

// Blaze is a suggested dependency, so the package has two boot paths. Tests in
// tests/Blaze run with Blaze registered; everything else runs without it.
uses(BlazeTestCase::class)->in(__DIR__.'/Blaze');
