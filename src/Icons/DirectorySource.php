<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Icons;

use Illuminate\Filesystem\Filesystem;

/**
 * A set that is already on disk — `--from`, which is what this command has
 * always done.
 *
 * It stays the way to generate from a local checkout, from a directory of
 * drawings somebody made themselves, or from a set with no upstream to fetch.
 * Nothing about it is a fallback: a designer handing over a folder of SVGs is
 * the case this library was built around, and it should not have to be a
 * repository first.
 *
 * It is also what the suite runs against. Every generator test points at
 * `tests/fixtures`, so the fast tests stay fast and none of them can reach the
 * network even if the wiring around them changes.
 */
final class DirectorySource implements IconSource
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $root,
    ) {}

    public function has(string $path): bool
    {
        return $this->files->exists($this->path($path));
    }

    public function get(string $path): string
    {
        return $this->files->get($this->path($path));
    }

    /**
     * @return list<string>
     */
    public function names(string $directory): array
    {
        $path = $this->path($directory);

        if (! $this->files->isDirectory($path)) {
            return [];
        }

        $names = [];

        foreach ($this->files->files($path) as $file) {
            if ($file->getExtension() === 'svg') {
                $names[] = $file->getFilenameWithoutExtension();
            }
        }

        return $names;
    }

    /**
     * A directory is not a revision of anything.
     */
    public function revision(): ?string
    {
        return null;
    }

    /**
     * A path inside the set, resolved against the root.
     *
     * The `rtrim` is what lets `names('')` mean the root itself, which is how a
     * flat set — whose only pattern is `{name}.svg` — asks about its own
     * directory without needing a case of its own.
     */
    private function path(string $path): string
    {
        return rtrim($this->root.'/'.$path, '/');
    }
}
