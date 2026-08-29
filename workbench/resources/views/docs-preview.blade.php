{{--
    The `@docs('preview', name: '…')` macro, rendered inside the docs site.

    The preview is a real Blade file in `docs/previews/`, rendered here and then
    printed underneath as its own source. One file, so a preview that stops
    compiling is a failing test rather than a page describing a prop that was
    renamed six months ago.

    Two things about the output are not stylistic choices.

    It is emitted as a single line, with the printed source's newlines written as
    `&#10;`. The macro is expanded into the markdown before CommonMark parses it,
    and a raw HTML block ends at the first blank line — so a preview with a blank
    line in it would have half of its own source parsed as markdown.

    The stylesheet is not here: GitHub-flavoured markdown escapes a raw `<style>`
    tag on principle, so it arrives as text. It is pushed into laradocs' `head`
    stack by the workbench provider instead, and built without Tailwind's
    preflight — these rules are guests on laradocs' page, and a reset would take
    the documentation's own typography down with it.
--}}

@php
    $file = \Orchestra\Testbench\package_path('docs/previews/'.basename($name).'.blade.php');
    $source = trim((string) file_get_contents($file));

    // One failed field, shared the way the session middleware shares it.
    //
    // `x-shape::error` reads the bag out of the view factory's shared data, so
    // passing it to `Blade::render()` as view data would not reach it. Sharing
    // is what a real request does. It is keyed to one field name, so only the
    // preview that asks for `billing_email` shows a message and every other
    // preview renders a valid field.
    view()->share('errors', (new \Illuminate\Support\ViewErrorBag)->put(
        'default',
        new \Illuminate\Support\MessageBag([
            'billing_email' => 'That address is already in use on another account.',
        ]),
    ));

    $rendered = trim(Blade::render($source));

    // A heading inside a preview becomes a div with the heading role.
    //
    // laradocs builds the page's contents list from every heading in the
    // document, and adds an anchor id to any that lacks one — so a modal
    // preview, whose dialog renders a real <h2>, otherwise puts "Delete project"
    // at the top of the contents for the page about modals. Stripping the id
    // only invites the anchor extension to invent one.
    //
    // `role="heading"` with `aria-level` is the same thing to a screen reader
    // and nothing at all to an XPath looking for h2, which is exactly the split
    // this needs: the preview keeps its semantics, the page keeps its outline.
    $rendered = (string) preg_replace('/<h([1-6])\b/i', '<div role="heading" aria-level="$1"', $rendered);
    $rendered = (string) preg_replace('/<\/h[1-6]>/i', '</div>', $rendered);

    $printed = str_replace("\n", '&#10;', e($source));

    // The stage is styled here; the source block underneath is left bare, so
    // that laradocs renders it as it renders every other code block on the page.
    //
    // `layout` decides how the example's own elements sit on the stage. A row of
    // buttons compares well side by side; a set of headings or a card does not,
    // and `layout: 'stack'` gives those the full width in a column. It is a
    // property of the picture, not of the example, so it is an argument to the
    // macro rather than a wrapper div printed in everybody's copied code.
    $arrangement = match ($layout ?? 'row') {
        'stack' => 'flex flex-col items-stretch gap-4',
        default => 'flex flex-wrap items-center gap-4',
    };

    $stage = 'mt-6 '.$arrangement.' rounded-shape-lg border border-shape-200 bg-white p-6 text-shape-900 dark:border-shape-800 dark:bg-shape-950 dark:text-shape-100';
@endphp
<div data-shape-preview class="{{ $stage }}">{!! str_replace("\n", ' ', $rendered) !!}</div><pre><code class="language-blade">{!! $printed !!}</code></pre>
