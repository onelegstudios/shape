<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Onelegstudios\Shape\Console\Commands\Concerns\ClearsCompiledViews;
use Onelegstudios\Shape\Console\Commands\Concerns\GeneratesIcons;
use Onelegstudios\Shape\Console\Commands\Concerns\ResolvesIconSets;
use Onelegstudios\Shape\Icons\IconSource;
use Onelegstudios\Shape\IconSet;
use Onelegstudios\Shape\IconSlots;
use RuntimeException;

/**
 * Generate exactly the icons Shape draws itself.
 */
class IconReplaceCommand extends Command
{
    use ClearsCompiledViews;
    use GeneratesIcons;
    use ResolvesIconSets;

    /**
     * The command signature.
     */
    protected $signature = 'shape:icon:replace
        {--set= : The icon set to read, overriding the configured one}
        {--from= : A directory to read SVGs from, instead of fetching the set}
        {--ref= : The branch, tag or commit to fetch, overriding the set\'s own}
        {--offline : Work from what has already been fetched, and fail rather than fetch}
        {--to= : Where to write the components}
        {--namespace= : Override the set\'s own subdirectory; empty writes flat}
        {--force : Overwrite icons that already exist}';

    /**
     * The command description.
     */
    protected $description = 'Generate exactly the icons Shape draws itself, from another set.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        try {
            $set = $this->set();
            $slots = IconSlots::fromConfig();
            $namespace = $this->namespace($set);
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($namespace !== null) {
            // A namespaced icon replaces nothing: this library asks for
            // `shape::icon.shape-close`, and a file under `icon/lucide/`
            // answers to `shape::icon.lucide.shape-close`. The run would write
            // fourteen files and change nothing.
            $this->components->error("shape:icon:replace writes over the names Shape draws, which are flat, and [{$set->name}] is written into [icon/{$namespace}/]. Pass --namespace= to write this run flat.");

            return self::FAILURE;
        }

        try {
            $source = $this->source($set, $files);

            return $this->generate($set, $slots, $source, $files, $this->destination($namespace));
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Whether this command reads far enough into the set to want it whole.
     */
    protected function walksTheSet(): bool
    {
        return true;
    }

    /**
     * Whether the set's own subdirectory applies to this command at all.
     *
     * This command is one set standing in for the library's own, whatever
     * `icon_set` says, and the names it writes are flat by definition — so the
     * subdirectory is not applied to it, or `shape:icon:replace --set=lucide`
     * would be refused rather than be a replacement. A set that declares a
     * namespace of its own still lands in it, and is still told that a
     * namespaced icon replaces nothing.
     */
    protected function namespaces(IconSet $set): bool
    {
        return $set->namespace !== null;
    }

    /**
     * The names to write components under: the library's declared slots.
     *
     * Which is what makes this a complete answer: a slot no component happens to
     * draw is still generated, and `shape-loading` is exactly that.
     *
     * @return list<string>
     */
    protected function names(IconSet $set, IconSlots $slots, IconSource $source): array
    {
        return $slots->names();
    }
}
