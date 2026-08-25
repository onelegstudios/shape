<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use Illuminate\Support\ServiceProvider;
use Onelegstudios\Shape\Console\Commands\ShapeCommand;

class ShapeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shape.php', 'shape');

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

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/shape.php' => config_path('shape.php'),
        ], ['shape', 'shape-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/shape'),
        ], ['shape', 'shape-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/shape'),
        ], ['shape', 'shape-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/shape'),
        ], ['shape', 'shape-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['shape', 'shape-migrations']);

        $this->commands([
            ShapeCommand::class,
        ]);
    }
}
