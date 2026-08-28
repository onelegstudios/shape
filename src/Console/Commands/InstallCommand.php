<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Add Shape's two lines to the application's stylesheet and script.
 *
 * There is no third line. The package has no config to publish before it works,
 * no assets to build, no CSS artifact to keep in sync — the token file is
 * imported from `vendor/` and declares `@source "../views"`, so a consumer's own
 * Tailwind build scans the package's Blade directly.
 *
 * Which is why this command edits nothing it was not asked to. It looks for the
 * two files a Laravel application has by default, appends what is missing, and
 * prints anything it could not place rather than guessing at a build setup it
 * cannot see. An installer that rewrites an entry point it does not understand
 * is worse than a paragraph in the README.
 */
class InstallCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'shape:install
        {--css=resources/css/app.css : The stylesheet to import the tokens into}
        {--js=resources/js/app.js : The script to register the plugin in}';

    /**
     * The command description.
     */
    protected $description = 'Import Shape\'s tokens and register its script in the application.';

    /**
     * The line that pulls in the design tokens.
     */
    protected string $import = '@import "../../vendor/onelegstudios/laravel-shape/resources/css/shape.css";';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        $this->stylesheet($files, $this->path((string) $this->option('css')));
        $this->script($files, $this->path((string) $this->option('js')));

        $this->newLine();
        $this->components->info('Shape is installed. Restart your build, and see docs/ for what to write first.');

        return self::SUCCESS;
    }

    /**
     * Import the tokens, after Tailwind rather than before it.
     */
    protected function stylesheet(Filesystem $files, string $path): void
    {
        $relative = $this->relative($path);

        if (! $files->exists($path)) {
            $this->missing($relative, $this->import);

            return;
        }

        $contents = $files->get($path);

        if (str_contains($contents, 'laravel-shape/resources/css/shape.css')) {
            $this->components->twoColumnDetail($relative, '<fg=gray>already imports the tokens</>');

            return;
        }

        // After the last import in the file, which in a default Laravel
        // application is Tailwind's own. Tokens declared before `@import
        // "tailwindcss"` would be overwritten by the theme they are overriding.
        $lines = explode("\n", $contents);
        $last = 0;

        foreach ($lines as $index => $line) {
            if (str_starts_with(trim($line), '@import')) {
                $last = $index + 1;
            }
        }

        array_splice($lines, $last, 0, [$this->import]);

        $files->put($path, implode("\n", $lines));

        $this->components->twoColumnDetail($relative, '<fg=green>tokens imported</>');
    }

    /**
     * Register the script, which installs as an Alpine plugin or on its own.
     */
    protected function script(Filesystem $files, string $path): void
    {
        $relative = $this->relative($path);

        $snippet = <<<'JS'

        import shape from '../../vendor/onelegstudios/laravel-shape/resources/js/shape.js'

        shape()
        JS;

        if (! $files->exists($path)) {
            $this->missing($relative, trim($snippet));

            return;
        }

        $contents = $files->get($path);

        if (str_contains($contents, 'laravel-shape/resources/js/shape.js')) {
            $this->components->twoColumnDetail($relative, '<fg=gray>already registers the script</>');

            return;
        }

        $files->append($path, $snippet."\n");

        $this->components->twoColumnDetail($relative, '<fg=green>script registered</>');
    }

    /**
     * Say what could not be placed, and where it goes.
     */
    protected function missing(string $relative, string $snippet): void
    {
        $this->components->twoColumnDetail($relative, '<fg=yellow>not found</>');

        $this->newLine();
        $this->line("  Add this to your stylesheet or script yourself:\n");

        foreach (explode("\n", $snippet) as $line) {
            $this->line("      <fg=gray>{$line}</>");
        }

        $this->newLine();
    }

    /**
     * Resolve a path given on the command line.
     *
     * Relative to the project, which is how anyone would type it, unless it is
     * already absolute.
     */
    protected function path(string $path): string
    {
        return str_starts_with($path, '/') ? $path : base_path($path);
    }

    protected function relative(string $path): string
    {
        return str_replace(base_path().'/', '', $path);
    }
}
