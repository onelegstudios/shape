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

it('draws each variant at its intended size rather than scaling one drawing', function () {
    $micro = Blade::render('<x-shape::icon.check variant="micro" />');
    $mini = Blade::render('<x-shape::icon.check variant="mini" />');
    $outline = Blade::render('<x-shape::icon.check />');

    expect($micro)->toContain('viewBox="0 0 16 16"')->toContain('size-4')
        ->and($mini)->toContain('viewBox="0 0 20 20"')->toContain('size-5')
        ->and($outline)->toContain('viewBox="0 0 24 24"')->toContain('size-6');

    // Three different drawings, not one drawing at three sizes.
    expect($micro)->not->toContain('viewBox="0 0 24 24"');
});

it('resolves an icon by name at runtime', function () {
    expect(Blade::render('<x-shape::icon name="check" variant="mini" />'))
        ->toBe(Blade::render('<x-shape::icon.check variant="mini" />'));
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

it('renders nothing rather than recursing when no name is given', function () {
    // Without a guard, `shape::icon.` resolves back to the dispatcher itself.
    expect(trim(Blade::render('<x-shape::icon />')))->toBe('');
});

it('renders nothing for a null name so optional icons compose', function () {
    expect(trim(Blade::render('<x-shape::icon :name="$icon" />', ['icon' => null])))->toBe('');
});
