<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders its label from a prop rather than a slot', function () {
    // Slotless is what makes the badge memoizable, which matters because it is
    // the component that repeats most in a table.
    $html = Blade::render('<x-shape::badge label="Active" />');

    expect($html)
        ->toContain('<span')
        ->toContain('Active')
        ->toContain('data-shape-badge');
});

it('falls back to the neutral tone', function () {
    expect(Blade::render('<x-shape::badge label="Draft" />'))
        ->toContain('data-shape-tone="neutral"');
});

it('carries semantics on the tone attribute', function () {
    expect(Blade::render('<x-shape::badge label="Paid" tone="success" />'))
        ->toContain('data-shape-tone="success"');
});

it('resolves an icon for every state so colour is never the only signal', function (string $tone) {
    expect(Blade::render("<x-shape::badge label=\"State\" tone=\"{$tone}\" />"))
        ->toContain('data-shape-icon');
})->with(['success', 'danger', 'warning', 'info']);

it('gives each state a glyph of its own rather than reusing one', function () {
    // Asserting the drawings differ, rather than asserting any particular path,
    // is what actually enforces the rule: a badge has to stay readable in
    // greyscale, which it doesn't if two states share an icon.
    $glyphs = collect(['success', 'danger', 'warning', 'info'])
        ->map(fn (string $tone) => Blade::render("<x-shape::badge label=\"State\" tone=\"{$tone}\" />"))
        ->map(fn (string $html) => preg_match('/<path[^>]*d="([^"]+)"/', $html, $m) ? $m[1] : null);

    expect($glyphs->filter())->toHaveCount(4)
        ->and($glyphs->unique())->toHaveCount(4);
});

it('renders no icon for the neutral tone', function () {
    expect(Blade::render('<x-shape::badge label="Draft" />'))
        ->not->toContain('data-shape-icon');
});

it('renders none for the emphasis tones either, which are not states', function (string $tone) {
    expect(Blade::render("<x-shape::badge label=\"New\" tone=\"{$tone}\" />"))
        ->toContain("data-shape-tone=\"{$tone}\"")
        ->not->toContain('data-shape-icon');
})->with(['brand', 'accent']);

it('lets a caller opt out of the icon', function () {
    expect(Blade::render('<x-shape::badge label="Paid" tone="success" :icon="false" />'))
        ->not->toContain('data-shape-icon')
        ->and(Blade::render('<x-shape::badge label="Paid" tone="success" />'))
        ->toContain('data-shape-icon');
});

it('lets a caller name an icon of its own', function () {
    expect(Blade::render('<x-shape::badge label="New" icon="shape-plus" />'))
        ->toContain('data-shape-icon');
});

it('renders a trailing icon after the label when one is named', function () {
    $html = Blade::render('<x-shape::badge label="Overdue" icon-trailing="shape-arrow-right" />');

    expect($html)->toContain('data-shape-icon')
        ->and(strpos($html, 'Overdue'))->toBeLessThan(strpos($html, 'data-shape-icon'));
});

it('resolves nothing into the trailing slot from the tone', function () {
    // The state glyph leads; a second copy behind the label would say the same
    // thing twice.
    expect(Blade::render('<x-shape::badge label="Paid" tone="success" />'))
        ->toContain('data-shape-icon')
        ->and(substr_count(Blade::render('<x-shape::badge label="Paid" tone="success" />'), 'data-shape-icon'))->toBe(1);
});

it('draws a leading and a trailing icon together, in that order', function () {
    $html = Blade::render('<x-shape::badge label="Paid" tone="success" icon-trailing="shape-arrow-right" />');

    expect(substr_count($html, 'data-shape-icon'))->toBe(2)
        ->and(strpos($html, 'data-shape-icon'))->toBeLessThan(strpos($html, 'Paid'))
        ->and(strrpos($html, 'data-shape-icon'))->toBeGreaterThan(strpos($html, 'Paid'));
});

it('keeps the trailing icon when the resolved one is opted out of', function () {
    $html = Blade::render('<x-shape::badge label="Paid" tone="success" :icon="false" icon-trailing="shape-arrow-right" />');

    expect(substr_count($html, 'data-shape-icon'))->toBe(1)
        ->and(strpos($html, 'Paid'))->toBeLessThan(strpos($html, 'data-shape-icon'));
});

it('sizes both icons with the same icon-size', function () {
    $html = Blade::render('<x-shape::badge label="Paid" tone="success" icon-trailing="shape-arrow-right" icon-size="sm" />');

    expect(substr_count($html, 'size-5'))->toBe(2);
});

it('reads its colours through the same tone variables the button does', function () {
    $badge = Blade::render('<x-shape::badge label="Paid" tone="success" variant="solid" />');

    expect($badge)
        ->toContain('bg-[var(--shape-tone)]')
        ->toContain('text-[var(--shape-tone-fg)]');
});

it('keeps every variant reading the same variables rather than a colour matrix', function (string $variant, string $expected) {
    expect(Blade::render("<x-shape::badge label=\"Paid\" tone=\"success\" variant=\"{$variant}\" />"))
        ->toContain($expected)
        ->toContain('data-shape-variant="'.$variant.'"');
})->with([
    ['solid', 'bg-[var(--shape-tone)]'],
    ['subtle', 'bg-[var(--shape-tone-tint)]'],
    ['outline', 'border-[var(--shape-tone-border)]'],
]);

