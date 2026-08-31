<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Tests;

use Illuminate\Support\Facades\Http;
use Onelegstudios\Shape\Facades\Shape as ShapeFacade;
use Onelegstudios\Shape\ShapeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Overrides `shape.components_path` for the tests in one file.
     *
     * The path has to be in place before the application boots, so tests set
     * this from `beforeAll` rather than reaching for the config at run time.
     */
    public static ?string $componentsPath = null;

    /**
     * No test in this suite reaches the network.
     *
     * `shape:icon` can fetch an icon set from GitHub, and the components it
     * generates are committed — so CI has no reason to fetch and every reason
     * not to: a suite that quietly depends on GitHub being up is one that fails
     * for reasons having nothing to do with the change under test.
     *
     * Stated here rather than left to discipline. Any request no test has
     * explicitly faked throws `StrayRequestException` naming the URL it tried,
     * which turns "this test went to the network" from something nobody notices
     * into something that cannot be merged.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    protected function defineEnvironment($app): void
    {
        if (static::$componentsPath !== null) {
            $app['config']->set('shape.components_path', static::$componentsPath);
        }

        // Every worker compiles views into a directory it owns.
        //
        // The suite runs in parallel, components are compiled on demand, and
        // `x-dynamic-component` writes temporary compiled views of its own. All
        // of that shares one directory by default, so workers truncate files
        // that other workers are midway through including — which surfaces as a
        // component rendering as an empty string, in whichever test happened to
        // be running at the time.
        $compiled = sys_get_temp_dir().'/shape-compiled-views-'.getmypid();

        if (! is_dir($compiled)) {
            mkdir($compiled, 0777, true);
        }

        $app['config']->set('view.compiled', $compiled);

        $app['config']->set('view.paths', [
            ...(array) $app['config']->get('view.paths'),
            __DIR__.'/fixtures/views',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ShapeServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Shape' => ShapeFacade::class,
        ];
    }
}
