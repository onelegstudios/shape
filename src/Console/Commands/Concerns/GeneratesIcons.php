<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;
use Onelegstudios\Shape\Icons\Component;
use Onelegstudios\Shape\Icons\IconSource;
use Onelegstudios\Shape\IconSet;
use Onelegstudios\Shape\IconSlots;

/**
 * Turn a list of names into components, and record what each was drawn from.
 *
 * Everything from the list onwards is the same work whichever command asked for
 * it: read the cells, write the component, report the row, pin the lockfile.
 * What differs is the list, and that is the one thing this asks the command for.
 *
 * Assembling the Blade is `Icons\Component`, which reads no option and prints
 * nothing. What is left here is the part that does both.
 */
trait GeneratesIcons
{
    /**
     * The names to write components under.
     *
     * The question each of the three writing commands exists to answer
     * differently, which is why it is asked rather than switched on.
     *
     * @return list<string>
     */
    abstract protected function names(IconSet $set, IconSlots $slots, IconSource $source): array;

    /**
     * Write one component per name, and record what each was drawn from.
     */
    protected function generate(IconSet $set, IconSlots $slots, IconSource $source, Filesystem $files, string $to): int
    {
        $names = $this->names($set, $slots, $source);

        if ($names === []) {
            $this->components->error('Name at least one icon, or run shape:icon:all.');

            return self::FAILURE;
        }

        $lock = $this->lock($files, $to);
        $component = new Component($set, $slots, $source);
        $written = 0;
        $unfilled = [];

        foreach ($names as $name) {
            $cells = $this->matrix($set, $source, $name);

            if ($cells === []) {
                [$report, $error] = $this->unfilled($set, $slots, $name);

                $this->components->twoColumnDetail("  {$name}", $report);

                if ($error !== null) {
                    $unfilled[$name] = $error;
                }

                continue;
            }

            $target = $to.'/'.$name.'.blade.php';

            if ($files->exists($target) && ! $this->option('force')) {
                $this->components->twoColumnDetail("  {$name}", '<fg=yellow>exists, kept</>');

                continue;
            }

            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $component->render($name, $cells));

            $lock[$set->name]['icons'][$name] = $this->digest($source, $cells);

            $written++;

            $this->components->twoColumnDetail("  {$name}", '<fg=green>'.count(array_unique($cells)).' drawing(s)</>');
        }

        if ($written > 0) {
            $this->pin($set, $source, $files, $to, $lock);
        }

        $this->newLine();
        $this->components->info("{$written} icon(s) written to {$to}.");

        if ($written > 0) {
            $this->clearCompiledViews();
        }

        if ($unfilled === []) {
            return self::SUCCESS;
        }

        foreach ($unfilled as $message) {
            $this->components->error($message);
        }

        return self::FAILURE;
    }

    /**
     * What to say about a name that produced no drawing, and whether to fail.
     *
     * An ordinary name reads as it always did: `shape:icon bicycle` asking for a
     * drawing the set has not got is a typo, reported and shrugged off, because
     * nothing in the library was depending on it.
     *
     * A slot is not that. Every one of them is resolved by a component, so a
     * slot this run could not fill is a component that goes on drawing the
     * packaged Heroicon on a page otherwise wearing somebody else's set — the
     * silence `shape:doctor` exists for, arriving one step earlier. The error
     * names the set and the config key, because the answer to most of these is
     * an entry in `slots` rather than anything to do with this command.
     *
     * The exception is a packaged slot. Shape draws `shape-loading` itself, so a
     * set with nothing to fill it has answered correctly and the fallback is the
     * designed outcome.
     *
     * @return array{0: string, 1: string|null}
     */
    protected function unfilled(IconSet $set, IconSlots $slots, string $name): array
    {
        if (! $slots->has($name)) {
            return ['<fg=red>no SVG found</>', null];
        }

        $key = "shape.icon_sets.{$set->name}.slots";

        if ($slots->isPackaged($name)) {
            return ['<fg=gray>packaged by Shape</>', null];
        }

        if (! $set->declares($name)) {
            return [
                '<fg=red>unfilled</>',
                "Icon set [{$set->name}] says nothing about slot [{$name}], so nothing was written for it and the component that resolves it keeps the icon this package ships. Name the drawing that fills it in [{$key}], or say null there if this set has none.",
            ];
        }

        $drawn = $set->sourceName($name);

        if ($drawn === null) {
            return [
                '<fg=yellow>no drawing in ['.$set->name.']</>',
                null,
            ];
        }

        return [
            '<fg=red>no SVG found</>',
            "Icon set [{$set->name}] fills slot [{$name}] from [{$drawn}], and there is no such drawing. Correct it in [{$key}].",
        ];
    }

    /**
     * Record the set, the ref, the resolved commit, and a digest per icon.
     *
     * Written beside the components, the way `shape:eject` writes
     * `shape-eject.json` beside the ones it copies. Keyed by set, because two
     * sets can legitimately write into one directory — a primary one flat and a
     * supplementary one namespaced — and each is pinned to its own upstream.
     *
     * @param  array<string, array{repo?: string, ref?: string, commit?: string, npm?: string, version?: string, icons: array<string, string>}>  $lock
     */
    protected function pin(IconSet $set, IconSource $source, Filesystem $files, string $to, array $lock): void
    {
        // Only when the run actually used the set's upstream. A `--from`
        // directory that happens to belong to a set with a `repo` was not
        // fetched from it, and recording otherwise would pin a component to a
        // commit nobody read it at.
        $record = $this->fetched()
            ? array_filter($set->npm !== null
                ? ['npm' => $set->npm, 'version' => $source->revision() ?? $this->version($set)]
                : ['repo' => $set->repo, 'ref' => $this->ref($set), 'commit' => $source->revision()],
                fn (?string $value): bool => $value !== null && $value !== '')
            : [];

        ksort($lock[$set->name]['icons']);

        $lock[$set->name] = [...$record, 'icons' => $lock[$set->name]['icons']];

        ksort($lock);

        $files->ensureDirectoryExists($to);
        $files->put(
            $to.'/'.$this->lockName(),
            json_encode($lock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n",
        );
    }
}
