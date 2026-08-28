<?php

namespace Workbench\App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Onelegstudios\Shape\Facades\Shape;

use function Orchestra\Testbench\package_path;

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

        $this->configureDocs();
    }

    /**
     * Point laradocs at the package's own `docs/` directory.
     *
     * The workbench is the documentation site. Testbench already boots a real
     * Laravel application to run the tests in, so pointing a docs package at the
     * markdown that is committed beside the components costs one config array —
     * and the components in the previews are the components, rendering.
     *
     * Set in `register()` so that it lands before laradocs registers its routes,
     * which it does in `boot()`.
     */
    protected function configureDocs(): void
    {
        config([
            'laradocs.docs.path' => package_path('docs'),

            // Rendered HTML is cached by file mtime, which is right for a
            // deployed site and wrong for one being written: `composer serve`
            // is how these pages get read while they are being changed.
            'laradocs.cache.enabled' => false,

            'laradocs.ui.brand.title' => 'Shape',
            'laradocs.ui.brand.tagline' => 'A Blade component library that folds',
            'laradocs.ui.accent' => 'oklch(54.6% 0.104 180)',

            // `@docs('preview', name: 'button')` renders `docs/previews/button.blade.php`
            // and prints it. One source for the example and the picture of it.
            'laradocs.macros.preview' => 'docs-preview',
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->styleTheDocs();
    }

    /**
     * Put Shape's stylesheet in the head of every documentation page.
     *
     * The previews render real components, so they need the real tokens. The
     * layout belongs to laradocs and is not this package's file to edit — but it
     * pushes a `head` stack, which is exactly the hook for this.
     *
     * A link rather than an inline `<style>`: GitHub-flavoured markdown escapes
     * a raw `<style>` tag on principle, so a stylesheet written into the page by
     * the preview macro arrives as text. The stack is upstream of the markdown.
     */
    protected function styleTheDocs(): void
    {
        View::composer('laradocs::layout', function ($view): void {
            $view->getFactory()->startPush(
                'head',
                '<link rel="stylesheet" href="'.route('shape.docs.css').'">',
            );
        });
    }
}
