<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Onelegstudios\Shape\Console\Commands\Concerns\ResolvesIconSets;
use Onelegstudios\Shape\Icons\IconSource;
use Onelegstudios\Shape\IconSet;
use RuntimeException;

/**
 * Report which generated icons have been redrawn upstream.
 */
class IconStatusCommand extends Command
{
    use ResolvesIconSets;

    /**
     * The command signature.
     */
    protected $signature = 'shape:icon:status
        {--set= : The icon set to read, overriding the configured one}
        {--from= : A directory to read SVGs from, instead of fetching the set}
        {--ref= : The branch, tag or commit to fetch, overriding the set\'s own}
        {--offline : Work from what has already been fetched, and fail rather than fetch}
        {--to= : Where the components were written}
        {--namespace= : Override the set\'s own subdirectory; empty reads flat}';

    /**
     * The command description.
     */
    protected $description = 'Report which generated Shape icons have been redrawn upstream.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        try {
            $namespace = $this->namespace($this->set());
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        return $this->status($files, $this->destination($namespace));
    }

    /**
     * Report which generated icons have been redrawn since they were generated.
     *
     * The lockfile records what each icon was drawn from, which is the only way
     * to tell the interesting case apart from the ordinary one. A component that
     * differs from the current upstream drawing might have been hand-edited, or
     * upstream might have moved underneath it; without the record both look the
     * same, and only the second is a reason to regenerate.
     *
     * This is the same trade `shape:eject --status` makes against the package,
     * one layer further out — there, the package is upstream; here, the icon set
     * is.
     *
     * Every directory that holds a lockfile is reported, not only the one this
     * run resolved to. A set in a subdirectory keeps its own lock beside its own
     * components, and a status that read one lockfile would answer "nothing has
     * been generated" for a set sitting right there — the one answer worse than
     * no answer, because it reads like a clean bill of health.
     */
    protected function status(Filesystem $files, string $to): int
    {
        $directories = $this->recorded($files, $to);
        $locks = [];

        foreach ($directories as $directory) {
            $lock = $this->lock($files, $directory);

            if ($lock !== []) {
                $locks[$directory] = $lock;
            }
        }

        if ($locks === []) {
            $this->components->info("No icons have been generated into {$to}.");

            return self::SUCCESS;
        }

        $stale = 0;

        // Named whenever a reader could be in any doubt about which directory a
        // row belongs to — several of them, or one that is not the destination
        // this run resolved to. A run that reports on exactly where it was
        // pointed reads as it always did.
        $named = count($locks) > 1 || array_key_first($locks) !== $to;

        foreach ($locks as $directory => $lock) {
            if ($named) {
                $this->newLine();
                $this->components->info("In {$directory}.");
            }

            $stale += $this->report($files, (string) $directory, $lock);
        }

        $this->newLine();

        if ($stale > 0) {
            $this->components->warn("{$stale} icon(s) have been redrawn upstream. Regenerate them with --force.");
        } else {
            $this->components->info('Every generated icon is level with the set it came from.');
        }

        return self::SUCCESS;
    }

    /**
     * Report on one directory's worth of generated icons, and count the stale.
     *
     * @param  array<string, array{repo?: string, ref?: string, commit?: string, npm?: string, version?: string, icons: array<string, string>}>  $lock
     */
    protected function report(Filesystem $files, string $to, array $lock): int
    {
        $stale = 0;

        foreach ($lock as $name => $record) {
            $this->components->twoColumnDetail("<fg=default>{$name}</>", $this->pinned($record));

            // Icons generated from a directory record no upstream, and this
            // command does not know which directory it was. Reaching for the
            // set's repository instead would compare them against drawings they
            // never came from, over a network nobody asked it to use.
            if (! isset($record['repo']) && ! isset($record['npm']) && $this->fetched()) {
                $this->components->twoColumnDetail('  generated from a directory', '<fg=gray>pass --from to check</>');

                continue;
            }

            try {
                $set = $this->set($name);
                $source = $this->source($set, $files);
            } catch (InvalidArgumentException|RuntimeException $e) {
                $this->components->twoColumnDetail('  '.$e->getMessage(), '<fg=yellow>not checked</>');

                continue;
            }

            foreach ($record['icons'] as $icon => $digest) {
                [$state, $counts] = $this->state($set, $source, $files, $to, $icon, $digest);

                $stale += $counts;

                $this->components->twoColumnDetail("  {$icon}", $state);
            }
        }

        return $stale;
    }

    /**
     * How one recorded icon stands against the set as it is now.
     *
     * @return array{0: string, 1: int}
     */
    protected function state(IconSet $set, IconSource $source, Filesystem $files, string $to, string $icon, string $digest): array
    {
        if (! $files->exists($to.'/'.$icon.'.blade.php')) {
            return ['<fg=gray>gone</>', 0];
        }

        $cells = $this->matrix($set, $source, $icon);

        if ($cells === []) {
            return ['<fg=yellow>no longer in the set</>', 1];
        }

        return $this->digest($source, $cells) === $digest
            ? ['<fg=green>unchanged</>', 0]
            : ['<fg=yellow>redrawn upstream</>', 1];
    }

    /**
     * What was recorded for one set, as one line.
     *
     * @param  array{repo?: string, ref?: string, commit?: string, npm?: string, version?: string, icons: array<string, string>}  $record
     */
    protected function pinned(array $record): string
    {
        $package = $record['npm'] ?? null;

        if ($package !== null) {
            return '<fg=gray>'.$package.'@'.($record['version'] ?? '?').'</>';
        }

        $repo = $record['repo'] ?? null;
        $commit = $record['commit'] ?? null;

        if ($repo === null) {
            return '<fg=gray>a directory</>';
        }

        return '<fg=gray>'.$repo.'@'.($commit === null ? ($record['ref'] ?? '?') : substr($commit, 0, 12)).'</>';
    }

    /**
     * Every directory a generated set could be recorded in.
     *
     * This command is asked "what is stale", not "what is stale in this one
     * directory", and answering only for the destination this run resolved to
     * would have it report nothing at all for a set that lives in a
     * subdirectory — a silence indistinguishable from a clean bill of health.
     * Namespaces come out of the config rather than off the command line now,
     * so the set of places to look is knowable without scanning for it.
     *
     * @return list<string>
     */
    protected function recorded(Filesystem $files, string $to): array
    {
        $directories = [$to];

        if (is_string($this->option('namespace'))) {
            return $directories;
        }

        $sets = config('shape.icon_sets');

        foreach (is_array($sets) ? $sets : [] as $name => $definition) {
            try {
                $namespace = $this->subdirectory($this->set((string) $name));
            } catch (InvalidArgumentException) {
                continue;
            }

            $directory = $this->destination($namespace);

            if ($namespace !== null && ! in_array($directory, $directories, true) && $files->exists($directory.'/'.$this->lockName())) {
                $directories[] = $directory;
            }
        }

        return $directories;
    }
}
