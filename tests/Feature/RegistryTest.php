<?php

declare(strict_types=1);

use Onelegstudios\Shape\IconSlots;
use Onelegstudios\Shape\Registry;
use Symfony\Component\Finder\Finder;

/**
 * The manifest is hand-written, and four things depend on it being right.
 *
 * So the scan that a generator would have run at build time runs here instead,
 * every time the suite does. A component added without a registry entry, a
 * dependency that stopped being composed, a documentation page that was renamed
 * — each of them fails a test rather than quietly making `shape:eject` ship half
 * a component.
 */
function registry(): Registry
{
    return new Registry;
}

/**
 * Which component a view file belongs to, by the library's own convention: a
 * file in a directory belongs to the component the directory is named for.
 */
function componentOf(string $file): string
{
    return str_contains($file, '/')
        ? explode('/', $file)[0]
        : substr($file, 0, -strlen('.blade.php'));
}

/**
 * Every `<x-shape::...>` tag a file renders, as component names.
 *
 * Blade comments are stripped first, for the reason `FoldSafety` strips them:
 * several components name a component they deliberately do not use in order to
 * explain why they don't.
 *
 * @return list<string>
 */
function composedBy(string $path): array
{
    $source = preg_replace('/\{\{--.*?--\}\}/s', '', (string) file_get_contents($path)) ?? '';

    preg_match_all('/<x-shape::([a-z0-9.\-]+)/', $source, $matches);

    return array_values(array_unique(array_map(
        fn (string $tag): string => explode('.', $tag)[0],
        $matches[1],
    )));
}

it('accounts for every view the package ships', function () {
    $registry = registry();

    $listed = [];

    foreach ($registry->all() as $component) {
        foreach ($component['files'] as $file) {
            expect($registry->path($file))->toBeFile();

            $listed[] = $file;
        }
    }

    $found = [];

    foreach (Finder::create()->files()->in($registry->views())->name('*.blade.php') as $file) {
        $found[] = $file->getRelativePathname();
    }

    sort($listed);
    sort($found);

    expect($listed)->toBe($found, 'Every component view must appear in exactly one registry entry.');
});

it('files each view under the component its directory names', function () {
    $registry = registry();

    foreach ($registry->all() as $name => $component) {
        foreach ($component['files'] as $file) {
            expect(componentOf($file))->toBe($name);
        }
    }
});

it('lists exactly the components each entry composes', function () {
    $registry = registry();

    foreach ($registry->all() as $name => $component) {
        $composed = [];

        foreach ($component['files'] as $file) {
            $composed = [...$composed, ...composedBy($registry->path($file))];
        }

        $composed = array_values(array_unique(array_filter(
            $composed,
            fn (string $dependency): bool => $dependency !== $name,
        )));

        sort($composed);

        expect($component['requires'])->toBe($composed, "The `requires` for [{$name}] no longer match its markup.");
    }
});

it('names a documentation page that exists for every component', function () {
    foreach (registry()->all() as $name => $component) {
        expect(__DIR__.'/../../docs/'.$component['docs'])->toBeFile("[{$name}] points at a page that isn't there.");
    }
});

it('agrees with the tier the documentation index states', function () {
    $index = (string) file_get_contents(__DIR__.'/../../docs/_index.md');

    preg_match_all('/^\| \[`([a-z]+)`\]\(([^)]+)\) \| ([^|]+?) \|/m', $index, $matches, PREG_SET_ORDER);

    expect($matches)->not->toBeEmpty();

    $registry = registry();

    foreach ($matches as [, $name, $docs, $tier]) {
        expect($registry->has($name))->toBeTrue("The index lists [{$name}], which is not in the registry.");
        expect($registry->get($name)['tier'])->toBe(trim($tier));
        expect($registry->get($name)['docs'])->toBe($docs);
    }
});

it('pulls a component and everything it composes, dependencies first', function () {
    // The promise the documentation makes about ejecting a modal: the button
    // inside it and the icon inside that come too.
    expect(registry()->resolve(['modal']))
        ->toBe(['heading', 'icon', 'button', 'overlay', 'text', 'modal']);
});

it('resolves a component that needs nothing to itself alone', function () {
    expect(registry()->resolve(['icon']))->toBe(['icon']);
});

it('names a component once however many times it is required', function () {
    $resolved = registry()->resolve(['modal', 'confirm', 'alert']);

    expect(array_count_values($resolved))->each->toBe(1);
});

it('refuses to answer for a component it has never heard of', function () {
    registry()->get('accordion');
})->throws(InvalidArgumentException::class, 'Unknown Shape component [accordion].');

it('declares every slot its components draw', function () {
    // The derived-⊆-declared assertion, and the property `Registry::icons()`
    // inverted to hold. The list `shape:icon:replace` works from is declared
    // in `shape.icon_slots`, so a component that starts drawing a slot nobody
    // declared would be generated by nothing and checked by nothing — it would
    // just go on resolving to whatever the package happens to ship.
    $undeclared = array_values(array_diff(
        registry()->icons(),
        IconSlots::fromConfig()->names(),
    ));

    expect($undeclared)->toBe([], 'Every slot a component draws must appear in [icon_slots].');
});

it('ships a component for every slot it declares', function () {
    // The other direction, which is allowed to be loose: a declared slot no
    // component draws is fine — `shape-loading` is one — but a declared slot
    // with no file behind it is a `shape:icon:replace` that cannot fall back.
    $registry = registry();

    foreach (IconSlots::fromConfig()->names() as $slot) {
        expect($registry->path('icon/'.$slot.'.blade.php'))->toBeFile();
    }
});

it('ships three icons that are not slots, and nothing else', function () {
    // Two tiers. The slots are the library's vocabulary; these three exist so
    // the README and the previews render, are resolved by nothing, and are left
    // alone by `shape:icon:replace`. They are prefixed regardless, so `trash`
    // and `plus` stay free for whatever an application generates for itself.
    $examples = array_values(array_diff(
        registry()->vocabulary(),
        IconSlots::fromConfig()->names(),
    ));

    sort($examples);

    expect($examples)->toBe(['shape-arrow-right', 'shape-plus', 'shape-trash']);
});
