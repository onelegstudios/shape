<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Onelegstudios\Shape\Console\Commands\Concerns\ClearsCompiledViews;
use Onelegstudios\Shape\Registry;

/**
 * Copy components out of the package and into the application.
 *
 * The third and last step of Shape's customization escalation: tokens, then
 * utilities, then this. Everything before it is a way of influencing a component
 * from outside; this hands the file over. Once ejected, the component is the
 * application's code, resolved ahead of the packaged one by the path the service
 * provider registers first.
 *
 * `vendor:publish --tag="laravel-shape-components"` does the same thing for the
 * whole library at once. This exists because that is rarely what anybody wants:
 * a modal alone is a file that composes a button, an icon, a heading and a
 * close — eject the modal without them and half of it still belongs to the
 * package, which is the worst of both arrangements.
 */
class EjectCommand extends Command
{
    use ClearsCompiledViews;

    /**
     * The command signature.
     */
    protected $signature = 'shape:eject
        {components?* : The components to eject}
        {--all : Eject every component}
        {--bare : Eject only the named components, without what they compose}
        {--force : Overwrite components that have already been ejected}
        {--status : Report how ejected components differ from the package}';

    /**
     * The command description.
     */
    protected $description = 'Copy Shape components into the application, with everything they compose.';

    /**
     * Execute the console command.
     */
    public function handle(Registry $registry, Filesystem $files): int
    {
        $destination = $this->destination();

        if ($destination === null) {
            $this->components->error('`shape.components_path` is not a path this command can write to.');

            return self::FAILURE;
        }

        if ($this->option('status')) {
            return $this->status($registry, $files, $destination);
        }

        $requested = $this->requested($registry);

        if ($requested === null) {
            return self::FAILURE;
        }

        $resolved = $this->option('bare') ? $requested : $registry->resolve($requested);

        return $this->eject($registry, $files, $destination, $requested, $resolved);
    }

    /**
     * The components named on the command line, or every component with `--all`.
     *
     * @return list<string>|null
     */
    protected function requested(Registry $registry): ?array
    {
        if ($this->option('all')) {
            return $registry->names();
        }

        /** @var list<string> $components */
        $components = (array) $this->argument('components');

        if ($components === []) {
            $this->components->error('Name at least one component, or pass --all.');
            $this->line('  '.implode(', ', $registry->names()));

            return null;
        }

        $unknown = array_values(array_filter($components, fn (string $name): bool => ! $registry->has($name)));

        if ($unknown !== []) {
            $this->components->error('No such component: '.implode(', ', $unknown).'.');
            $this->line('  '.implode(', ', $registry->names()));

            return null;
        }

        return $components;
    }

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
     * Where ejected components live.
     */
    protected function destination(): ?string
    {
        $path = config('shape.components_path');

        return is_string($path) && $path !== '' ? rtrim($path, '/') : null;
    }
}
