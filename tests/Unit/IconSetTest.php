<?php

declare(strict_types=1);

use Onelegstudios\Shape\IconSet;

function heroicons(): IconSet
{
    return IconSet::fromArray('heroicons', [
        'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
        'sizes' => [
            'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
            'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
            'base' => ['class' => 'size-6', 'prefer' => 'outline'],
        ],
        'styles' => [
            'solid' => [
                'xs' => '16/solid/{name}.svg',
                'sm' => '20/solid/{name}.svg',
                'base' => '24/solid/{name}.svg',
            ],
            'outline' => [
                'base' => '24/outline/{name}.svg',
            ],
        ],
    ]);
}

function lucide(): IconSet
{
    return IconSet::fromArray('lucide', [
        'sizes' => [
            'xs' => ['class' => 'size-4'],
            'sm' => ['class' => 'size-5'],
            'base' => ['class' => 'size-6'],
        ],
        'styles' => [
            'outline' => ['base' => '{name}.svg'],
        ],
    ]);
}

it('reads the two axes off a set definition', function () {
    expect(heroicons()->sizes())->toBe(['xs', 'sm', 'base'])
        ->and(heroicons()->styles())->toBe(['solid', 'outline'])
        ->and(heroicons()->defaultSize())->toBe('base');
});

it('lets a size choose the style it is drawn in', function () {
    // The rule this whole model exists to state once: a 1.5px stroke does not
    // read at 16px, which is why Heroicons draws no outline there and why the
    // small sizes reach for solid.
    expect(heroicons()->styleFor('xs'))->toBe('solid')
        ->and(heroicons()->styleFor('sm'))->toBe('solid')
        ->and(heroicons()->styleFor('base'))->toBe('outline');
});

it('falls back to the default size\'s style for a size that states no preference', function () {
    $set = IconSet::fromArray('partial', [
        'sizes' => [
            'sm' => ['class' => 'size-5'],
            'base' => ['class' => 'size-6', 'prefer' => 'outline'],
        ],
        'styles' => [
            'solid' => ['base' => 'solid/{name}.svg'],
            'outline' => ['base' => 'outline/{name}.svg'],
        ],
    ]);

    expect($set->styleFor('sm'))->toBe('outline');
});

it('falls back to the only style there is when nothing states a preference', function () {
    expect(lucide()->styleFor('xs'))->toBe('outline')
        ->and(lucide()->hasOneStyle())->toBeTrue()
        ->and(heroicons()->hasOneStyle())->toBeFalse();
});

it('resolves a cell the set actually draws', function () {
    expect(heroicons()->pattern('solid', 'xs'))->toBe('16/solid/{name}.svg')
        ->and(heroicons()->pattern('solid', 'base'))->toBe('24/solid/{name}.svg')
        ->and(heroicons()->pattern('outline', 'base'))->toBe('24/outline/{name}.svg');
});

it('borrows the largest drawing a style has for a cell it does not draw', function () {
    // Heroicons has no 16px or 20px outline. Both cells resolve to the 24px one
    // and are scaled down by the size class — never scaled up, which is why the
    // fallback is the largest drawing rather than the nearest.
    expect(heroicons()->pattern('outline', 'xs'))->toBe('24/outline/{name}.svg')
        ->and(heroicons()->pattern('outline', 'sm'))->toBe('24/outline/{name}.svg');
});

it('has nothing to resolve for a style it does not have', function () {
    expect(heroicons()->pattern('duotone', 'base'))->toBeNull();
});

it('wraps a size class the way the rest of the library wraps an overridable one', function () {
    expect(heroicons()->classFor('xs'))->toBe('[:where(&)]:size-4')
        ->and(heroicons()->classFor('base'))->toBe('[:where(&)]:size-6');
});

it('derives the directories to walk from the patterns themselves', function () {
    expect(heroicons()->directories())->toBe(['16/solid', '20/solid', '24/solid', '24/outline']);

    // A flat set's only pattern is `{name}.svg`, so the directory to walk is the
    // source directory itself — which is a consequence of the model rather than
    // a case written for it.
    expect(lucide()->directories())->toBe(['']);
});

it('refuses a set that declares no sizes or no styles', function () {
    expect(fn () => IconSet::fromArray('empty', ['styles' => ['solid' => []]]))
        ->toThrow(InvalidArgumentException::class, 'at least one size');
});

it('refuses a size with no class to render it at', function () {
    expect(fn () => IconSet::fromArray('bad', [
        'sizes' => ['base' => []],
        'styles' => ['solid' => ['base' => '{name}.svg']],
    ]))->toThrow(InvalidArgumentException::class, 'size without a class');
});

it('refuses a drawing at a size the set never declared', function () {
    // The likeliest typo in a hand-written set, and the one that would otherwise
    // surface as a cell that silently never resolves.
    expect(fn () => IconSet::fromArray('bad', [
        'sizes' => ['base' => ['class' => 'size-6']],
        'styles' => ['solid' => ['huge' => '{name}.svg']],
    ]))->toThrow(InvalidArgumentException::class, 'which is not one of its sizes');
});

it('refuses a set that is not an array at all', function () {
    expect(fn () => IconSet::fromArray('bad', 'heroicons'))
        ->toThrow(InvalidArgumentException::class, 'is not an array');
});

it('ships a heroicons set and a single-style set to check the model generalises', function () {
    $sets = config('shape.icon_sets');

    expect($sets)->toBeArray()
        ->and($sets)->toHaveKeys(['heroicons', 'lucide']);

    $shipped = IconSet::fromArray('heroicons', $sets['heroicons']);

    expect($shipped->styleFor('sm'))->toBe('solid')
        ->and($shipped->notice)->toContain('Heroicons');
});
