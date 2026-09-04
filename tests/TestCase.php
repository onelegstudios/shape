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
     * Overrides where `config_path()` points, for the tests in one file.
     *
     * `shape:install` publishes the config when it is told to draw the library
     * in another set, and the application these tests boot is the Testbench
     * skeleton — whose `config/` is a directory inside `vendor/`, read on boot
     * and shared by every worker in a parallel run. A test that published into
     * it would be reconfiguring the tests running beside it.
     *
     * Set from `beforeAll` for the reason above it is: a publish path is
     * registered while the provider boots, so `config_path()` has to already
     * answer differently by then.
     */
    public static ?string $configPath = null;

    /**
     * Overrides where `storage_path()` points, for the tests in one file.
     *
     * `shape:icon` caches a fetched set under `storage/framework/shape/icons`,
     * and in the skeleton that is one directory shared by every worker — which
     * the tests for the sources themselves empty between cases. A test that
     * fetches a set has to own the directory it unpacks into, or a worker
     * beside it clears the cache midway through.
     *
     * Set from `beforeAll`, for the reason the two above it are.
     */
    public static ?string $storagePath = null;

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

        if (static::$configPath !== null) {
            $app->useConfigPath(static::$configPath);
        }

        if (static::$storagePath !== null) {
            $app->useStoragePath(static::$storagePath);
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
