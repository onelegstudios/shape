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
 * Generate icon components for the names it is given.
 *
 * The path typed most, and the narrowest one: a drawing read per name, and a
 * component written per name. `shape:icon:all` writes the whole set,
 * `shape:icon:replace` writes the library's own slots, and `shape:icon:status`
 * reports on whatever any of them has written.
 *
 * What a set is, and what every one of the four has to work out before it can
 * do anything, is in `ResolvesIconSets`.
 */
class IconCommand extends Command
{
    use ClearsCompiledViews;
    use GeneratesIcons;
    use ResolvesIconSets;

    /**
     * The command signature.
     */
    protected $signature = 'shape:icon
        {icons?* : The icon names to generate}
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
    protected $description = 'Generate Shape icon components from a set of SVGs.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        try {
            $set = $this->set();
            $slots = IconSlots::fromConfig();
            $namespace = $this->namespace($set);

            $source = $this->source($set, $files);

            return $this->generate($set, $slots, $source, $files, $this->destination($namespace));
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * The names to write components under: the ones that were typed.
     *
     * @return list<string>
     */
    protected function names(IconSet $set, IconSlots $slots, IconSource $source): array
    {
        /** @var list<string> $icons */
        $icons = (array) $this->argument('icons');

        return $icons;
    }
}
