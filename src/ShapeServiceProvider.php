<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Blaze\Blaze;
use Onelegstudios\Shape\Console\Commands\DoctorCommand;
use Onelegstudios\Shape\Console\Commands\EjectAllCommand;
use Onelegstudios\Shape\Console\Commands\EjectCommand;
use Onelegstudios\Shape\Console\Commands\EjectStatusCommand;
use Onelegstudios\Shape\Console\Commands\IconAllCommand;
use Onelegstudios\Shape\Console\Commands\IconCommand;
use Onelegstudios\Shape\Console\Commands\IconReplaceCommand;
use Onelegstudios\Shape\Console\Commands\IconStatusCommand;
use Onelegstudios\Shape\Console\Commands\InstallCommand;
use Onelegstudios\Shape\Http\Middleware\RescueFeedbackFromNavigate;

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
        $this->app->singleton(Registry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shape');

        $this->registerComponentPaths();

        $this->registerBlaze();

        $this->registerFeedbackMiddleware();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/shape.php' => config_path('shape.php'),
        ], ['shape', 'shape-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/shape'),
        ], ['shape', 'shape-views']);

        // The whole library at once. `shape:eject` is the same operation for one
        // component and the components it composes, which is what anyone
        // customizing a single modal actually wants.
        $this->publishes([
            __DIR__.'/../resources/views/shape' => resource_path('views/shape'),
        ], ['shape', 'shape-components']);

        $this->publishes([
            __DIR__.'/../resources/css/shape.css' => resource_path('css/shape.css'),
        ], ['shape', 'shape-css']);

        $this->publishes([
            __DIR__.'/../resources/js/shape.js' => resource_path('js/shape.js'),
        ], ['shape', 'shape-js']);

        $this->commands([
            DoctorCommand::class,
            EjectAllCommand::class,
            EjectCommand::class,
            EjectStatusCommand::class,
            IconAllCommand::class,
            IconCommand::class,
            IconReplaceCommand::class,
            IconStatusCommand::class,
            InstallCommand::class,
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
     * Guard `Shape::toast()`/`Shape::confirm()` against a `wire:navigate`
     * redirect eating the event — see `RescueFeedbackFromNavigate`.
     *
     * Bound to the container rather than checked by class, for the same
     * reason `FeedbackChannel` reaches Livewire that way: this never runs
     * in an application that has never installed Livewire.
     */
    protected function registerFeedbackMiddleware(): void
    {
        if (! $this->app->bound('livewire')) {
            return;
        }

        $this->app->make('router')->pushMiddlewareToGroup('web', RescueFeedbackFromNavigate::class);
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
