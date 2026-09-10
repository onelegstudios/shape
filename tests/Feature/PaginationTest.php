<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

function pages(int $current = 1, int $total = 120, int $perPage = 10): LengthAwarePaginator
{
    return new LengthAwarePaginator(range(1, $perPage), $total, $perPage, $current, ['path' => '/invoices']);
}

it('renders nothing when there is only one page', function () {
    // Laravel's own views make the same choice. A pager for a single page is
    // chrome that answers a question nobody asked.
    expect(Blade::render('<x-shape::pagination :paginator="$p" />', ['p' => pages(1, 5)]))
        ->toBe('');
});

it('reads the paginator and never the request', function () {
    // The whole API rests on this: the paginator already knows its URLs, so this
    // component has no reason to look at the current request — which is what
    // makes it safe to render the same markup from a mailable or a test.
    $source = (string) file_get_contents(__DIR__.'/../../resources/views/shape/pagination/pagination.blade.php');

    expect($source)
        ->not->toContain('request(')
        ->not->toContain('Request::')
        ->not->toContain('url(')
        ->not->toContain('route(');
});

it('is compiled rather than folded, and says so in the annotation', function () {
    // A folded pager would bake one visitor's page of links into the compiled
    // template and serve it to everybody.
    $source = (string) file_get_contents(__DIR__.'/../../resources/views/shape/pagination/pagination.blade.php');

    expect(trim($source))->toStartWith("@blaze\n");
});

it('disables the step it cannot take, at either end', function () {
    $first = Blade::render('<x-shape::pagination :paginator="$p" />', ['p' => pages(1)]);
    $last = Blade::render('<x-shape::pagination :paginator="$p" />', ['p' => pages(12)]);

    expect($first)
        ->toMatch('/<span[^>]*aria-disabled="true"[^>]*data-shape-pagination-previous/')
        ->toContain('rel="next"')
        ->and($last)
        ->toMatch('/<span[^>]*aria-disabled="true"[^>]*data-shape-pagination-next/')
        ->toContain('rel="prev"');
});

it('marks the page someone is on, rather than only colouring it', function () {
    expect(Blade::render('<x-shape::pagination :paginator="$p" />', ['p' => pages(3)]))
        ->toContain('aria-current="page"')
        ->toMatch('/aria-current="page"[^>]*>3</');
});

it('drops the framework\'s own previous and next from the number window', function () {
    // linkCollection() opens and closes with Previous and Next, already
    // rendered. Keeping them would print each one twice, in two languages.
    $html = Blade::render('<x-shape::pagination :paginator="$p" />', ['p' => pages(3)]);

    expect(substr_count($html, 'Previous'))->toBe(1)
        ->and(substr_count($html, 'Next'))->toBe(1);
});

it('renders the gap in the window as text rather than as a link', function () {
    $html = Blade::render('<x-shape::pagination :paginator="$p" />', ['p' => pages(6, 400)]);

    expect($html)->toMatch('/<span[^>]*aria-hidden="true"[^>]*>\.\.\.</');
});

it('falls back to previous and next when handed a paginator that has no window', function () {
    // linkCollection() belongs to LengthAwarePaginator alone. A simple paginator
    // has neither it nor a total, so `simple` is also what this component does
    // when it is given one — rather than a fatal on a method that isn't there.
    $simple = new Paginator(range(1, 10), 10, 2, ['path' => '/invoices']);

    $html = Blade::render('<x-shape::pagination :paginator="$p" />', ['p' => $simple]);

    expect($html)
        ->toContain('data-shape-pagination-previous')
        ->toContain('data-shape-pagination-next')
        ->not->toContain('aria-current="page"');
});

it('omits the numbers when a caller asks for the short form', function () {
    expect(Blade::render('<x-shape::pagination :paginator="$p" simple />', ['p' => pages(3)]))
        ->not->toContain('aria-current="page"')
        ->toContain('data-shape-pagination-next');
});

it('puts livewire attributes on the links and everything else on the nav', function () {
    // The navigation happens on the anchors, so that is where wire:navigate has
    // to be. A class or a key describes the nav element itself.
    $html = Blade::render('<x-shape::pagination :paginator="$p" wire:navigate class="justify-end" />', ['p' => pages(3)]);

    expect(substr_count($html, 'wire:navigate'))->toBeGreaterThan(1)
        ->and($html)->toMatch('/<nav[^>]*justify-end/')
        ->and($html)->not->toMatch('/<nav[^>]*wire:navigate/');
});

it('takes its words as props, so an application translates them at the call site', function () {
    // Nothing here is folded, so a translation helper inside this file would
    // work. It still does not belong here: a component that translates for you
    // is one a caller cannot correct.
    expect(Blade::render('<x-shape::pagination :paginator="$p" previous-label="Föregående" next-label="Nästa" />', ['p' => pages(3)]))
        ->toContain('Föregående')
        ->toContain('Nästa')
        ->not->toContain('Previous');
});

it('names the navigation landmark it creates', function () {
    expect(Blade::render('<x-shape::pagination :paginator="$p" label="Invoice pages" />', ['p' => pages(3)]))
        ->toContain('role="navigation"')
        ->toContain('aria-label="Invoice pages"');
});

it('moves every step in the row together', function (string $size, string $box, string $gap) {
    // A pager is one control repeated, so a step out of proportion is repeated
    // fifteen times across the page.
    $html = Blade::render('<x-shape::pagination :paginator="$p" :size="$size" />', ['p' => pages(3), 'size' => $size]);

    expect($html)
        ->toContain($box)
        ->toContain("[:where(&amp;)]:{$gap}")
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['xs', 'h-7 min-w-7 gap-0.5 px-1.5 text-xs', 'gap-0.5'],
    ['sm', 'h-8 min-w-8 gap-1 px-2 text-sm', 'gap-1'],
    ['base', 'h-9 min-w-9 gap-1 px-2.5 text-sm', 'gap-1'],
    ['lg', 'h-11 min-w-11 gap-1.5 px-3.5 text-base', 'gap-1.5'],
    ['xl', 'h-12 min-w-12 gap-2 px-4 text-lg', 'gap-2'],
]);

it('takes the chevrons down with the steps they sit in', function () {
    expect(Blade::render('<x-shape::pagination :paginator="$p" size="xs" />', ['p' => pages(3)]))
        ->toContain('[:where(&amp;)]:size-4');
});
