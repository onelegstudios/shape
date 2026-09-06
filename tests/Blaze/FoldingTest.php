<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;
use Livewire\Blaze\Blaze;
use Livewire\Blaze\Events\ComponentFolded;
use Onelegstudios\Shape\Facades\Shape;
use Onelegstudios\Shape\FeedbackChannel;

/**
 * Folding happens during Blade compilation, so every test here starts from a
 * cleared view cache — otherwise a previously compiled fixture would render
 * without ever going through the Blaze pipeline.
 */
beforeEach(function () {
    clearCompiledViews();

    // Surface fold failures instead of silently falling back to the compiled path.
    Blaze::throw();
});

/**
 * Clear the view cache, and the one cache that outlives it.
 *
 * A component rendered from a string — which is what `x-dynamic-component`
 * produces, and Shape's icon dispatcher is one — has its source written into the
 * compiled-view directory and its name remembered in a static on `Component`.
 * `view:clear` deletes the file; the static still names it. The next render of
 * an icon then fails with "File does not exist at path" naming a hash, several
 * views deep, with nothing in the message naming a component.
 *
 * Any test that clears views between two renders needs this. It is one line, and
 * it is invisible until a fixture happens to contain a dynamic icon.
 */
function clearCompiledViews(): void
{
    Artisan::call('view:clear');

    Component::flushCache();
}

/**
 * @return list<string>
 */
function foldedComponentsWhileRendering(string $view, array $data = []): array
{
    return foldedComponentsWhile(fn () => view($view, $data)->render());
}

/**
 * The components folded into one template, compiling it rather than rendering it.
 *
 * Rendering compiles a template the first time it is asked for and never again
 * in that process: Blaze writes each component into a function named after its
 * path and guards the `require` with `function_exists`, so a second render
 * reuses the function the first one left behind and never reaches the compiler.
 * Clearing the view cache does not undo that — a function cannot be undefined —
 * so a test that watches a template fold something another test has already
 * rendered has to compile that template itself.
 *
 * @return list<string>
 */
function foldedComponentsWhileCompiling(string $path): array
{
    return foldedComponentsWhile(fn () => Blade::compile($path));
}

/**
 * @return list<string>
 */
function foldedComponentsWhile(callable $callback): array
{
    $folded = [];

    Event::listen(ComponentFolded::class, function (ComponentFolded $event) use (&$folded): void {
        $folded[] = $event->name;
    });

    $callback();

    return $folded;
}

it('folds a button when every prop that drives logic is static', function () {
    expect(foldedComponentsWhileRendering('static-button'))
        ->toContain('shape::button');
});

it('folds icons', function () {
    expect(foldedComponentsWhileRendering('static-icon'))
        ->toContain('shape::icon.shape-checked');
});

it('folds an icon down to one drawing, leaving no switch behind', function () {
    // An icon is a matrix of styles against sizes, and both axes are static at
    // almost every call site — so what reaches the compiled template is one
    // `<svg>` and none of the machinery that chose it.
    $fixture = __DIR__.'/../fixtures/views/static-icon.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('switch')
        ->not->toContain('$__blaze->compile(')
        ->toContain('data-shape-icon');
});

it('bakes in the drawing the size chose, not the one the default style would give', function () {
    // `static-icon` asks for `size="sm"` and names no style. Heroicons draws no
    // outline at 20px, so the size reaches for solid — and it is the solid
    // drawing that has to end up in the compiled template, decided at compile
    // time rather than left as a `match` for every request to re-run.
    $fixture = __DIR__.'/../fixtures/views/static-icon.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->toContain('viewBox="0 0 20 20"')
        ->toContain('fill="currentColor"')
        ->toContain('size-5')
        ->not->toContain('stroke="currentColor"');
});

it('bakes an icon size travelling through a parent prop into that parent\'s fold', function () {
    // The shape of most of the places this library draws an icon: the
    // size is a prop on the component around it, and the icon is baked into that
    // component's fold rather than folding on its own.
    $fixture = __DIR__.'/../fixtures/views/static-button-icon-size.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('switch')
        ->not->toContain('$__blaze->compile(')
        ->toContain('viewBox="0 0 16 16"')
        ->toContain('size-4');
});

