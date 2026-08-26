<?php

namespace Workbench\App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Onelegstudios\Shape\Facades\Shape;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // In a real application the `Shape` alias arrives through package
        // discovery. The workbench loads the package provider by hand, so it
        // has to register the alias by hand too.
        AliasLoader::getInstance()->alias('Shape', Shape::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
