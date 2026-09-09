<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Symfony\Component\Finder\Finder;
use Workbench\App\Providers\WorkbenchServiceProvider;

/**
 * The previews are the examples, rendered.
 *
 * A documentation page calls `@docs('preview', name: 'button')`, and the docs
 * site renders `docs/previews/button.blade.php` and prints that same file
 * underneath as the example. One file for the picture and the code — which is
 * only worth anything if the file still compiles, so that is asserted here
 * rather than discovered by a reader.
 */
uses(WithWorkbench::class);

function previewFiles(): array
{
    $files = [];

    foreach (Finder::create()->files()->in(__DIR__.'/../../docs/previews')->name('*.blade.php') as $file) {
        $name = substr($file->getFilename(), 0, -strlen('.blade.php'));

        $files[$name] = (string) file_get_contents($file->getPathname());
    }

    ksort($files);

    return $files;
}

/**
 * @return list<string>
 */
function previewCalls(): array
{
    $names = [];

    foreach (Finder::create()->files()->in(__DIR__.'/../../docs')->name('*.md') as $file) {
        preg_match_all("/@docs\('preview', name: '([^']+)'/", (string) file_get_contents($file->getPathname()), $matches);

        $names = [...$names, ...$matches[1]];
    }

    sort($names);

    return $names;
}

it('found the previews', function () {
    expect(previewFiles())->not->toBeEmpty();
});

it('renders every preview', function (string $name) {
    // Not a smoke test: rendering is the assertion. A prop renamed in a
    // component and not in the example fails here, which is the whole reason the
    // example is a Blade file rather than a fenced block of text.
    $html = Blade::render(previewFiles()[$name]);

    expect(trim($html))->not->toBeEmpty();
})->with(fn () => array_keys(previewFiles()));

it('has a file for every preview a page asks for', function () {
    expect(array_values(array_diff(previewCalls(), array_keys(previewFiles()))))->toBe([]);
});

/**
 * @return list<string>
 */
function previewsNamingVariables(): array
{
    return array_keys(array_filter(previewFiles(), fn (string $source): bool => str_contains($source, '$')));
}

it('prints a preview with its dollars out of KaTeX\'s reach', function (string $name) {
    // laradocs renders `$…$` as maths, and the pass that does it protects
    // markdown code rather than code: fenced blocks and backtick spans. The
    // macro emits a raw HTML block, one step before KaTeX runs, so the first
    // `$` in a preview opened an expression that closed at the next one — and
    // because the source is emitted on a single line, the newline that would
    // have ended the match is a `&#10;` and one expression took the rest of the
    // file. What came back was the source re-escaped into an attribute.
    //
    // So the invariant is that no `$` survives into the printed block, and it is
    // asserted over every preview that names a variable rather than over the one
    // that found it. A preview is written to be copied, and the next one to put
    // two of them on a line should not have to know any of this.
    $printed = (string) view('docs-preview', ['name' => $name])->render();

    $code = (string) preg_replace('/^.*<code class="language-blade">|<\/code>.*$/s', '', $printed);

    expect($code)->not->toContain('$');
})->with(fn () => previewsNamingVariables());

it('escapes them as the entity a browser reads back as a dollar', function () {
    // The other half: `&#36;` has to survive to the page as itself, or the fix
    // above would be a preview nobody can copy.
    $printed = (string) view('docs-preview', ['name' => 'avatar-group-overflow'])->render();

    expect($printed)->toContain('&#36;shown as &#36;initials');
});

it('has a page for every preview file', function () {
    // The other direction: a preview nothing renders is a file that will rot.
    expect(array_values(array_diff(array_keys(previewFiles()), previewCalls())))->toBe([]);
});

it('lets a rendered textarea through the markdown pipeline', function () {
    // GitHub-flavoured markdown bundles CommonMark's `DisallowedRawHtml`, which
    // escapes the opening `<` of nine tag names wherever they appear in raw
    // HTML. The preview macro hands its rendered component to the markdown as
    // raw HTML, so `textarea` being on that list meant every preview on that
    // page arrived as visible source and the page held no control at all.
    //
    // Nothing else in this file catches it, because the macro was never the
    // part that broke: it emits a perfectly good `<textarea>` and the markdown
    // escapes it afterwards. So this asserts the decision — the tag list the
    // workbench hands the parser — and what that list does to a document.
    //
    // The list rather than a rendered page, because laradocs boots no provider
    // and registers no routes under Testbench: there is no docs site to fetch
    // here, only the choice that shapes one.
    $environment = new Environment([
        'html_input' => 'allow',
        'disallowed_raw_html' => ['disallowed_tags' => WorkbenchServiceProvider::DISALLOWED_RAW_HTML_TAGS],
    ]);

    $environment->addExtension(new CommonMarkCoreExtension);
    $environment->addExtension(new GithubFlavoredMarkdownExtension);

    $converter = new MarkdownConverter($environment);

    expect((string) $converter->convert("x\n\n<div><textarea></textarea></div>\n"))
        ->toContain('<textarea')
        ->not->toContain('&lt;textarea');
});

it('keeps escaping the tags that rule exists for', function () {
    // Narrowed rather than emptied. `script` and `iframe` are what the rule is
    // actually for, and a preview has no business rendering either — so if one
    // ever reaches a page as live markup that is a hole rather than a feature.
    // `style` stays on it too, which is what keeps a preview from carrying a
    // stylesheet, as `docs/components/drawer.md` describes.
    expect(WorkbenchServiceProvider::DISALLOWED_RAW_HTML_TAGS)
        ->toContain('script')
        ->toContain('iframe')
        ->toContain('style')
        ->not->toContain('textarea');
});
