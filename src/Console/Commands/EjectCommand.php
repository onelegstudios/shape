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
 * Copy components out of the package and into the application.
 *
 * The third and last step of Shape's customization escalation: tokens, then
 * utilities, then this. Everything before it is a way of influencing a component
 * from outside; this hands the file over. Once ejected, the component is the
 * application's code, resolved ahead of the packaged one by the path the service
 * provider registers first.
 *
 * `vendor:publish --tag="shape-components"` does the same thing for the
 * whole library at once. This exists because that is rarely what anybody wants:
 * a modal alone is a file that composes a button, an icon, a heading and a
 * close — eject the modal without them and half of it still belongs to the
 * package, which is the worst of both arrangements.
 */
class EjectCommand extends Command
{
    use ClearsCompiledViews;
    use EjectsComponents;
    use WritesComponents;

    /**
     * The command signature.
     */
    protected $signature = 'shape:eject
        {components?* : The components to eject}
        {--bare : Eject only the named components, without what they compose}
        {--force : Overwrite components that have already been ejected}';

    /**
     * The command description.
     */
    protected $description = 'Copy the named Shape components into the application, with everything they compose.';

    /**
     * Execute the console command.
     */
    public function handle(Registry $registry, Filesystem $files): int
    {
        $destination = $this->destination();

        if ($destination === null) {
            return self::FAILURE;
        }

        $requested = $this->requested($registry);

        if ($requested === null) {
            return self::FAILURE;
        }

        $resolved = $this->option('bare') ? $requested : $registry->resolve($requested);

        return $this->eject($registry, $files, $destination, $requested, $resolved);
    }

    /**
     * The components named on the command line.
     *
     * @return list<string>|null
     */
    protected function requested(Registry $registry): ?array
    {
        /** @var list<string> $components */
        $components = (array) $this->argument('components');

        if ($components === []) {
            $this->components->error('Name at least one component, or run shape:eject:all.');
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
}