it('applies the requested size', function (string $size, string $expected) {
    expect(Blade::render("<x-shape::badge label=\"Paid\" size=\"{$size}\" />"))->toContain($expected);
})->with([
    ['xs', '[:where(&amp;)]:px-1.5 [:where(&amp;)]:py-0 [:where(&amp;)]:text-2xs'],
    ['sm', '[:where(&amp;)]:px-2 [:where(&amp;)]:py-0.5 [:where(&amp;)]:text-2xs'],
    ['base', '[:where(&amp;)]:px-2.5 [:where(&amp;)]:py-1 [:where(&amp;)]:text-xs'],
    ['lg', '[:where(&amp;)]:px-3 [:where(&amp;)]:py-1 [:where(&amp;)]:text-sm'],
]);

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::badge label="Paid" class="rounded-full" />'))
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('rounded-full');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::badge label="Paid" wire:key="b" title="Paid in full" />'))
        ->toContain('wire:key="b"')
        ->toContain('title="Paid in full"');
});

it('adds no vertical margin by default', function () {
    expect(Blade::render('<x-shape::badge label="Paid" size="base" />'))
        ->not->toContain('-my-1');
});

it('cancels its vertical padding with an equal negative margin when inset', function (string $size, string $expected) {
    expect(Blade::render("<x-shape::badge label='Paid' size='{$size}' inset />"))
        ->toContain($expected);
})->with([
    ['sm', '[:where(&amp;)]:-my-0.5'],
    ['base', '[:where(&amp;)]:-my-1'],
    ['lg', '[:where(&amp;)]:-my-1'],
]);

it('has no vertical padding to cancel at xs, inset or not', function () {
    expect(Blade::render('<x-shape::badge label="Paid" size="xs" inset />'))
        ->not->toContain('-my-');
});

it('is a span until a call site asks for something that can be pressed', function () {
    expect(Blade::render('<x-shape::badge label="Paid" />'))
        ->toContain('<span')
        ->not->toContain('<button');
});

it('becomes a control when asked to be one', function () {
    $html = Blade::render('<x-shape::badge label="Overdue" tone="danger" as="button" />');

    expect($html)
        ->toContain('<button type="button"')
        ->toContain('data-shape-badge')
        ->toContain('Overdue');
});

it('becomes a link on an href without being told to', function () {
    // The tab, the menu item and the avatar all resolve their element the same
    // way: middle-click and "open in new tab" work for a link and for nothing
    // pretending to be one.
    expect(Blade::render('<x-shape::badge label="Paid" href="/invoices/1042" />'))
        ->toContain('<a')
        ->toContain('href="/invoices/1042"')
        ->not->toContain('<button');
});

it('lets as beat an href, for a link that is really a control', function () {
    expect(Blade::render('<x-shape::badge label="Paid" href="/invoices/1042" as="button" />'))
        ->toContain('<button type="button"')
        ->toContain('href="/invoices/1042"');
});

it('takes a div, for a badge inside something already clickable', function () {
    expect(Blade::render('<x-shape::badge label="Paid" as="div" />'))
        ->toContain('<div')
        ->not->toContain('<button');
});

it('is the control rather than something wrapped in one', function () {
    // Same element, same classes, same bag. A wrapper would have moved every
    // attribute a call site passes off the thing being pressed.
    $html = Blade::render('<x-shape::badge label="Overdue" tone="danger" as="button" class="ring-2" wire:click="clear" />');

    expect($html)
        ->toContain('wire:click="clear"')
        ->toContain('ring-2')
        ->and(substr_count($html, '<button'))->toBe(1)
        ->and(substr_count($html, 'data-shape-badge='))->toBe(1);
});

it('keeps its icons, size and paint when it becomes a control', function () {
    $html = Blade::render('<x-shape::badge label="Overdue" tone="danger" size="lg" icon-trailing="shape-arrow-right" as="button" />');

    expect($html)
        ->toContain('data-shape-tone="danger"')
        ->toContain('[:where(&amp;)]:px-3')
        ->and(substr_count($html, 'data-shape-icon'))->toBe(2);
});

it('adds the chrome a control owes and nothing a span would wear', function () {
    $html = Blade::render('<x-shape::badge label="Draft" as="button" />');

    expect($html)
        ->toContain('focus-visible:outline-[var(--shape-ring)]')
        ->toContain('transition-colors')
        ->toContain('disabled:opacity-50')
        ->toContain('aria-disabled:pointer-events-none');
});

it('leaves a badge that is not a control with none of it', function () {
    // A span that lit up under the pointer would be promising a press that
    // isn't there.
    expect(Blade::render('<x-shape::badge label="Draft" />'))
        ->not->toContain('focus-visible:')
        ->not->toContain('hover:')
        ->not->toContain('transition-colors')
        ->not->toContain('disabled:');
});

it('repaints on hover out of the same variables the button reads', function (string $variant, string $expected) {
    // The avatar dims, because its paint is what it means. A badge's paint is
    // the button's chrome, so its hover is the button's too.
    expect(Blade::render("<x-shape::badge label=\"Paid\" tone=\"success\" variant=\"{$variant}\" as=\"button\" />"))
        ->toContain($expected);
})->with([
    ['solid', 'hover:bg-[var(--shape-tone-hover)]'],
    ['subtle', 'hover:bg-[var(--shape-tone-tint-hover)]'],
    ['outline', 'hover:bg-[var(--shape-tone-tint)]'],
]);
