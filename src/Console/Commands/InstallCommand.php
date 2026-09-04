<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use function Laravel\Prompts\select;

/**
 * Add Shape's two lines to the application's stylesheet and script, and ask
 * which set the library is drawn in.
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
 *
 * The icon question is asked here because this is the only moment it is cheap.
 * The fourteen slots ship drawn in Heroicons, so pressing enter is a working
 * install that publishes nothing and keeps the paragraph above true. Naming
 * another set is two writes rather than none — the drawings, and the `icon_set`
 * that every later run reads — which is the pair that makes the answer true of
 * the application rather than of one command line. Asked on day one it is a
 * prompt; found out on day ninety it is a components directory holding two
 * vendors under names that state a role and say nothing about who drew them.
 */
class InstallCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'shape:install
        {--css=resources/css/app.css : The stylesheet to import the tokens into}
        {--js=resources/js/app.js : The script to register the plugin in}
        {--icons= : The icon set to draw the library in, answering the prompt in advance}';

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

        if ($this->icons($files) !== self::SUCCESS) {
            return self::FAILURE;
        }

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
     * Ask which set the library is drawn in, and make the answer true.
     *
     * Enter is Heroicons, and that is not a decision deferred: the slots ship
     * drawn in it, so the answer that changes nothing is also the answer with
     * nothing to publish. Any other answer generates the slots from that set and
     * then records it, in that order — a set is fetched over the network, and a
     * run that cannot reach one has to leave `icon_set` naming the set the files
     * on disk were actually drawn from.
     */
    protected function icons(Filesystem $files): int
    {
        $sets = config('shape.icon_sets');
        $current = config('shape.icon_set');

        if (! is_array($sets) || $sets === [] || ! is_string($current) || $current === '') {
            // A published config somebody has edited past the point this command
            // can read. Saying so beats installing an answer over the top of it.
            $this->components->twoColumnDetail('icon set', '<fg=yellow>not configured</>');

            return self::SUCCESS;
        }

        /** @var list<string> $names */
        $names = array_map(strval(...), array_keys($sets));

        $chosen = $this->chosen($names, $current);

        if ($chosen === null) {
            return self::FAILURE;
        }

        if ($chosen === $current) {
            $this->components->twoColumnDetail('icon set', "<fg=gray>drawn in [{$current}]</>");

            return self::SUCCESS;
        }

        $this->newLine();

        if ($this->call('shape:icon:replace', ['--set' => $chosen, '--force' => true]) !== self::SUCCESS) {
            $this->components->error("The icons could not be generated, so [icon_set] still says [{$current}] and nothing has been swapped.");

            return self::FAILURE;
        }

        $this->record($files, $chosen);

        return self::SUCCESS;
    }

    /**
     * The set this run is being told to draw the library in.
     *
     * `--icons` for a scripted install, the prompt for a typed one, and the set
     * already configured for a run with no terminal to ask — which is what keeps
     * `--no-interaction` the install it has always been.
     *
     * @param  list<string>  $names
     * @return string|null Null where the option named a set that is not configured.
     */
    protected function chosen(array $names, string $current): ?string
    {
        $option = $this->option('icons');

        if (is_string($option) && $option !== '') {
            if (! in_array($option, $names, true)) {
                $this->components->error("No icon set named [{$option}] is configured. [shape.icon_sets] has: ".implode(', ', $names).'.');

                return null;
            }

            return $option;
        }

        if (! $this->input->isInteractive()) {
            return $current;
        }

        $answer = select(
            label: 'Which icon set should Shape be drawn in?',
            options: $names,
            default: $current,
            // Every set at once. A list this short that scrolls hides the
            // choice being made, which is the whole point of asking.
            scroll: count($names),
            hint: "Enter keeps [{$current}], which is the set the library is drawn in now.",
        );

        // Not a multiple-choice question and not a keyed list, so the answer is
        // one of the names. `select()` types it wider than it asks it, and the
        // set already configured is the right reading of anything else.
        return is_string($answer) ? $answer : $current;
    }

    /**
     * Write the answer into a published config, so no later run has to be told.
     *
     * Which set is yours is true of the application and not of a run: a `--set`
     * typed once and forgotten is how a components directory ends up holding two
     * vendors. This is the one file a default install does not publish, and only
     * because the default is the one answer that needs no file.
     */
    protected function record(Filesystem $files, string $set): void
    {
        $path = config_path('shape.php');
        $relative = $this->relative($path);

        if (! $files->exists($path)) {
            $this->callSilent('vendor:publish', ['--tag' => 'laravel-shape-config']);
        }

        $contents = $files->exists($path) ? $files->get($path) : '';

        $count = 0;
        $replaced = preg_replace("/('icon_set'\s*=>\s*)'[^']*'/", '${1}'."'{$set}'", $contents, 1, $count);

        if (! is_string($replaced) || $count !== 1) {
            // The drawings are already written, so this is a line to add rather
            // than a run to repeat. Printing it beats rewriting a config file
            // whose shape this command no longer recognises.
            $this->components->twoColumnDetail($relative, '<fg=yellow>could not place [icon_set]</>');

            $this->newLine();
            $this->line("  The icons are generated. Set this yourself, so later runs read it:\n");
            $this->line("      <fg=gray>'icon_set' => '{$set}',</>");
            $this->newLine();

            return;
        }

        $files->put($path, $replaced);

        config(['shape.icon_set' => $set]);

        $this->components->twoColumnDetail($relative, "<fg=green>icon set is [{$set}]</>");
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
