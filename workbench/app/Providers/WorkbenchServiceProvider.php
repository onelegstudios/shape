<?php

namespace Workbench\App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laradocs\Contracts\DocumentParser;
use Laradocs\Parsers\MarkdownParser;
use Laradocs\Parsers\MarkdownPipelineFactory;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Onelegstudios\Shape\Facades\Shape;

use function Orchestra\Testbench\package_path;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * The tag names the markdown pipeline still escapes in raw HTML.
     *
     * CommonMark's default list carries `textarea` as well. See
     * `allowTextareaThroughMarkdown()` for why this one does not, and
     * `tests/Feature/DocsPreviewTest.php` for what holds it to that.
     *
     * @var list<string>
     */
    public const DISALLOWED_RAW_HTML_TAGS = [
        'title', 'style', 'xmp', 'iframe',
        'noembed', 'noframes', 'script', 'plaintext',
    ];

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
        $this->allowTextareaThroughMarkdown();
        $this->styleTheDocs();
    }

    /**
     * Let a rendered `<textarea>` survive the markdown pipeline.
     *
     * GitHub-flavoured markdown bundles CommonMark's `DisallowedRawHtml`
     * extension, which escapes the opening `<` of nine tag names wherever they
     * appear in raw HTML — `title`, `textarea`, `style`, `xmp`, `iframe`,
     * `noembed`, `noframes`, `script` and `plaintext`. The preview macro hands
     * its rendered component to the markdown as raw HTML, so every preview on
     * the textarea page arrived as visible source instead of a control: three
     * stages of escaped markup, and no `<textarea>` in the document at all.
     *
     * The rule is a sensible default for markdown written by strangers and the
     * wrong one here, where every document in `docs/` is committed beside the
     * components and the HTML being escaped is this package's own rendering of
     * its own component.
     *
     * So the list is narrowed rather than emptied: `textarea` comes off it and
     * the other eight stay on, which leaves `script` and `iframe` — the two the
     * rule exists for — escaped exactly as before. `style` stays on the list
     * too, so a preview still cannot carry a stylesheet; that is the limit
     * `docs/components/drawer.md` describes, and lifting it is a separate
     * decision from rendering a form control.
     *
     * Rebound rather than configured, because laradocs builds the environment
     * itself and takes no options for it. In `boot()` so that it lands after
     * laradocs' own `register()`, and on the same singleton, with the pipeline
     * left exactly as the package assembled it.
     */
    protected function allowTextareaThroughMarkdown(): void
    {
        $this->app->singleton(DocumentParser::class, fn ($app): MarkdownParser => new MarkdownParser(
            new MarkdownConverter(self::markdownEnvironment()),
            MarkdownPipelineFactory::markdownExtensions($app),
            MarkdownPipelineFactory::htmlExtensions(),
        ));
    }

    /**
     * The CommonMark environment the documentation site parses with.
     *
     * `DisallowedRawHtmlExtension` is named here even though
     * `GithubFlavoredMarkdownExtension` bundles it, and naming it is what makes
     * the `disallowed_raw_html` key legal. A bundle registers its members from
     * `register()`, which the environment defers until it initializes, while a
     * schema is registered the moment the extension carrying it is added. Under
     * `league/config` v1.2 the configuration is validated late enough for the
     * deferred registration to land first; under v1.1 — which is what
     * `--prefer-lowest` resolves — it is not, and the key is rejected as an
     * unexpected item. Adding the extension by name registers its schema
     * eagerly, and the copy the bundle adds afterwards is the same two
     * renderers at the same priority.
     *
     * Public and static so that `tests/Feature/DocsPreviewTest.php` asserts
     * this environment rather than a second copy of it — a copy is what let the
     * ordering above be wrong on one lane and right on the others.
     */
    public static function markdownEnvironment(): Environment
    {
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'disallowed_raw_html' => ['disallowed_tags' => self::DISALLOWED_RAW_HTML_TAGS],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new DisallowedRawHtmlExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new AttributesExtension);
        $environment->addExtension(new FootnoteExtension);

        return $environment;
    }

    /**
     * Put Shape's stylesheet and script in the head of every documentation page.
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
                '<link rel="stylesheet" href="'.route('shape.docs.css').'">'
                    .'<script type="module">import shape from "'.route('shape.docs.js').'"; shape()</script>',
            );
        });
    }
}
