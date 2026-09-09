<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a button element by default', function () {
    $html = Blade::render('<x-shape::button>Save</x-shape::button>');

    expect($html)
        ->toContain('<button')
        ->toContain('type="button"')
        ->toContain('Save')
        ->toContain('data-shape-button');
});

it('renders an anchor when asked to', function () {
    $html = Blade::render('<x-shape::button as="a" href="/settings">Settings</x-shape::button>');

    expect($html)
        ->toContain('<a ')
        ->toContain('href="/settings"')
        ->not->toContain('<button');
});

it('becomes a link on an href without being told to', function () {
    // The badge, the avatar, the tab and the menu item all resolve their element
    // the same way: middle-click and "open in new tab" work for a link and for
    // nothing pretending to be one. An href on a `<button>` is the silent
    // version of that mistake, so the href settles the tag.
    $html = Blade::render('<x-shape::button href="/settings">Settings</x-shape::button>');

    expect($html)
        ->toContain('<a ')
        ->toContain('href="/settings"')
        ->not->toContain('<button');
});

it('lets as beat an href, for a link that is really a control', function () {
    $html = Blade::render('<x-shape::button as="div" href="/settings">Settings</x-shape::button>');

    expect($html)
        ->toContain('<div ')
        ->toContain('href="/settings"')
        ->not->toContain('<a ');
});

it('keeps the button element when there is no href to read', function () {
    expect(Blade::render('<x-shape::button wire:click="save">Save</x-shape::button>'))
        ->toContain('<button')
        ->toContain('type="button"');
});

it('carries hierarchy on the variant attribute and semantics on the tone attribute', function () {
    $html = Blade::render('<x-shape::button variant="subtle" tone="danger">Delete</x-shape::button>');

    expect($html)
        ->toContain('data-shape-variant="subtle"')
        ->toContain('data-shape-tone="danger"');
});

it('falls back to the neutral tone', function () {
    expect(Blade::render('<x-shape::button>Save</x-shape::button>'))
        ->toContain('data-shape-tone="neutral"');
});

it('reads its colours through tone variables rather than a variant colour matrix', function () {
    $primary = Blade::render('<x-shape::button variant="primary" tone="danger">Delete</x-shape::button>');
    $neutral = Blade::render('<x-shape::button variant="primary">Save</x-shape::button>');

    // Same classes either way — only the tone attribute differs, which is what
    // keeps `tone` a pass-through prop and the component foldable.
    expect($primary)->toContain('bg-[var(--shape-tone)]')
        ->and($neutral)->toContain('bg-[var(--shape-tone)]');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    $html = Blade::render('<x-shape::button class="rounded-full">Save</x-shape::button>');

    expect($html)
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('rounded-full');
});

it('renders leading and trailing icons', function () {
    $html = Blade::render('<x-shape::button icon="shape-plus" icon-trailing="shape-arrow-right">Add</x-shape::button>');

    expect(substr_count($html, 'data-shape-icon'))->toBe(2);
});

it('sizes itself squarely for icon only buttons', function () {
    $html = Blade::render('<x-shape::button square icon="shape-trash" aria-label="Delete" />');

    expect($html)
        ->toContain('size-10')
        ->toContain('aria-label="Delete"');
});

it('passes livewire and alpine attributes straight through', function () {
    $html = Blade::render('<x-shape::button wire:click="save" x-on:click="track()">Save</x-shape::button>');

    expect($html)
        ->toContain('wire:click="save"')
        ->toContain('x-on:click="track()"');
});

it('applies the requested size', function (string $size, string $expected) {
    expect(Blade::render("<x-shape::button size=\"{$size}\">Save</x-shape::button>"))
        ->toContain($expected);
})->with([
    ['sm', 'h-8'],
    ['base', 'h-10'],
    ['lg', 'h-12'],
]);

it('lets a caller change or remove its elevation', function () {
    // Elevation is applied at zero specificity, like the other defaults.
    expect(Blade::render('<x-shape::button variant="primary" class="shadow-none">Flat</x-shape::button>'))
        ->toContain('[:where(&amp;)]:shadow-sm')
        ->toContain('shadow-none');
});

