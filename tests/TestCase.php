<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Tests;

use Illuminate\Filesystem\Filesystem;
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
     * The compiled view directories this process has already thrown away the
     * contents of — see `defineEnvironment`.
     *
     * @var array<string, true>
     */
    protected static array $compiledViewsCleared = [];

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
            // The directory has to be there before the provider boots.
            //
            // Ejected components are registered only when their directory
            // exists, which is what an application that has ejected nothing
            // wants. A test file creates the directory in `beforeEach`, which
            // runs after the application has booted — so whichever test the
            // random order puts first would boot without the path registered,
            // and the packaged component would answer where the ejected one
            // should have.
            if (! is_dir(static::$componentsPath)) {
                mkdir(static::$componentsPath, 0777, true);
            }

            $app['config']->set('shape.components_path', static::$componentsPath);
        }

        if (static::$configPath !== null) {
            $app->useConfigPath(static::$configPath);
        }

        if (static::$storagePath !== null) {
            $app->useStoragePath(static::$storagePath);
        }

        $compiled = $this->compiledViewPath();

        // Process ids come round again, and the directory is named after one.
        // Emptied once per process rather than once per test, because the
        // directory is deliberately shared by the tests a worker runs: what is
        // being thrown away is an older run's views, compiled against a
        // different version of the package or of Blaze.
        if (! isset(static::$compiledViewsCleared[$compiled])) {
            (new Filesystem)->deleteDirectory($compiled);

            static::$compiledViewsCleared[$compiled] = true;
        }

        if (! is_dir($compiled)) {
            mkdir($compiled, 0777, true);
        }

        $app['config']->set('view.compiled', $compiled);

        $app['config']->set('view.paths', [
            ...(array) $app['config']->get('view.paths'),
            __DIR__.'/fixtures/views',
        ]);
    }

    /**
     * Where this test case's application compiles its views.
     *
     * A directory per worker, because the suite runs in parallel, components
     * are compiled on demand, and `x-dynamic-component` writes temporary
     * compiled views of its own. All of that shares one directory by default,
     * so workers truncate files that other workers are midway through
     * including — which surfaces as a component rendering as an empty string,
     * in whichever test happened to be running at the time.
     *
     * And a directory per boot path on top of that, which is what
     * `BlazeTestCase` overrides this for. Blaze compiles a component into a
     * function definition its runtime then calls; included by an application
     * that never booted Blaze, that file defines the function and prints
     * nothing. Blade decides a compiled view is still current by comparing it
     * against the modification time of its source, which is the same file
     * either way — so with one directory between them, whichever suite
     * compiled a component first answers for the other, and the other renders
     * an empty string.
     */
    protected function compiledViewPath(): string
    {
        return sys_get_temp_dir().'/shape-compiled-views-'.getmypid();
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
