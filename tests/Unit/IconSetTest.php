<?php

declare(strict_types=1);

use Onelegstudios\Shape\IconSet;

/**
 * The library's scale, which is the same one for every set below — that being
 * the whole point of it living outside them.
 *
 * @return array<string, array{class: string, prefer?: string}>
 */
function scale(): array
{
    return [
        'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
        'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
        'base' => ['class' => 'size-6', 'prefer' => 'outline'],
    ];
}

function heroicons(): IconSet
{
    return IconSet::fromArray('heroicons', [
        'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
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
    ], scale());
}

function lucide(): IconSet
{
    return IconSet::fromArray('lucide', [
        'styles' => [
            'outline' => ['base' => '{name}.svg'],
        ],
    ], scale());
}

/**
 * A set that spells things its own way, which is every set that is not
 * Heroicons.
 */
function aliased(): IconSet
{
    return IconSet::fromArray('lucide', [
        'styles' => [
            'outline' => ['base' => '{name}.svg'],
        ],
        'aliases' => [
            'x-mark' => 'x',
            'information-circle' => 'info',
            'close' => 'x',
        ],
    ], scale());
}

it('reads the styles off a set and the sizes off the library', function () {
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
        'styles' => [
            'solid' => ['base' => 'solid/{name}.svg'],
            'outline' => ['base' => 'outline/{name}.svg'],
        ],
    ], [
        'sm' => ['class' => 'size-5'],
        'base' => ['class' => 'size-6', 'prefer' => 'outline'],
    ]);

    expect($set->styleFor('sm'))->toBe('outline');
});

