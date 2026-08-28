<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('separates items with a rule on the list rather than a border on each', function () {
    // "Use fewer borders." Both draw the same line; only one of them draws it in
    // the right number of places, and only one of them has nothing to reset on
    // the last item.
    $html = Blade::render(<<<'BLADE'
    <x-shape::list>
        <x-shape::list.item>Ada</x-shape::list.item>
        <x-shape::list.item>Grace</x-shape::list.item>
    </x-shape::list>
    BLADE);

    expect($html)
        ->toContain('data-shape-list')
        ->toContain('[:where(&amp;)]:divide-y')
        ->toContain('Ada')
        ->and(Blade::render('<x-shape::list.item>Ada</x-shape::list.item>'))
        ->not->toContain('border-b');
});

it('renders its empty state without ever inspecting the slot', function () {
    // The promise is an empty state by default. Keeping it by asking whether the
    // slot has content would be a runtime question, and this component would
    // stop folding. So the empty state is always in the markup and CSS removes
    // it — which means it is present here even though there are items.
    $withItems = Blade::render('<x-shape::list><x-shape::list.item>Ada</x-shape::list.item></x-shape::list>');

    expect($withItems)
        ->toContain('data-shape-list-empty')
        ->toContain('data-shape-empty');
});

it('gives the empty state a heading, so a list with nothing in it is never a blank box', function () {
    expect(Blade::render('<x-shape::list />'))
        ->toContain('Nothing here yet');
});

it('lets a caller replace the empty state copy, or turn it off entirely', function () {
    expect(Blade::render('<x-shape::list empty-heading="No invoices" empty-description="They will appear as they are raised." />'))
        ->toContain('No invoices')
        ->toContain('They will appear as they are raised.')
        ->and(Blade::render('<x-shape::list :empty="false" />'))
        ->not->toContain('data-shape-list-empty');
});

it('keeps the empty state outside the list, because an item would hide itself', function () {
    // The rule that removes the empty state fires on `[data-shape-list-item]`.
    // An empty state written as an `<li>` would be an item like any other and
    // would remove itself the moment it appeared.
    $html = Blade::render('<x-shape::list />');

    expect($html)->toMatch('/<\/ul>\s*<div data-shape-list-empty>/');
});

it('takes an ordered list from a dynamic binding', function () {
    expect(Blade::render('<x-shape::list :as="$as" />', ['as' => 'ol']))
        ->toContain('<ol')
        ->toContain('</ol>');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::list class="divide-none" />'))
        ->toContain('[:where(&amp;)]:divide-y')
        ->toContain('divide-none');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::list wire:key="people" aria-label="People" />'))
        ->toContain('wire:key="people"')
        ->toContain('aria-label="People"')
        ->and(Blade::render('<x-shape::list.item wire:key="ada">Ada</x-shape::list.item>'))
        ->toContain('wire:key="ada"');
});
