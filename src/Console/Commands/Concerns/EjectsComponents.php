<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;

/**
 * Where ejected components live, and what was recorded there last time.
 *
 * The two questions every eject command has to answer before it can do its
 * work — the one that only reports asks them so it knows what it is reporting
 * on — so they live here rather than in any one of the three.
 */
trait EjectsComponents
{
    /**
     * The checksum of a file, or null when there is no file to read.
     */
    protected function checksum(string $path): ?string
    {
        $checksum = is_file($path) ? sha1_file($path) : false;

        return $checksum === false ? null : $checksum;
    }

    /**
     * The record written at eject time, if there is one.
     *
     * @return array<string, array<string, string>>
     */
    protected function manifest(Filesystem $files, string $destination): array
    {
        $path = $destination.'/'.$this->manifestName();

        if (! $files->exists($path)) {
            return [];
        }

        $decoded = json_decode($files->get($path), true);

        /** @var array<string, array<string, string>> */
        return is_array($decoded) ? $decoded : [];
    }

    protected function manifestName(): string
    {
        return 'shape-eject.json';
    }

    /**
     * Where ejected components live, or null — having said so — when nothing
     * in the config names a directory to write them into.
     *
     * Reported here rather than at the three call sites, because all three
     * commands ask this first and none of them can do anything without it.
     */
    protected function destination(): ?string
    {
        $path = config('shape.components_path');

        if (! is_string($path) || $path === '') {
            $this->components->error('`shape.components_path` is not a path this command can write to.');

            return null;
        }

        return rtrim($path, '/');
    }
}
