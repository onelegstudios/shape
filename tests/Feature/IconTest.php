<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders an icon directly', function () {
    $html = Blade::render('<x-shape::icon.check />');

    expect($html)
        ->toContain('<svg')
        ->toContain('data-shape-icon')
        ->toContain('aria-hidden="true"');
});

it('draws each size at its intended size rather than scaling one drawing', function () {
    $xs = Blade::render('<x-shape::icon.check size="xs" />');
    $sm = Blade::render('<x-shape::icon.check size="sm" />');
    $outline = Blade::render('<x-shape::icon.check />');

    expect($xs)->toContain('viewBox="0 0 16 16"')->toContain('size-4')
        ->and($sm)->toContain('viewBox="0 0 20 20"')->toContain('size-5')
        ->and($outline)->toContain('viewBox="0 0 24 24"')->toContain('size-6');

    // Three different drawings, not one drawing at three sizes.
    expect($xs)->not->toContain('viewBox="0 0 24 24"');
});

it('resolves an icon by name at runtime', function () {
    expect(Blade::render('<x-shape::icon name="check" size="sm" />'))
        ->toBe(Blade::render('<x-shape::icon.check size="sm" />'));
});

it('lets the size choose the style, so a call site can name only a size', function () {
    // The rule that makes the twelve places this library draws an icon portable
    // to a set that has one style: they ask for a size and nothing else. A
    // 1.5px stroke does not read at 20px, so a small size reaches for solid —
    // which is also why Heroicons draws no outline below 24.
    expect(Blade::render('<x-shape::icon.check size="sm" />'))
        ->toContain('fill="currentColor"')
        ->not->toContain('stroke="currentColor"');

    expect(Blade::render('<x-shape::icon.check />'))
        ->toContain('stroke="currentColor"');
});

it('lets the call site override the style the size would have chosen', function () {
    expect(Blade::render('<x-shape::icon.check variant="outline" size="sm" />'))
        ->toContain('stroke="currentColor"');
});

it('scales the one drawing a sparse cell has rather than leaving it empty', function () {
    // Heroicons draws no 16px outline. Asking for one is not an error and does
    // not fall back to a solid glyph: it is the 24px outline drawing, sized down
    // by the class, which is the whole of what a sparse cell means.
    expect(Blade::render('<x-shape::icon.check variant="outline" size="xs" />'))
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('stroke="currentColor"')
        ->toContain('size-4');
});

it('ignores a style on an icon whose set has only one', function () {
    // `loading` is a single drawing. A shared call site may still name a style,
    // and it has to be dropped rather than land in the attribute bag and render
    // itself onto the `<svg>`.
    expect(Blade::render('<x-shape::icon.loading variant="solid" size="sm" />'))
        ->toContain('size-5')
        ->not->toContain('variant="solid"');
});

it('lets the caller override the size', function () {
    expect(Blade::render('<x-shape::icon.check class="size-12" />'))
        ->toContain('size-12');
});

it('lets the caller expose the icon to assistive technology', function () {
    expect(Blade::render('<x-shape::icon.check aria-hidden="false" />'))
        ->toContain('aria-hidden="false"')
        ->not->toContain('aria-hidden="true"');
});

it('spins the loading icon', function () {
    expect(Blade::render('<x-shape::icon.loading />'))
        ->toContain('animate-spin');
});

it('ships the icons the button demo needs', function (string $icon) {
    expect(Blade::render("<x-shape::icon.{$icon} />"))->toContain('<svg');
})->with(['check', 'check-circle', 'x-mark', 'exclamation-triangle', 'arrow-right', 'plus', 'trash', 'chevron-down']);

it('ships a distinct glyph for every badge state', function (string $icon) {
    expect(Blade::render("<x-shape::icon.{$icon} />"))->toContain('<svg');
})->with(['check-circle', 'x-circle', 'exclamation-triangle', 'information-circle']);

it('draws the new state icons across the whole matrix', function (string $icon) {
    expect(Blade::render("<x-shape::icon.{$icon} size=\"xs\" />"))->toContain('viewBox="0 0 16 16"')
        ->and(Blade::render("<x-shape::icon.{$icon} size=\"sm\" />"))->toContain('viewBox="0 0 20 20"')
        ->and(Blade::render("<x-shape::icon.{$icon} variant=\"solid\" />"))->toContain('fill="currentColor"')
        ->and(Blade::render("<x-shape::icon.{$icon} />"))->toContain('stroke="currentColor"');
})->with(['x-circle', 'information-circle', 'chevron-left', 'chevron-right', 'arrow-trending-up', 'arrow-trending-down']);

it('ships the two directions a stat can move, as two different drawings', function () {
    // "Never rely on colour alone" only holds if up and down are actually
    // distinguishable. Rotating one arrow would be one drawing at two angles,
    // which is the thing this library's icon rule exists to refuse.
    $up = Blade::render('<x-shape::icon.arrow-trending-up size="xs" />');
    $down = Blade::render('<x-shape::icon.arrow-trending-down size="xs" />');

    expect($up)->toContain('<svg')
        ->and($down)->toContain('<svg')
        ->and($up)->not->toBe($down);
});

it('ships the chevrons the pager needs, pointing opposite ways', function () {
    $left = Blade::render('<x-shape::icon.chevron-left size="sm" />');
    $right = Blade::render('<x-shape::icon.chevron-right size="sm" />');

    expect($left)->toContain('<svg')->and($left)->not->toBe($right);
});

it('renders nothing rather than recursing when no name is given', function () {
    // Without a guard, `shape::icon.` resolves back to the dispatcher itself.
    expect(trim(Blade::render('<x-shape::icon />')))->toBe('');
});

it('renders nothing for a null name so optional icons compose', function () {
    expect(trim(Blade::render('<x-shape::icon :name="$icon" />', ['icon' => null])))->toBe('');
});
