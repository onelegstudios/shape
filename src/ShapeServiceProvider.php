<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Blaze\Blaze;
use Onelegstudios\Shape\Console\Commands\ShapeCommand;

class ShapeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shape.php', 'shape');

        $this->app->singleton(FeedbackChannel::class);
        $this->app->singleton(Shape::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/shape.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shape');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'shape');

        $this->registerComponentPaths();

        $this->registerBlaze();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/shape.php' => config_path('shape.php'),
        ], ['laravel-shape', 'laravel-shape-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/shape'),
        ], ['laravel-shape', 'laravel-shape-views']);

        $this->publishes([
            __DIR__.'/../resources/views/shape' => resource_path('views/shape'),
        ], ['laravel-shape', 'laravel-shape-components']);

        $this->publishes([
            __DIR__.'/../resources/css/shape.css' => resource_path('css/shape.css'),
        ], ['laravel-shape', 'laravel-shape-css']);

        $this->publishes([
            __DIR__.'/../resources/js/shape.js' => resource_path('js/shape.js'),
        ], ['laravel-shape', 'laravel-shape-js']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/shape'),
        ], ['laravel-shape', 'laravel-shape-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/shape'),
        ], ['laravel-shape', 'laravel-shape-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['laravel-shape', 'laravel-shape-migrations']);

        $this->commands([
            ShapeCommand::class,
        ]);
    }

    /**
     * Register the component paths behind the `shape` prefix.
     *
     * Ejected components are registered first so that a component living in the
     * host application always resolves ahead of the one shipped by the package.
     */
    protected function registerComponentPaths(): void
    {
        $ejected = config('shape.components_path');

        if (is_string($ejected) && is_dir($ejected)) {
            Blade::anonymousComponentPath($ejected, 'shape');
        }

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/shape', 'shape');
    }

    /**
     * Opt the package views into Blaze, or fall back to no-op directives.
     *
     * Blaze is a suggested dependency rather than a required one. Components are
     * annotated with `@blaze` regardless, so the directives have to compile to
     * nothing when Blaze is absent.
     *
     * The binding is checked rather than the class, because that is the thing
     * this method actually goes on to use.
     */
    protected function registerBlaze(): void
    {
        if (! $this->app->bound('blaze')) {
            $this->registerBlazeFallbackDirectives();

            return;
        }

        Blaze::optimize()
            ->in(__DIR__.'/../resources/views/shape')
            ->in(__DIR__.'/../resources/views/shape/icon', memo: true);
    }

    /**
     * Compile `@blaze` away and keep `@unblaze` blocks rendering inline.
     *
     * `@unblaze(scope: [...])` has to keep binding `$scope` for the block body,
     * and restore any outer `$scope` afterwards.
     */
    protected function registerBlazeFallbackDirectives(): void
    {
        Blade::directive('blaze', fn (): string => '');

        Blade::directive('unblaze', fn (string $expression): string => implode('', [
            '<?php $__shapeScope = fn ($scope = []) => $scope; ?>',
            '<?php if (isset($scope)) $__shapeOuterScope = $scope; ?>',
            "<?php \$scope = \$__shapeScope({$expression}); ?>",
        ]));

        Blade::directive('endunblaze', fn (): string => implode('', [
            '<?php if (isset($__shapeOuterScope)) { $scope = $__shapeOuterScope; unset($__shapeOuterScope); } ?>',
        ]));
    }
}
