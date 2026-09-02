<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Onelegstudios\Shape\Console\Commands\Concerns\ClearsCompiledViews;
use Onelegstudios\Shape\Console\Commands\Concerns\EjectsComponents;
use Onelegstudios\Shape\Console\Commands\Concerns\WritesComponents;
use Onelegstudios\Shape\Registry;

/**
 * Copy every component out of the package and into the application.
 */
class EjectAllCommand extends Command
{
    use ClearsCompiledViews;
    use EjectsComponents;
    use WritesComponents;

    /**
     * The command signature.
     */
    protected $signature = 'shape:eject:all
        {--force : Overwrite components that have already been ejected}';

    /**
     * The command description.
     */
    protected $description = 'Copy every Shape component into the application.';

    /**
     * Execute the console command.
     */
    public function handle(Registry $registry, Filesystem $files): int
    {
        $destination = $this->destination();

        if ($destination === null) {
            return self::FAILURE;
        }

        // Every component's name asked for, so there is nothing to validate and
        // nothing composed that is not already in the list. Resolution still
        // runs, because it is what puts a component behind the ones that
        // compose it rather than in registry order.
        $requested = $registry->names();

        return $this->eject($registry, $files, $destination, $requested, $registry->resolve($requested));
    }
}