it('memoizes an icon whose size is bound dynamically', function () {
    // Size drives which drawing is chosen, so it cannot be declared safe. What
    // catches this call site is memoization, and there are only three sizes, so
    // the cache actually hits.
    expect(foldedComponentsWhileRendering('dynamic-icon-size', ['size' => 'sm']))
        ->not->toContain('shape::icon.shape-checked');

    $fixture = __DIR__.'/../fixtures/views/dynamic-icon-size.blade.php';

    expect(Blaze::compile((string) file_get_contents($fixture), $fixture))
        ->toContain('Memo::key("shape::icon.shape-checked"');
});

it('renders an icon identically folded and unfolded, on every cell of the matrix', function () {
    // Six cells over four drawings, three of which Heroicons does not draw and
    // fills by scaling. Every one of them has to survive the compile-time path
    // and the run-time one identically, or folding is changing what renders.
    $cells = [
        ['solid', 'xs'], ['solid', 'sm'], ['solid', 'base'],
        ['outline', 'xs'], ['outline', 'sm'], ['outline', 'base'],
    ];

    foreach ($cells as [$variant, $size]) {
        $call = "<x-shape::icon.shape-checked variant=\"{$variant}\" size=\"{$size}\" />";

        clearCompiledViews();
        $folded = Blade::render($call);

        Blaze::disable();
        clearCompiledViews();
        $unfolded = Blade::render($call);
        Blaze::enable();

        expect(trim($folded))->toBe(trim($unfolded), "{$variant}/{$size}");
    }
});

it('bakes a nested icon into the compiled template', function () {
    // The nested icon doesn't fold on its own — it is rendered as part of the
    // button's fold. What matters is the result: the SVG ends up in the
    // compiled PHP, so nothing resolves an icon component at runtime.
    $fixture = __DIR__.'/../fixtures/views/static-button.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->toContain('<svg')
        ->toContain('data-shape-icon');
});

it('abandons folding when a prop that drives logic is bound dynamically', function () {
    // `variant` selects a match arm, so its value has to be known at compile
    // time. Blaze aborts on its own here — the component still renders, just
    // through the compiled path rather than the folded one.
    expect(foldedComponentsWhileRendering('dynamic-variant-button', ['variant' => 'primary']))
        ->not->toContain('shape::button');
});

it('keeps folding when a pass-through prop is bound dynamically', function () {
    // `tone` is only ever interpolated into `data-shape-tone`, never branched
    // on, which is what `safe: ['tone']` in the component declares.
    expect(foldedComponentsWhileRendering('dynamic-tone-button', ['tone' => 'danger']))
        ->toContain('shape::button');
});

it('renders identical markup whether or not the component folded', function () {
    clearCompiledViews();
    $folded = view('dynamic-tone-button', ['tone' => 'danger'])->render();

    Blaze::disable();
    clearCompiledViews();
    $unfolded = view('dynamic-tone-button', ['tone' => 'danger'])->render();
    Blaze::enable();

    expect($folded)->toBe($unfolded);
});

it('folds every call site in the typography and surfaces set', function () {
    // Counted rather than merely present. The fixture calls `heading` and
    // `text` more than once, so asserting that the set *contains* each name
    // would pass while one of the two call sites had quietly stopped folding.
    $folded = array_count_values(foldedComponentsWhileRendering('static-typography'));

    expect($folded)->toBe([
        'shape::heading' => 2,
        'shape::text' => 2,
        'shape::card.header' => 1,
        'shape::separator' => 1,
        'shape::badge' => 1,
        'shape::button' => 2,
        'shape::card.footer' => 1,
        'shape::card' => 1,
        'shape::empty' => 1,
    ]);
});