it('uses tailwind\'s elevation scale rather than one of its own', function () {
    expect(Blade::render('<x-shape::button variant="primary">Save</x-shape::button>'))
        ->toContain('shadow-sm')
        ->not->toContain('shadow-shape');
});

it('draws no border by default, because the fill is already the boundary', function (string $variant) {
    // Three of the four arms paint something, and where there is a fill there
    // is an edge. `border` is the opt-in for where there is not one — or where
    // the button has to hold its own next to something already drawn with one.
    expect(Blade::render("<x-shape::button variant=\"{$variant}\" tone=\"danger\">Delete</x-shape::button>"))
        ->not->toContain('border ');
})->with(['primary', 'subtle', 'ghost']);

it('draws the border in a step of the tone rather than a palette of its own', function (string $variant, string $paint) {
    // The same rule the fills follow: every arm reads `--shape-tone-*`, so a
    // border never has to know a hue and a retheme carries it with everything
    // else. Which step is the only thing that varies, and it follows what the
    // edge sits against.
    expect(Blade::render("<x-shape::button variant=\"{$variant}\" tone=\"danger\" border>Delete</x-shape::button>"))
        ->toContain('border ')
        ->toContain($paint);
})->with([
    // Both arms read the same variable, because the edge is doing the same job
    // in each: bounding the tint on one, and the whole of the paint on the
    // other. `--shape-tone-border-strong` is the tone's answer to the neutral
    // `--shape-tone-border` the outline arm takes by default.
    ['subtle', 'border-[var(--shape-tone-border-strong)]'],
    ['outline', 'border-[var(--shape-tone-border-strong)]'],
    // The step past the fill, not the step past the tint — a pale edge on a
    // saturated fill reads as a highlight. Darker in light mode and brighter
    // in dark, which is why it is the tone's hover and not a fixed darkening.
    ['primary', 'border-[var(--shape-tone-hover)]'],
]);

it('leaves the outline border grey until it is asked for the tone', function () {
    // The one arm the prop adds no border to, because it has one already. All
    // it decides there is the colour, and the default stays the neutral edge
    // the outline alert and badge take, so the three go on agreeing.
    expect(Blade::render('<x-shape::button variant="outline" tone="danger">Delete</x-shape::button>'))
        ->toContain('border-[var(--shape-tone-border)]');

    expect(Blade::render('<x-shape::button variant="outline" tone="danger" border>Delete</x-shape::button>'))
        ->not->toContain('border-[var(--shape-tone-border)]');
});

it('keeps the neutral edge on a variant it does not know', function () {
    // `outline` is the default arm of both matches, so an unrecognised variant
    // lands on the same paint it always did rather than losing its border to
    // the split.
    expect(Blade::render('<x-shape::button variant="nonsense">Save</x-shape::button>'))
        ->toContain('border-[var(--shape-tone-border)]');
});

it('shows the ghost border only under the pointer, with the fill it arrives with', function () {
    // The arm stays unpainted at rest, so the edge waits with the tint rather
    // than drawing a box around nothing. `transition-colors` on the root
    // carries `border-color`, so the edge fades in with the background.
    expect(Blade::render('<x-shape::button variant="ghost" tone="danger" border>Delete</x-shape::button>'))
        ->toContain('hover:border-[var(--shape-tone-border-strong)]')
        ->toContain('hover:bg-[var(--shape-tone-tint)]')
        ->toContain('transition-colors');
});

it('reserves the ghost border before it paints it, so the hover moves nothing', function () {
    // A border that appears on hover is a pixel of layout that appears with
    // it. Drawing it transparent at rest is what keeps the label still.
    expect(Blade::render('<x-shape::button variant="ghost" tone="danger" border>Delete</x-shape::button>'))
        ->toContain('border-transparent');
});

it('keeps the other variants still on hover once they have a border', function (string $variant) {
    // Only ghost moves. A border on the other three is drawn at rest and stays
    // where it is.
    expect(Blade::render("<x-shape::button variant=\"{$variant}\" tone=\"danger\" border>Delete</x-shape::button>"))
        ->not->toContain('hover:border-');
})->with(['primary', 'subtle', 'outline']);

