<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands\Concerns;

use Illuminate\Console\Command;

/**
 * Throw away the compiled views after writing components into an application.
 *
 * A Blade component is a file, and a compiled view is a cached answer about a
 * file. Writing one and leaving the other is the ordinary staleness problem,
 * except that Blade's cache is keyed on the *source* it was compiled from — so
 * a component this package writes for the first time has no stale artifact to
 * invalidate, and one it removes or overwrites has one that nothing will.
 *
 * The failure that costs is the second. A compiled artifact can hold a
 * hard-coded path to a component file, and go on being served after the file is
 * gone; under a folding compiler it can also hold a copy of that component's
 * markup, inlined. Neither raises an error. What reaches a page is stale markup,
 * or — the case that prompted this — a fragment of a template that was never
 * meant to be output at all.
 *
 * So the cache is cleared rather than reasoned about. It costs one recompile,
 * which is exactly what editing any Blade file by hand costs, and it is the
 * difference between a generated component being a file on disk and being a
 * file on disk that may or may not be what renders.
 */
trait ClearsCompiledViews
{
    /**
     * Clear them, and say either that it happened or that it did not.
     *
     * Not silent on failure: a run that wrote components and could not
     * invalidate the cache has left the application in the state this exists to
     * prevent, and the caller is the only one who can finish the job.
     */
    protected function clearCompiledViews(): void
    {
        if ($this->callSilently('view:clear') !== Command::SUCCESS) {
            $this->components->warn('The compiled views could not be cleared. Run `php artisan view:clear` before rendering, or the components just written may not be what renders.');

            return;
        }

        $this->components->info('Compiled views cleared.');
    }
}