it('leaves nothing in the typography fixture to resolve at runtime', function () {
    // The collective failure this guards against: one unsafe call added to a
    // shared partial drops everything below it off the fold path, and nothing
    // else in the suite notices. A component that did not fold leaves a
    // `$__blaze->compile(` call behind; a fully folded template is just markup.
    $fixture = __DIR__.'/../fixtures/views/static-typography.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('$__blaze->compile(')
        ->toContain('data-shape-card')
        ->toContain('data-shape-icon');
});

it('keeps folding a heading whose level is bound dynamically', function () {
    // `level` only ever reaches the tag name, which is what `safe: ['level']`
    // declares — and what lets document hierarchy be computed at the call site
    // without costing the fold.
    expect(foldedComponentsWhileRendering('dynamic-heading-level', ['level' => 3]))
        ->toContain('shape::heading');
});

it('keeps folding a badge whose label is bound dynamically', function () {
    // `label` is interpolated and nothing more, so it is safe. This is the call
    // site that matters most: a badge in a table almost always has a dynamic
    // label, and without this it would drop to the memo path and miss on every
    // row whose label was unique.
    expect(foldedComponentsWhileRendering('dynamic-badge-label', ['label' => 'Invoice #1042']))
        ->toContain('shape::badge');
});

it('abandons folding a badge whose colour is bound dynamically', function () {
    // Unlike the button, the badge branches on `tone` to resolve its state
    // icon, so colour cannot be declared safe here. This is the documented
    // cost of the "never rely on colour alone" rule.
    expect(foldedComponentsWhileRendering('dynamic-badge-tone', ['tone' => 'danger']))
        ->not->toContain('shape::badge');
});

it('memoizes the slotless components when they cannot fold', function () {
    // Fold and memo are alternatives, not a stack: a folded component is
    // already inlined and has nothing left to cache. Memo is what catches the
    // badge and separator on the call sites where folding gives up.
    $fixture = __DIR__.'/../fixtures/views/dynamic-badge-tone.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)->toContain('Memo::key("shape::badge"');
});

it('bakes a state icon into a folded badge', function () {
    $fixture = __DIR__.'/../fixtures/views/static-badge.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->toContain('<svg')
        ->toContain('data-shape-icon');
});

it('renders a self closing component that has been folded', function () {
    // Folding inlines the component's body into its parent, which is where an
    // unguarded `{{ $slot }}` would go looking for a variable that never
    // existed. Self-closing calls have to keep working.
    expect(view('self-closing-button')->render())->toContain('data-shape-button');
});

it('folds every call site in the forms set', function () {
    // Counted rather than merely present, for the same reason the typography
    // assertion above is: `toContain` would pass while one of two call sites had
    // quietly stopped folding.
    $folded = array_count_values(foldedComponentsWhileRendering('static-form'));

    expect($folded)->toBe([
        'shape::label' => 2,
        'shape::description' => 1,
        'shape::input' => 2,
        'shape::error' => 2,
        'shape::field' => 2,
        'shape::textarea' => 1,
        'shape::select.option' => 1,
        'shape::select' => 1,
        'shape::radio' => 2,
        'shape::checkbox' => 1,
        'shape::switch' => 1,
    ]);
});

it('leaves nothing in the form fixture to resolve at runtime', function () {
    $fixture = __DIR__.'/../fixtures/views/static-form.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('$__blaze->compile(')
        ->toContain('data-shape-field')
        ->toContain('data-shape-control');
});

it('does not bake one request\'s validation errors into the compiled template', function () {
    // The single failure the unblaze hole exists to prevent. A folded component
    // is pre-rendered once at compile time, so without that hole the first
    // visitor to fail validation would have their message compiled into the
    // template and served to everyone after them.
    //
    // The view cache is deliberately *not* cleared between these two renders:
    // the point is that one compiled template produces two different results.
    $first = view('static-error-field')->withErrors(['email' => 'That address is already taken.'])->render();
    $second = view('static-error-field')->withErrors(['email' => 'That address is not valid.'])->render();
    $clean = view('static-error-field')->render();

    expect($first)->toContain('That address is already taken.')
        ->and($second)->toContain('That address is not valid.')
        ->and($second)->not->toContain('That address is already taken.')
        ->and($clean)->not->toContain('data-shape-error');
});

it('keeps the surrounding field folded even though the error is cut out of it', function () {
    // The hole is the error region, not the component around it.
    expect(foldedComponentsWhileRendering('static-error-field'))
        ->toContain('shape::field')
        ->toContain('shape::error')
        ->toContain('shape::input');
});

it('abandons folding for every aware child when the field name is dynamic', function () {
    // This is the cost of stating the name once. `@aware` props are treated as
    // unsafe, so a dynamic `:name` on the field takes the label, the control and
    // the error off the fold path — not just the one component that reads it.
    $folded = foldedComponentsWhileRendering('dynamic-field-name', ['name' => 'email']);

    expect($folded)
        ->not->toContain('shape::label')
        ->not->toContain('shape::input')
        ->not->toContain('shape::error');
});

it('renders identical form markup whether or not the components folded', function () {
    clearCompiledViews();
    $folded = view('static-form')->render();

    Blaze::disable();
    clearCompiledViews();
    $unfolded = view('static-form')->render();
    Blaze::enable();

    // Compared with whitespace collapsed, unlike the button assertion above.
    // Folding trims the blank lines an error that rendered nothing leaves
    // behind, so the two paths differ by insignificant whitespace and by
    // nothing else. How Blaze trims is Blaze's business; what this package
    // promises is that the elements, attributes and content are the same.
    $normalise = function (string $html): string {
        $html = (string) preg_replace('/\s+/', ' ', $html);

        return trim((string) preg_replace('/>\s+</', '><', $html));
    };

    expect($normalise($folded))->toBe($normalise($unfolded));
});

it('folds every call site in the overlay set', function () {
    // The correction this step earned. The plan filed `modal` and `dropdown`
    // under compile-only, on the reasoning that an overlay has to inspect its
    // slots to know whether it has a header, a footer, a heading. Built on
    // `<dialog>` and the `popover` attribute it inspects nothing: the heading is
    // a prop, the footer is a component, and open state is the platform's. So
    // the whole set folds, including the two that were supposed not to.
    $folded = array_count_values(foldedComponentsWhileRendering('static-overlays'));

    expect($folded)->toBe([
        'shape::overlay.trigger' => 2,
        'shape::text' => 3,
        'shape::overlay.close' => 1,
        'shape::button' => 2,
        'shape::overlay.footer' => 1,
        'shape::modal' => 1,
        'shape::drawer' => 1,
        'shape::dropdown.trigger' => 1,
        'shape::dropdown.item' => 3,
        'shape::dropdown' => 1,
        'shape::popover.trigger' => 1,
        'shape::popover' => 1,
        'shape::tooltip' => 1,
    ]);
});

it('leaves nothing in the overlay fixture to resolve at runtime', function () {
    $fixture = __DIR__.'/../fixtures/views/static-overlays.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('$__blaze->compile(')
        ->toContain('<dialog')
        ->toContain('data-shape-popover');
});

it('renders identical overlay markup whether or not the components folded', function () {
    clearCompiledViews();
    $folded = view('static-overlays')->render();

    Blaze::disable();
    clearCompiledViews();
    $unfolded = view('static-overlays')->render();
    Blaze::enable();

    $normalise = function (string $html): string {
        $html = (string) preg_replace('/\s+/', ' ', $html);

        return trim((string) preg_replace('/>\s+</', '><', $html));
    };

    expect($normalise($folded))->toBe($normalise($unfolded));
});

it('bakes a translation into a folded component, which is why none of them call one', function () {
    // Not a test of Blaze so much as the reason for a rule. A folded component
    // is pre-rendered at compile time, so `__()` inside one resolves once and
    // serves that locale to every visitor afterwards — the same failure the
    // error component's `@unblaze` hole exists to prevent, in a place nobody
    // thinks to look for request state.
    //
    // `ComponentConventionsTest` enforces the rule; this records what it costs.
    clearCompiledViews();

    Lang::addLines(['probe.hello' => 'Hello'], 'en');
    Lang::addLines(['probe.hello' => 'Hej'], 'sv');

    $english = Blade::render('<x-localised />');

    app()->setLocale('sv');

    $swedish = Blade::render('<x-localised />');

    expect(trim($english))->toBe('<span data-probe>Hello</span>')
        ->and(trim($swedish))->toBe(trim($english));
});

it('folds every call site in the feedback set', function () {
    // Counted rather than merely present, like the sets before it. The confirm
    // dialog reports as one fold and not five: the modal, the text, the footer
    // and the two buttons inside it are baked into its body, the same way the
    // button's icon is.
    $folded = array_count_values(foldedComponentsWhileRendering('static-feedback'));

    expect($folded)->toBe([
        'shape::text' => 1,
        'shape::alert' => 2,
        'shape::progress' => 2,
        'shape::confirm' => 1,
    ]);
});

it('leaves nothing in the feedback fixture to resolve at runtime', function () {
    $fixture = __DIR__.'/../fixtures/views/static-feedback.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('$__blaze->compile(')
        ->toContain('data-shape-alert')
        ->toContain('<progress')
        ->toContain('data-shape-confirm');
});

it('keeps folding a progress bar whose value is bound dynamically', function () {
    // The call site every progress bar has. It folds because the element
    // computes its own width — the template never divides `value` by `max`, and
    // `indeterminate` is a separate prop precisely so that nothing has to branch
    // on the one value that is always dynamic.
    expect(foldedComponentsWhileRendering('dynamic-progress-value', ['value' => 42]))
        ->toContain('shape::progress');
});

it('keeps folding a toast whose text is bound dynamically', function () {
    // Which is what makes the template approach pay: the toaster renders one
    // toast per tone at compile time, and a toast's text is never known then.
    expect(foldedComponentsWhileRendering('dynamic-toast-heading', ['heading' => 'Invoice sent']))
        ->toContain('shape::toast');
});

it('abandons folding an alert whose colour is bound dynamically', function () {
    // Same trade the badge makes, for the same reason: the alert branches on
    // `tone` to resolve its glyph, so colour cannot be safe here. It is the
    // documented price of never relying on colour alone.
    expect(foldedComponentsWhileRendering('dynamic-alert-tone', ['tone' => 'danger']))
        ->not->toContain('shape::alert');
});

it('renders identical feedback markup whether or not the components folded', function () {
    clearCompiledViews();
    $folded = view('static-feedback')->render();

    Blaze::disable();
    clearCompiledViews();
    $unfolded = view('static-feedback')->render();
    Blaze::enable();

    $normalise = function (string $html): string {
        $html = (string) preg_replace('/\s+/', ' ', $html);

        return trim((string) preg_replace('/>\s+</', '><', $html));
    };

    expect($normalise($folded))->toBe($normalise($unfolded));
});

it('does not fold the toaster, and does not need to', function () {
    // The one component in the library that reads request state without cutting
    // a hole for it. There is one on a page, so folding it would save nothing
    // and cost a boundary that variables cannot cross. Its templates fold on
    // their own — which is the part that matters, since they are what gets
    // cloned for every toast.
    // Compiled rather than rendered, and the two templates separately, because
    // the test below renders the toaster too: whichever of the two the random
    // order runs second would watch a render that never reaches the compiler.
    expect(foldedComponentsWhileCompiling(__DIR__.'/../fixtures/views/static-toaster.blade.php'))
        ->not->toContain('shape::toaster');

    expect(foldedComponentsWhileCompiling(__DIR__.'/../../resources/views/shape/toaster/toaster.blade.php'))
        ->toContain('shape::toast');
});

it('does not bake one request\'s flashed toasts into the compiled template', function () {
    // The failure the toaster would have if it folded, and the reason it does
    // not: the view cache is deliberately not cleared between these renders, so
    // one compiled template has to produce two different payloads.
    Shape::toast()->success('Invoice sent')->send();
    $first = view('static-toaster')->render();

    Session::forget(FeedbackChannel::SESSION_KEY);
    Shape::toast()->danger('Card declined')->send();
    $second = view('static-toaster')->render();

    expect($first)->toContain('Invoice sent')
        ->and($second)->toContain('Card declined')
        ->and($second)->not->toContain('Invoice sent');
});

it('folds every call site in the data display set', function () {
    // The set that renders in loops, so the one where this census is worth the
    // most: a cell and an avatar appear once per row, and a component that
    // quietly stopped folding here costs a page far more than one that stopped
    // folding in a card.
    //
    // `shape::empty` is absent on purpose. The table and the list each call it,
    // and a component called inside another's fold is baked into that fold
    // without an event of its own — the same reason the confirm dialog reports
    // one name and nothing under it.
    $folded = array_count_values(foldedComponentsWhileRendering('static-data'));

    expect($folded)->toBe([
        'shape::table.heading' => 2,
        'shape::table.head' => 1,
        'shape::table.cell' => 2,
        'shape::table.row' => 1,
        'shape::table.body' => 1,
        'shape::table' => 1,
        'shape::avatar' => 3,
        'shape::list.item' => 2,
        'shape::list' => 1,
        'shape::stat' => 1,
        'shape::avatar.group' => 1,
        'shape::tabs.tab' => 2,
        'shape::tabs' => 1,
        'shape::tabs.panel' => 2,
    ]);
});

it('leaves nothing in the data fixture to resolve at runtime', function () {
    $fixture = __DIR__.'/../fixtures/views/static-data.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)
        ->not->toContain('$__blaze->compile(')
        ->toContain('data-shape-table')
        ->toContain('role="tabpanel"');
});

it('renders identical data markup whether or not the components folded', function () {
    clearCompiledViews();
    $folded = view('static-data')->render();

    Blaze::disable();
    clearCompiledViews();
    $unfolded = view('static-data')->render();
    Blaze::enable();

    $normalise = function (string $html): string {
        $html = (string) preg_replace('/\s+/', ' ', $html);

        return trim((string) preg_replace('/>\s+</', '><', $html));
    };

    expect($normalise($folded))->toBe($normalise($unfolded));
});

it('keeps folding a cell whose value is bound dynamically', function () {
    // The call site this whole step turns on: one cell per column per row, each
    // with a different value. `safe: ['value']` is what keeps it here rather
    // than on the memo path, where a prop set unique per row means a cache entry
    // per row and no hits at all.
    expect(foldedComponentsWhileRendering('dynamic-cell-value', ['value' => '£240.00']))
        ->toContain('shape::table.cell');
});

it('keeps folding a stat whose value and delta are bound dynamically', function () {
    // True only because the delta row is collapsed in CSS. Written as
    // `@if ($delta)` the prop would drive a branch, could not be safe, and every
    // stat with a computed delta — which is all of them — would land here in the
    // negative.
    expect(foldedComponentsWhileRendering('dynamic-stat-value', ['value' => '1,204', 'delta' => '12%']))
        ->toContain('shape::stat');
});

it('abandons folding an avatar whose picture is bound dynamically', function () {
    // `src` decides which element renders, so it cannot be safe. This is the
    // avatar list built from per-row URLs, and it is the badge's expensive row
    // in different clothes.
    expect(foldedComponentsWhileRendering('dynamic-avatar-src', ['src' => '/ada.jpg']))
        ->not->toContain('shape::avatar');
});

it('memoizes that avatar, for whatever that is worth', function () {
    // It memoizes, and every key is unique, so the table is a cost rather than a
    // saving. Worth asserting because the annotation promises the net is there;
    // the docs are where it is said that this call site falls through it.
    $fixture = __DIR__.'/../fixtures/views/dynamic-avatar-src.blade.php';

    $compiled = Blaze::compile((string) file_get_contents($fixture), $fixture);

    expect($compiled)->toContain('Memo::key("shape::avatar"');
});

it('abandons folding a tab whose selection is computed', function () {
    // The normal call site for a tab strip, since selection usually comes from
    // the current route. `selected` drives `aria-selected`, `tabindex` and
    // `aria-current`, so it branches — a handful of components on a page, and
    // stated in the docs rather than discovered.
    expect(foldedComponentsWhileRendering('dynamic-tab-selected', ['selected' => true]))
        ->not->toContain('shape::tabs.tab');
});

it('does not fold the pagination, and must not', function () {
    // The second compile-only component in the library. It loops a collection
    // the server produced this request; there is nothing here to bake.
    $folded = foldedComponentsWhileRendering('static-pagination', [
        'paginator' => new LengthAwarePaginator(range(1, 10), 120, 10, 1, ['path' => '/invoices']),
    ]);

    expect($folded)->not->toContain('shape::pagination');
});

it('does not bake one page of links into the compiled template', function () {
    // The failure folding it would cause, and the reason it is annotated the way
    // it is: the view cache is deliberately not cleared between these renders,
    // so one compiled template has to produce two different pages.
    $first = view('static-pagination', [
        'paginator' => new LengthAwarePaginator(range(1, 10), 120, 10, 1, ['path' => '/invoices']),
    ])->render();

    $second = view('static-pagination', [
        'paginator' => new LengthAwarePaginator(range(1, 10), 120, 10, 3, ['path' => '/invoices']),
    ])->render();

    expect($first)->toMatch('/aria-current="page"[^>]*>1</')
        ->and($second)->toMatch('/aria-current="page"[^>]*>3</');
});
