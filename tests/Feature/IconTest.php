<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Onelegstudios\Shape\IconSlots;

it('renders an icon directly', function () {
    $html = Blade::render('<x-shape::icon.shape-checked />');

    expect($html)
        ->toContain('<svg')
        ->toContain('data-shape-icon')
        ->toContain('aria-hidden="true"');
});

it('draws each size at its intended size rather than scaling one drawing', function () {
    $xs = Blade::render('<x-shape::icon.shape-checked size="xs" />');
    $sm = Blade::render('<x-shape::icon.shape-checked size="sm" />');
    $outline = Blade::render('<x-shape::icon.shape-checked />');

    expect($xs)->toContain('viewBox="0 0 16 16"')->toContain('size-4')
        ->and($sm)->toContain('viewBox="0 0 20 20"')->toContain('size-5')
        ->and($outline)->toContain('viewBox="0 0 24 24"')->toContain('size-6');

    // Three different drawings, not one drawing at three sizes.
    expect($xs)->not->toContain('viewBox="0 0 24 24"');
});

it('resolves an icon by name at runtime', function () {
    expect(Blade::render('<x-shape::icon name="shape-checked" size="sm" />'))
        ->toBe(Blade::render('<x-shape::icon.shape-checked size="sm" />'));
});

it('lets the size choose the style, so a call site can name only a size', function () {
    // The rule that makes every place this library draws an icon portable to a
    // set that has one style: they ask for a size and nothing else. A
    // 1.5px stroke does not read at 20px, so a small size reaches for solid —
    // which is also why Heroicons draws no outline below 24.
    expect(Blade::render('<x-shape::icon.shape-checked size="sm" />'))
        ->toContain('fill="currentColor"')
        ->not->toContain('stroke="currentColor"');

    expect(Blade::render('<x-shape::icon.shape-checked />'))
        ->toContain('stroke="currentColor"');
});

it('lets the call site override the style the size would have chosen', function () {
    expect(Blade::render('<x-shape::icon.shape-checked variant="outline" size="sm" />'))
        ->toContain('stroke="currentColor"');
});

it('scales the one drawing a sparse cell has rather than leaving it empty', function () {
    // Heroicons draws no 16px outline. Asking for one is not an error and does
    // not fall back to a solid glyph: it is the 24px outline drawing, sized down
    // by the class, which is the whole of what a sparse cell means.
    expect(Blade::render('<x-shape::icon.shape-checked variant="outline" size="xs" />'))
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('stroke="currentColor"')
        ->toContain('size-4');
});

it('ignores a style on an icon whose set has only one', function () {
    // The packaged spinner is a single drawing. A shared call site may still
    // name a style, and it has to be dropped rather than land in the attribute
    // bag and render itself onto the `<svg>`.
    expect(Blade::render('<x-shape::icon.shape-loading variant="solid" size="sm" />'))
        ->toContain('size-5')
        ->not->toContain('variant="solid"');
});

it('lets the caller override the size', function () {
    expect(Blade::render('<x-shape::icon.shape-checked class="size-12" />'))
        ->toContain('size-12');
});

it('lets the caller expose the icon to assistive technology', function () {
    expect(Blade::render('<x-shape::icon.shape-checked aria-hidden="false" />'))
        ->toContain('aria-hidden="false"')
        ->not->toContain('aria-hidden="true"');
});

it('spins the loading icon', function () {
    expect(Blade::render('<x-shape::icon.shape-loading />'))
        ->toContain('animate-spin');
});

it('renders every slot it declares', function () {
    // The declared list is what `shape:icon:replace` generates and what
    // `shape:doctor` checks, so a slot the package itself cannot render is a
    // hole in both. One test rather than a dataset: a dataset closure is
    // resolved before the application boots, and the list lives in its config.
    foreach (IconSlots::fromConfig()->names() as $slot) {
        expect(Blade::render("<x-shape::icon.{$slot} />"))->toContain('<svg');
    }
});

it('ships the three its own examples use', function (string $icon) {
    // Resolved by no component: the README and the previews render these, which
    // is the only reason they are files. Not slots — `shape:icon:replace` leaves
    // them alone — and not offered as a catalogue either.
    expect(Blade::render("<x-shape::icon.{$icon} />"))->toContain('<svg');
})->with(['shape-arrow-right', 'shape-plus', 'shape-trash']);

it('ships a distinct glyph for every badge state', function (string $icon) {
    expect(Blade::render("<x-shape::icon.{$icon} />"))->toContain('<svg');
})->with(['shape-success', 'shape-danger', 'shape-warning', 'shape-info']);

it('draws the state slots across the whole matrix', function (string $icon) {
    expect(Blade::render("<x-shape::icon.{$icon} size=\"xs\" />"))->toContain('viewBox="0 0 16 16"')
        ->and(Blade::render("<x-shape::icon.{$icon} size=\"sm\" />"))->toContain('viewBox="0 0 20 20"')
        ->and(Blade::render("<x-shape::icon.{$icon} variant=\"solid\" />"))->toContain('fill="currentColor"')
        ->and(Blade::render("<x-shape::icon.{$icon} />"))->toContain('stroke="currentColor"');
})->with(['shape-danger', 'shape-info', 'shape-prev', 'shape-next', 'shape-trend-up', 'shape-trend-down']);

it('ships the two directions a stat can move, as two different drawings', function () {
    // "Never rely on colour alone" only holds if up and down are actually
    // distinguishable. Rotating one arrow would be one drawing at two angles,
    // which is the thing this library's icon rule exists to refuse.
    $up = Blade::render('<x-shape::icon.shape-trend-up size="xs" />');
    $down = Blade::render('<x-shape::icon.shape-trend-down size="xs" />');

    expect($up)->toContain('<svg')
        ->and($down)->toContain('<svg')
        ->and($up)->not->toBe($down);
});

it('ships the two directions the pager goes, pointing opposite ways', function () {
    $left = Blade::render('<x-shape::icon.shape-prev size="sm" />');
    $right = Blade::render('<x-shape::icon.shape-next size="sm" />');

    expect($left)->toContain('<svg')->and($left)->not->toBe($right);
});

it('renders nothing rather than recursing when no name is given', function () {
    // Without a guard, `shape::icon.` resolves back to the dispatcher itself.
    expect(trim(Blade::render('<x-shape::icon />')))->toBe('');
});

it('renders nothing for a null name so optional icons compose', function () {
    expect(trim(Blade::render('<x-shape::icon :name="$icon" />', ['icon' => null])))->toBe('');
});

it('falls back to the packaged spinner for a slot the ejected set left alone', function () {
    // The state a Heroicons user is in permanently: `arrow-path` is a circular
    // arrow rather than a loader, so the set fills every other slot and this
    // one resolves to the drawing Shape ships — which still has to spin.
    expect(Blade::render('<x-shape::icon.shape-loading />'))
        ->toContain('animate-spin')
        ->toContain('<circle');
});

it('leaves every bare name free for the icons an application generates', function () {
    // What the prefix is for. `<x-shape::button icon="trash">` used to resolve,
    // draw, and stay a Heroicon in an application that had replaced every other
    // icon — `trash` was not a slot, so nothing regenerated it and
    // `shape:doctor` never counted it. Now nothing answers to a bare name until
    // the application generates one from its own set, and reaching for one it
    // has not is an error rather than a drawing nobody chose.
    foreach (['arrow-right', 'plus', 'trash', 'check', 'x-mark'] as $bare) {
        expect(fn () => Blade::render("<x-shape::icon.{$bare} />"))
            ->toThrow(InvalidArgumentException::class);
    }
});
