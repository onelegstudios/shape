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
 * Generate every icon in the set.
 */
class IconAllCommand extends Command
{
    use ClearsCompiledViews;
    use GeneratesIcons;
    use ResolvesIconSets;

    /**
     * The command signature.
     */
    protected $signature = 'shape:icon:all
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
    protected $description = 'Generate a Shape icon component for every icon in the set.';

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

        // This command is a listing, and a listing is the one thing a drawing-
        // at-a-time source cannot produce. Said here, before anything is
        // fetched, because the alternative is what this used to do: pull a
        // repository measured in gigabytes and be killed unpacking it, with a
        // stack trace where the reason should be. A local checkout still lists
        // fine, so `--from` is the way to have this run anyway.
        if (! $set->archive && $this->fetched()) {
            $this->components->error("Icon set [{$set->name}] is read one drawing at a time, because its repository is too large to fetch whole — so there is no listing to walk. Name the icons you want with shape:icon, run shape:icon:replace for Shape's own, or pass --from with a local checkout.");

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
     * The names to write components under: everything the set holds.
     *
     * It used to have to run the alias map backwards, and the hairy case was a
     * file whose name was itself an alias key — a Heroicons-named
     * `check-circle.svg` sitting beside Lucide's `circle-check.svg` would have
     * shadowed the alias with the wrong glyph, so it was written under nothing.
     * Slots live in a namespace no set uses, so that case cannot arise and there
     * is nothing left to reverse.
     *
     * @return list<string>
     */
    protected function names(IconSet $set, IconSlots $slots, IconSource $source): array
    {
        $names = [];

        foreach ($set->directories() as $directory) {
            foreach ($source->names($directory) as $file) {
                // The set's own patterns decide what a listed file is called,
                // because a listing is filenames and a filename is not always a
                // name: Phosphor's `assets/fill/heart-fill.svg` is the fill
                // drawing of `heart`, and a file no pattern accounts for is not
                // this set's to write at all.
                $name = $set->nameFor($directory, $file);

                if ($name !== null) {
                    $names[] = $name;
                }
            }
        }

        $names = array_values(array_unique($names));

        sort($names);

        return $names;
    }
}
