<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Tests;

use Livewire\Blaze\BlazeServiceProvider;

/**
 * A test case with Blaze registered ahead of the package provider, so that the
 * package boots down its Blaze-enabled path rather than the fallback one.
 */
abstract class BlazeTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BlazeServiceProvider::class,
            ...parent::getPackageProviders($app),
        ];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('view.paths', [
            ...(array) $app['config']->get('view.paths'),
            __DIR__.'/fixtures/views',
        ]);
    }
}
