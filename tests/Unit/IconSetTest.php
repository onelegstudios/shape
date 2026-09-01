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
 * A set that puts the style in the filename as well as the directory, which is
 * what Phosphor does and what a pattern — rather than a directory — is for.
 */
function suffixed(): IconSet
{
    return IconSet::fromArray('phosphor', [
        'styles' => [
            'outline' => ['base' => 'regular/{name}.svg'],
            'solid' => ['base' => 'fill/{name}-fill.svg'],
        ],
    ], scale());
}

/**
 * A set that answers Shape's slots with its own spellings, which is every set —
 * Heroicons included, since it is asked the same question as the rest.
 */
function slotted(): IconSet
{
    return IconSet::fromArray('lucide', [
        'styles' => [
            'outline' => ['base' => '{name}.svg'],
        ],
        'slots' => [
            'shape-close' => 'x',
            'shape-info' => 'info',
            'shape-indeterminate' => 'minus',
            'shape-trend-flat' => 'minus',
            'shape-loading' => null,
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

it('reads a listed file back through the pattern that named it', function () {
    // The other half of `directories()`: that says where to look, and this says
    // what a file found there is called. For Phosphor those are two questions —
    // `fill/heart-fill.svg` is the fill drawing of `heart`, and writing it under
    // `heart-fill` would produce a component whose every other cell resolves to
    // `heart-fill-fill.svg`.
    expect(suffixed()->nameFor('fill', 'heart-fill'))->toBe('heart')
        ->and(suffixed()->nameFor('regular', 'heart'))->toBe('heart')
        ->and(heroicons()->nameFor('24/outline', 'bell'))->toBe('bell')
        ->and(lucide()->nameFor('', 'bell'))->toBe('bell');
});

it('has no name for a file the set does not account for', function () {
    // A drawing in the fill directory that is not a fill drawing is not this
    // set's to write, and neither is anything in a directory it never named.
    expect(suffixed()->nameFor('fill', 'heart'))->toBeNull()
        ->and(suffixed()->nameFor('thin', 'heart-thin'))->toBeNull();

    // Nor is a file matched by a pattern that names nothing. A pattern with no
    // `{name}` in it resolves to the same drawing whatever is asked for, which
    // is a typo in a hand-written set rather than an icon called `logo`.
    $fixed = IconSet::fromArray('fixed', [
        'styles' => ['outline' => ['base' => 'logo.svg']],
    ], scale());

    expect($fixed->nameFor('', 'logo'))->toBeNull();
});

it('reads a suffixed file as the drawing it is rather than as a name', function () {
    // Both patterns match, because a flat set that draws its solid style by
    // suffix looks at `heart-fill` twice: `{name}` reads it as an icon called
    // `heart-fill`, and `{name}-fill` reads it as the fill drawing of `heart`.
    // The shortest answer is the second, which is the one that is true.
    $flat = IconSet::fromArray('bootstrap', [
        'styles' => [
            'outline' => ['base' => '{name}.svg'],
            'solid' => ['base' => '{name}-fill.svg'],
        ],
    ], scale());

    expect($flat->nameFor('', 'heart-fill'))->toBe('heart')
        ->and($flat->nameFor('', 'heart'))->toBe('heart');
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

it('resolves a slot to the drawing the set fills it with', function () {
    expect(slotted()->sourceName('shape-close'))->toBe('x')
        ->and(slotted()->sourceName('shape-info'))->toBe('info');
});

it('lets two slots be filled by one drawing', function () {
    // The checkbox's dash and a flat trend render the same glyph today and are
    // free to diverge — a set may well want `square-minus` for one and `equal`
    // for the other. One duplicated file is the price of that being possible.
    expect(slotted()->sourceName('shape-indeterminate'))->toBe('minus')
        ->and(slotted()->sourceName('shape-trend-flat'))->toBe('minus');
});

it('answers nothing for a slot the set says it has no drawing for', function () {
    // `null` is an answer. A slot left out of the map has not been answered at
    // all, and only the first of those is something a run can report as a
    // decision rather than as an oversight.
    expect(slotted()->sourceName('shape-loading'))->toBeNull()
        ->and(slotted()->declares('shape-loading'))->toBeTrue()
        ->and(slotted()->declares('shape-warning'))->toBeFalse();
});

it('leaves anything outside the slots as itself', function () {
    // Which covers every icon a user generates — `bell`, `plus`, a set's own
    // file under `--all` — and is why a set that fills no slots needs no
    // special case anywhere.
    expect(slotted()->sourceName('bell'))->toBe('bell')
        ->and(lucide()->sourceName('bell'))->toBe('bell')
        ->and(heroicons()->sourceName('trash'))->toBe('trash');
});

it('refuses a slot that is not a name against a name', function () {
    expect(fn () => IconSet::fromArray('bad', [
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'slots' => ['shape-close' => ['x']],
    ], scale()))->toThrow(InvalidArgumentException::class, 'not a name against a name');

    expect(fn () => IconSet::fromArray('bad', [
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'slots' => 'x',
    ], scale()))->toThrow(InvalidArgumentException::class, 'not an array');
});

it('refuses a set that still speaks in aliases', function () {
    // What a config published before slots existed looks like. Ignoring the key
    // would leave the set covering no slot at all — a `--replace` that writes
    // nothing and reports success, which is worse than saying so.
    expect(fn () => IconSet::fromArray('stale', [
        'styles' => ['outline' => ['base' => '{name}.svg']],
        'aliases' => ['x-mark' => 'x'],
    ], scale()))->toThrow(InvalidArgumentException::class, 'rewrite it as [slots]');
});

it('ships two sets that between them fill every declared slot', function () {
    // Both shipped sets are worked examples and have to actually be ones. A slot
    // either misses is a component that keeps its Heroicon after a full
    // replacement — except `shape-loading`, which is packaged and where `null`
    // is the right answer rather than a gap.
    $slots = array_keys(config('shape.icon_slots'));

    foreach (['heroicons', 'lucide'] as $name) {
        $set = IconSet::fromArray($name, config('shape.icon_sets')[$name], config('shape.icon_sizes'));

        foreach ($slots as $slot) {
            expect($set->declares($slot))->toBeTrue("[{$name}] says nothing about [{$slot}]");
        }
    }

    $lucide = IconSet::fromArray('lucide', config('shape.icon_sets')['lucide'], config('shape.icon_sizes'));

    expect($lucide->sourceName('shape-close'))->toBe('x')
        ->and($lucide->sourceName('shape-success'))->toBe('circle-check')
        ->and($lucide->sourceName('shape-danger'))->toBe('circle-x')
        ->and($lucide->sourceName('shape-warning'))->toBe('triangle-alert')
        ->and($lucide->sourceName('shape-info'))->toBe('info')
        ->and($lucide->sourceName('shape-trend-up'))->toBe('trending-up')
        ->and($lucide->sourceName('shape-trend-down'))->toBe('trending-down')
        ->and($lucide->sourceName('shape-loading'))->toBe('loader-circle');

    // Heroicons has nothing that reads as a loader, and says so rather than
    // shadowing the packaged spinner with a circular arrow.
    $heroicons = IconSet::fromArray('heroicons', config('shape.icon_sets')['heroicons'], config('shape.icon_sizes'));

    expect($heroicons->sourceName('shape-loading'))->toBeNull()
        ->and($heroicons->sourceName('shape-close'))->toBe('x-mark');
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
