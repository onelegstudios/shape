<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use InvalidArgumentException;
use RuntimeException;

/**
 * The component manifest: what each component is made of, and what it needs.
 *
 * Four things in this package need to agree about what a component is, and this
 * is the one place that answers: `shape:eject` walks `requires` so that ejecting
 * a modal takes the button and the icon inside it, `shape:doctor` walks `files`
 * to know what to lint, the suite asserts every entry has a documentation page,
 * and the bundled Boost skill is written from the tiers below.
 *
 * The manifest is hand-maintained JSON rather than something generated at boot.
 * Scanning seventy views for their `<x-shape::...>` tags on every request to
 * discover a graph that only changes when a component does would be the wrong
 * trade — so the scan lives in the test suite instead, where it fails the build
 * if the JSON and the markup ever disagree.
 */
final class Registry
{
    /**
     * @var array<string, array{files: list<string>, requires: list<string>, docs: string, tier: string}>|null
     */
    private ?array $components = null;

    public function __construct(
        private readonly string $manifest = __DIR__.'/../resources/registry.json',
        private readonly string $views = __DIR__.'/../resources/views/shape',
    ) {}

    /**
     * Every component, keyed by name, in the order the manifest lists them.
     *
     * @return array<string, array{files: list<string>, requires: list<string>, docs: string, tier: string}>
     */
    public function all(): array
    {
        return $this->components ??= $this->read();
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->all());
    }

    public function has(string $name): bool
    {
        return isset($this->all()[$name]);
    }

    /**
     * @return array{files: list<string>, requires: list<string>, docs: string, tier: string}
     */
    public function get(string $name): array
    {
        if (! $this->has($name)) {
            throw new InvalidArgumentException("Unknown Shape component [{$name}].");
        }

        return $this->all()[$name];
    }

    /**
     * Expand a list of components into everything that has to travel with them.
     *
     * Dependencies come out ahead of the components that asked for them, and
     * each appears once however many times it was required. Depth-first is what
     * produces that order, and the visited set is what keeps a cycle — two
     * components that compose each other — from recursing forever. Nothing in
     * the library does that today; the guard costs one array.
     *
     * @param  list<string>  $names
     * @return list<string>
     */
    public function resolve(array $names): array
    {
        $resolved = [];

        foreach ($names as $name) {
            $this->resolveInto($name, $resolved);
        }

        return array_keys($resolved);
    }

    /**
     * Which component a view file belongs to, or null if it belongs to none.
     *
     * A file in a directory belongs to the component the directory is named
     * for — `table/cell.blade.php` is part of `table` — and a file at the top
     * level is its own component.
     */
    public function componentFor(string $file): ?string
    {
        foreach ($this->all() as $name => $component) {
            if (in_array($file, $component['files'], true)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * The view files a component is made of, relative to the views directory.
     *
     * @return list<string>
     */
    public function files(string $name): array
    {
        return $this->get($name)['files'];
    }

    /**
     * The absolute path a packaged view file lives at.
     */
    public function path(string $file): string
    {
        return $this->views.'/'.$file;
    }

    /**
     * The directory the packaged views live in.
     */
    public function views(): string
    {
        return $this->views;
    }

    /**
     * @param  array<string, true>  $resolved
     * @param  array<string, true>  $visiting
     */
    private function resolveInto(string $name, array &$resolved, array $visiting = []): void
    {
        if (isset($resolved[$name]) || isset($visiting[$name])) {
            return;
        }

        // Passed by value, so it records the path taken to get here rather than
        // everything seen so far. That is what makes it a cycle guard without it
        // also swallowing a component that two others legitimately require.
        $visiting[$name] = true;

        foreach ($this->get($name)['requires'] as $dependency) {
            $this->resolveInto($dependency, $resolved, $visiting);
        }

        $resolved[$name] = true;
    }

    /**
     * @return array<string, array{files: list<string>, requires: list<string>, docs: string, tier: string}>
     */
    private function read(): array
    {
        $contents = @file_get_contents($this->manifest);

        if ($contents === false) {
            throw new RuntimeException("Shape's component manifest is missing at [{$this->manifest}].");
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Shape's component manifest at [{$this->manifest}] is not valid JSON.");
        }

        /** @var array<string, array{files: list<string>, requires: list<string>, docs: string, tier: string}> $decoded */
        return $decoded;
    }
}