it('measures every set against the one scale', function () {
    // The regression this key exists to prevent. Heroicons draws at all three
    // sizes and Lucide draws at one, and both still answer `size="sm"` with a
    // 20px drawing — so a call site reads the same whichever set is behind it.
    expect(lucide()->sizes())->toBe(heroicons()->sizes())
        ->and(lucide()->defaultSize())->toBe(heroicons()->defaultSize());

    foreach (scale() as $size => $spec) {
        expect(lucide()->classFor($size))->toBe(heroicons()->classFor($size))
            ->and(lucide()->classFor($size))->toBe('[:where(&)]:'.$spec['class']);
    }
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

it('refuses a set that draws nothing', function () {
    expect(fn () => IconSet::fromArray('empty', ['styles' => ['solid' => []]], scale()))
        ->toThrow(InvalidArgumentException::class, 'at least one style');
});

it('refuses a scale that measures nothing', function () {
    expect(fn () => IconSet::fromArray('heroicons', ['styles' => ['solid' => ['base' => '{name}.svg']]], []))
        ->toThrow(InvalidArgumentException::class, 'No icon sizes are configured');
});

it('refuses a size with no class to render it at', function () {
    expect(fn () => IconSet::fromArray('bad', [
        'styles' => ['solid' => ['base' => '{name}.svg']],
    ], ['base' => []]))->toThrow(InvalidArgumentException::class, 'size without a class');
});

it('refuses a drawing at a size the library does not have', function () {
    // The likeliest typo in a hand-written set, and the one that would otherwise
    // surface as a cell that silently never resolves.
    expect(fn () => IconSet::fromArray('bad', [
        'styles' => ['solid' => ['huge' => '{name}.svg']],
    ], scale()))->toThrow(InvalidArgumentException::class, 'which is not one of the library\'s sizes');
});

it('refuses a set that keeps a scale of its own', function () {
    // What a config published before the scale was hoisted looks like. Ignoring
    // the key would leave someone declaring a scale that does nothing, which is
    // the failure this whole arrangement exists to rule out.
    expect(fn () => IconSet::fromArray('stale', [
        'sizes' => ['base' => ['class' => 'size-8']],
        'styles' => ['solid' => ['base' => '{name}.svg']],
    ], scale()))->toThrow(InvalidArgumentException::class, 'move it to [icon_sizes]');
});

it('refuses a set that is not an array at all', function () {
    expect(fn () => IconSet::fromArray('bad', 'heroicons', scale()))
        ->toThrow(InvalidArgumentException::class, 'is not an array');
});

it('ships a heroicons set and a single-style set to check the model generalises', function () {
    $sets = config('shape.icon_sets');
    $sizes = config('shape.icon_sizes');

    expect($sets)->toBeArray()
        ->and($sets)->toHaveKeys(['heroicons', 'lucide'])
        ->and($sizes)->toBe(scale());

    $shipped = IconSet::fromArray('heroicons', $sets['heroicons'], $sizes);

    expect($shipped->styleFor('sm'))->toBe('solid')
        ->and($shipped->notice)->toContain('Heroicons');

    // A library-level `prefer` a set cannot honour is ignored, not an error:
    // Lucide has one style, and the scale asking for `solid` gets `outline`.
    $single = IconSet::fromArray('lucide', $sets['lucide'], $sizes);

    expect($single->styleFor('sm'))->toBe('outline')
        ->and($single->sizes())->toBe($shipped->sizes());
});

it('translates one of Shape\'s names into the set\'s own', function () {
    expect(aliased()->sourceName('x-mark'))->toBe('x')
        ->and(aliased()->sourceName('information-circle'))->toBe('info');
});

it('leaves a name the set spells the same way alone', function () {
    // Which is most of them, and why a set with no `aliases` key needs no
    // special case anywhere: identity is the default answer.
    expect(aliased()->sourceName('check'))->toBe('check')
        ->and(lucide()->sourceName('x-mark'))->toBe('x-mark')
        ->and(heroicons()->sourceName('x-mark'))->toBe('x-mark');
});

it('reverses a file back to every name Shape would draw it under', function () {
    // `--all` walks files, and files carry the set's names. Two of Shape's names
    // may legitimately be drawn from one file, so the reverse is a list.
    expect(aliased()->canonicalNames('x'))->toBe(['x-mark', 'close'])
        ->and(aliased()->canonicalNames('info'))->toBe(['information-circle']);
});

it('reverses a file nothing aliases to under its own name', function () {
    // The ordinary case, and the one `--all` is for: a set's own `bell.svg`
    // becomes `icon.bell` here.
    expect(aliased()->canonicalNames('bell'))->toBe(['bell'])
        ->and(lucide()->canonicalNames('bell'))->toBe(['bell']);
});

it('reverses a file whose name an alias has already spoken for under nothing', function () {
    // If `x-mark` means `x.svg` in this set, then a file actually called
    // `x-mark.svg` is a different drawing with no name left to take. Generating
    // it would shadow the alias with the wrong glyph, silently.
    expect(aliased()->canonicalNames('x-mark'))->toBe([])
        ->and(aliased()->canonicalNames('information-circle'))->toBe([]);
});

it('refuses an alias that is not a name against a name', function () {
    expect(fn () => IconSet::fromArray('bad', [
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'aliases' => ['x-mark' => ['x']],
    ], scale()))->toThrow(InvalidArgumentException::class, 'not a name against a name');

    expect(fn () => IconSet::fromArray('bad', [
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'aliases' => 'x',
    ], scale()))->toThrow(InvalidArgumentException::class, 'not an array');
});

it('ships aliases that cover every name the library draws', function () {
    // The shipped Lucide entry is the worked example of a replacement set, so
    // it has to actually be one. A name Lucide spells differently and this map
    // misses is a component that keeps its Heroicon after a full replacement.
    $set = IconSet::fromArray('lucide', config('shape.icon_sets')['lucide'], config('shape.icon_sizes'));

    expect($set->sourceName('x-mark'))->toBe('x')
        ->and($set->sourceName('check-circle'))->toBe('circle-check')
        ->and($set->sourceName('x-circle'))->toBe('circle-x')
        ->and($set->sourceName('exclamation-triangle'))->toBe('triangle-alert')
        ->and($set->sourceName('information-circle'))->toBe('info')
        ->and($set->sourceName('arrow-trending-up'))->toBe('trending-up')
        ->and($set->sourceName('arrow-trending-down'))->toBe('trending-down');

    // The five Lucide happens to agree with Heroicons about.
    foreach (['check', 'minus', 'chevron-left', 'chevron-right', 'chevron-down'] as $name) {
        expect($set->sourceName($name))->toBe($name);
    }
});

it('reads the subdirectory a set is written into', function () {
    $set = IconSet::fromArray('lucide', [
        'namespace' => 'lucide',
        'styles' => ['outline' => ['base' => '{name}.svg']],
    ], scale());

    expect($set->namespace)->toBe('lucide');
});

it('ships both sets flat, so a call site has one spelling for an icon', function () {
    // Flat is the default and stays it: a namespace declared here would put the
    // set's drawings under a second spelling at every call site, and neither
    // shipped set is the supplementary one that wants that.
    foreach (['heroicons', 'lucide'] as $name) {
        $set = IconSet::fromArray($name, config('shape.icon_sets')[$name], config('shape.icon_sizes'));

        expect($set->namespace)->toBeNull();
    }
});

it('refuses a namespace that is more than one segment', function () {
    // The only value in a set definition that decides where a file is written.
    foreach (['../escape', 'lucide/nested', 'Lucide', '.', ''] as $namespace) {
        expect(fn () => IconSet::fromArray('bad', [
            'namespace' => $namespace,
            'styles' => ['outline' => ['base' => '{name}.svg']],
        ], scale()))->toThrow(InvalidArgumentException::class);
    }
});