it('joins a group into one control with a seam and no doubled border', function () {
    $html = Blade::render(<<<'BLADE'
    <x-shape::button.group>
        <x-shape::button>Day</x-shape::button>
        <x-shape::button>Week</x-shape::button>
    </x-shape::button.group>
    BLADE);

    expect($html)
        ->toContain('data-shape-button-group')
        ->toContain('role="group"')
        // A pixel back, so two 1px borders overlap into one rule rather than
        // stacking into a 2px seam.
        ->toContain('[&amp;&gt;*:not(:first-child)]:-ml-px');
});

it('squares off only the corners that face a neighbour', function () {
    $html = Blade::render('<x-shape::button.group><x-shape::button>Day</x-shape::button></x-shape::button.group>');

    expect($html)
        ->toContain('[&amp;&gt;*:not(:first-child)]:rounded-l-none')
        ->toContain('[&amp;&gt;*:not(:last-child)]:rounded-r-none')
        // The ends are never named, which is what leaves a caller's own
        // `rounded-full` on the first and last button intact.
        ->not->toContain('rounded-l-shape')
        ->not->toContain('rounded-r-shape');
});

it('outranks the button\'s own corner without an important flag', function () {
    // The button writes its radius at zero specificity, and the group's
    // selector carries a class and a pseudo-class. So the inner corners flatten
    // on the cascade rather than on `!important` — the same bargain the button
    // offers a caller who passes `rounded-full`.
    $html = Blade::render(<<<'BLADE'
    <x-shape::button.group>
        <x-shape::button>Day</x-shape::button>
        <x-shape::button>Week</x-shape::button>
    </x-shape::button.group>
    BLADE);

    expect($html)
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->not->toContain('!');
});

it('turns the focus ring inward, and only where there is a neighbour', function () {
    // A ring drawn outside the button is painted over by the button beside it,
    // because later siblings paint last and this library has no z-index to lift
    // it with. `:not(:only-child)` is what leaves a group of one alone.
    expect(Blade::render('<x-shape::button.group><x-shape::button>Day</x-shape::button></x-shape::button.group>'))
        ->toContain('[&amp;&gt;*:not(:only-child):focus-visible]:-outline-offset-2');
});

it('stacks a vertical group and squares the corners on the other axis', function () {
    $html = Blade::render('<x-shape::button.group orientation="vertical"><x-shape::button>Day</x-shape::button></x-shape::button.group>');

    expect($html)
        ->toContain('flex-col items-stretch')
        ->toContain('data-shape-orientation="vertical"')
        ->toContain('[&amp;&gt;*:not(:first-child)]:-mt-px')
        ->toContain('[&amp;&gt;*:not(:first-child)]:rounded-t-none')
        ->toContain('[&amp;&gt;*:not(:last-child)]:rounded-b-none')
        ->not->toContain('-ml-px');
});

it('names a group only when it is given a name', function () {
    // Through the bag rather than an `@if`, so a null is dropped without
    // anything having to ask — an empty `aria-label` is worse than none.
    expect(Blade::render('<x-shape::button.group label="View"><x-shape::button>Day</x-shape::button></x-shape::button.group>'))
        ->toContain('aria-label="View"');

    expect(Blade::render('<x-shape::button.group><x-shape::button>Day</x-shape::button></x-shape::button.group>'))
        ->not->toContain('aria-label');
});

it('paints nothing of its own, so a button in a group is the button', function () {
    // The group is a container. Every colour in there is still the button's,
    // which is what keeps a toned or ghosted button in a group unremarkable.
    $alone = Blade::render('<x-shape::button variant="subtle" tone="danger">Delete</x-shape::button>');
    $grouped = Blade::render('<x-shape::button.group><x-shape::button variant="subtle" tone="danger">Delete</x-shape::button></x-shape::button.group>');

    expect($grouped)->toContain('bg-[var(--shape-tone-tint)]')
        ->and($alone)->toContain('bg-[var(--shape-tone-tint)]');
});

it('lets a caller override the group role and add classes to it', function () {
    $html = Blade::render('<x-shape::button.group role="toolbar" class="w-full"><x-shape::button>Day</x-shape::button></x-shape::button.group>');

    expect($html)
        ->toContain('role="toolbar"')
        ->not->toContain('role="group"')
        ->toContain('w-full')
        ->toContain('inline-flex');
});
