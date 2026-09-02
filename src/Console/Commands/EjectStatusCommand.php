<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Onelegstudios\Shape\Console\Commands\Concerns\EjectsComponents;
use Onelegstudios\Shape\Registry;

/**
 * Report how ejected components differ from the package.
 */
class EjectStatusCommand extends Command
{
    use EjectsComponents;

    /**
     * The command signature.
     */
    protected $signature = 'shape:eject:status';

    /**
     * The command description.
     */
    protected $description = 'Report how ejected Shape components differ from the package.';

    /**
     * Execute the console command.
     */
    public function handle(Registry $registry, Filesystem $files): int
    {
        $destination = $this->destination();

        if ($destination === null) {
            return self::FAILURE;
        }

        return $this->status($registry, $files, $destination);
    }

    /**
     * Report how each ejected file stands against the package.
     *
     * The manifest records the checksum of the packaged file at the moment it
     * was ejected, which is the only way to tell the two interesting cases
     * apart: a component the application has since edited, and a component the
     * package has since changed underneath an untouched copy. Without that
     * record both look identical — a local file that differs from the packaged
     * one — and only the second is a reason to look at an upgrade's diff.
     */
    protected function status(Registry $registry, Filesystem $files, string $destination): int
    {
        $manifest = $this->manifest($files, $destination);

        if ($manifest === []) {
            $this->components->info("Nothing has been ejected into {$destination}.");

            return self::SUCCESS;
        }

        $stale = 0;

        foreach ($manifest as $name => $recorded) {
            $this->components->twoColumnDetail("<fg=default>{$name}</>", '');

            foreach ($recorded as $file => $sha) {
                $local = $this->checksum($destination.'/'.$file);
                $packaged = $registry->has($name) ? $this->checksum($registry->path($file)) : null;

                [$state, $counts] = $this->state($sha, $local, $packaged);

                $stale += $counts;

                $this->components->twoColumnDetail("  {$file}", $state);
            }
        }

        $this->newLine();

        if ($stale > 0) {
            $this->components->warn("{$stale} file(s) have moved on in the package since they were ejected. Compare them before re-ejecting with --force.");
        } else {
            $this->components->info('Every ejected component is level with the package.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function state(string $recorded, ?string $local, ?string $packaged): array
    {
        if ($local === null) {
            return ['<fg=gray>gone</>', 0];
        }

        if ($packaged === null) {
            return ['<fg=yellow>no longer in the package</>', 1];
        }

        return match (true) {
            $local === $recorded && $packaged === $recorded => ['<fg=green>unchanged</>', 0],
            $local !== $recorded && $packaged === $recorded => ['<fg=gray>edited here</>', 0],
            $local === $recorded => ['<fg=yellow>the package moved</>', 1],
            default => ['<fg=yellow>edited here, and the package moved</>', 1],
        };
    }
}
