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
