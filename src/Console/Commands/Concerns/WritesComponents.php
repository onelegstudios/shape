<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;
use Onelegstudios\Shape\Registry;

/**
 * Copy a resolved list of components into the application, and record what the
 * package held at the moment it happened.
 *
 * Everything from the list onwards is the same work whichever command asked for
 * it. What differs is the list, and that is the one thing this asks the command
 * for.
 */
trait WritesComponents
{
    /**
     * Copy the resolved components into the application.
     *
     * @param  list<string>  $requested
     * @param  list<string>  $resolved
     */
    protected function eject(Registry $registry, Filesystem $files, string $destination, array $requested, array $resolved): int
    {
        $manifest = $this->manifest($files, $destination);
        $ejected = 0;
        $skipped = 0;

        foreach ($resolved as $name) {
            $this->components->twoColumnDetail(
                "<fg=default>{$name}</>",
                $this->reason($registry, $name, $requested, $resolved),
            );

            foreach ($registry->files($name) as $file) {
                $source = $registry->path($file);
                $target = $destination.'/'.$file;

                if ($files->exists($target) && ! $this->option('force')) {
                    $this->components->twoColumnDetail("  {$file}", '<fg=yellow>exists, kept</>');

                    $skipped++;

                    continue;
                }

                $files->ensureDirectoryExists(dirname($target));
                $files->copy($source, $target);

                $manifest[$name][$file] = (string) $this->checksum($source);

                $ejected++;

                $this->components->twoColumnDetail("  {$file}", '<fg=green>ejected</>');
            }
        }

        ksort($manifest);

        $files->put(
            $destination.'/'.$this->manifestName(),
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n",
        );

        $this->newLine();
        $this->components->info("Ejected into {$destination}.");

        if ($skipped > 0) {
            $this->components->warn("{$skipped} file(s) were already there and were kept. Pass --force to overwrite them.");
        }

        if ($ejected > 0) {
            $this->clearCompiledViews();
        }

        return self::SUCCESS;
    }

    /**
     * Why a component is in the list — asked for, or composed by one that was.
     *
     * @param  list<string>  $requested
     * @param  list<string>  $resolved
     */
    protected function reason(Registry $registry, string $name, array $requested, array $resolved): string
    {
        if (in_array($name, $requested, true)) {
            return '<fg=gray>requested</>';
        }

        foreach ($resolved as $candidate) {
            if (in_array($name, $registry->get($candidate)['requires'], true)) {
                return "<fg=gray>required by {$candidate}</>";
            }
        }

        return '';
    }
}
