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

    /**
     * A compiled view directory of this suite's own — see the parent.
     *
     * The package's components are the same files whichever way it boots, and
     * Blade's staleness check is their modification time, so one directory
     * between the two suites has a folded component answering for the fallback
     * boot path and the other way about.
     */
    protected function compiledViewPath(): string
    {
        return parent::compiledViewPath().'-blaze';
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
