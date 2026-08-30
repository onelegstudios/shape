<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Onelegstudios\Shape\FoldSafety;
use Onelegstudios\Shape\Registry;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Lint components for the one mistake that costs the fold and says nothing.
 *
 * A component that reads global state still renders. It renders correctly, in
 * development, for the developer who wrote it. What it stops doing is folding —
 * or worse, it folds and serves one visitor's session to everybody. Neither
 * failure has a stack trace, which is why this is a command rather than a
 * paragraph in the documentation.
 *
 * It runs over the ejected components in the application by default, because
 * that is where the rule is easiest to break: the file is in `resources/views`
 * now, it looks like every other Blade file in the project, and nothing about it
 * says the compiler is going to pre-render it.
 *
 * Icon coverage is here for the same reason and is otherwise unrelated. Once an
 * application has replaced Shape's icons with a set of its own, a name it missed
 * does not fail — it resolves to the packaged Heroicon and draws perfectly, in
 * the wrong set, on a page that is now wearing two. Nothing renders red, so
 * something has to say it out loud.
 */
class DoctorCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'shape:doctor
        {--path=* : Directories to lint instead of the ejected components}
        {--package : Lint the components this package ships}';

    /**
     * The command description.
     */
    protected $description = 'Check Shape components for the global state that costs them their fold.';

    /**
     * Execute the console command.
     */
    public function handle(Registry $registry, FoldSafety $safety): int
    {
        $paths = $this->paths($registry);

        if ($paths === []) {
            $this->components->info('No components to check. Eject one with `shape:eject`, or pass --path.');

            return self::SUCCESS;
        }

        $offences = 0;
        $checked = 0;

        foreach ($paths as $path) {
            $this->components->info("Checking {$path}");

            foreach ($this->components($path) as $file) {
                $checked++;
                $offences += $this->check($safety, $file, $path);
            }
        }

        $missing = $this->coverage($registry);

        $this->newLine();

        if ($offences === 0 && $missing === 0) {
            $this->components->info("{$checked} component(s) checked, and every one of them folds cleanly.");

            return self::SUCCESS;
        }

        if ($offences > 0) {
            $this->components->error("{$offences} problem(s) in {$checked} component(s).");
            $this->line('  A folded component is rendered once, while Blade compiles. Anything above');
            $this->line('  belongs at the call site, or inside an @unblaze block that cuts a hole in');
            $this->line('  the fold on purpose — which is what `error.blade.php` does, and why.');
        }

        if ($missing > 0) {
            $this->components->error("{$missing} icon(s) this library draws are not in your set.");
            $this->line('  Each one falls back to the icon this package ships, so those components');
            $this->line('  render in Heroicons while the rest of the page renders in yours. Generate');
            $this->line('  them with `shape:icon --replace`, and if a name has no drawing under it,');
            $this->line('  give the set an `aliases` entry for that name in `shape.icon_sets`.');
        }

        return self::FAILURE;
    }

    /**
     * Report how much of what the library draws the ejected icon set covers.
     *
     * Both halves are derived rather than declared: the names come out of the
     * markup of the components that draw them, and the coverage out of the
     * directory those names resolve from first. The check is silent when nothing
     * has been ejected into that directory — an application on the packaged
     * icons is not partially covered, it is covered.
     */
    protected function coverage(Registry $registry): int
    {
        $path = config('shape.components_path');

        if (! is_string($path) || ! is_dir($directory = rtrim($path, '/').'/icon')) {
            return 0;
        }

        $drawn = $registry->icons();

        $present = array_values(array_filter(
            $drawn,
            fn (string $name): bool => is_file($directory.'/'.$name.'.blade.php'),
        ));

        if ($present === []) {
            return 0;
        }

        $missing = array_values(array_diff($drawn, $present));

        $this->components->twoColumnDetail(
            'icon set',
            $this->attribution($directory, $present).', '.count($present).' of '.count($drawn).' names',
        );

        foreach ($missing as $name) {
            $this->components->twoColumnDetail(
                "  {$name}",
                '<fg=red>missing</> <fg=gray>— falls back to Heroicons</>',
            );
        }

        return count($missing);
    }

    /**
     * Which set the ejected icons came from, as far as they will say.
     *
     * Every generated icon carries its set's notice verbatim, so this is a fact
     * about the files rather than a guess about them. Two answers instead of one
     * is worth printing on its own: a directory holding icons from two sets is
     * the mistake this whole check is looking for, arrived at from the other
     * direction.
     *
     * @param  list<string>  $present
     */
    protected function attribution(string $directory, array $present): string
    {
        $sets = config('shape.icon_sets');
        $found = [];

        foreach ($present as $name) {
            $source = (string) file_get_contents($directory.'/'.$name.'.blade.php');

            foreach (is_array($sets) ? $sets : [] as $set => $definition) {
                $notice = is_array($definition) ? ($definition['notice'] ?? null) : null;

                if (is_string($notice) && $notice !== '' && str_contains($source, $notice)) {
                    $found[(string) $set] = true;
                }
            }
        }

        return $found === [] ? 'unattributed' : implode(' and ', array_keys($found));
    }

    /**
     * Report on one component, and return how many problems it has.
     */
    protected function check(FoldSafety $safety, SplFileInfo $file, string $root): int
    {
        $name = str_replace($root.'/', '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());

        if (! $safety->declaresStrategy($source)) {
            $this->components->twoColumnDetail(
                "  {$name}",
                '<fg=yellow>no @blaze annotation</>',
            );

            return 1;
        }

        $found = $safety->inspect($source);

        if ($found === []) {
            if ($this->output->isVerbose()) {
                $this->components->twoColumnDetail("  {$name}", '<fg=green>ok</>');
            }

            return 0;
        }

        foreach ($found as $offence) {
            $this->components->twoColumnDetail(
                "  {$name}:{$offence['line']} <fg=red>{$offence['pattern']}</>",
                "<fg=gray>{$offence['hint']}, resolved once at compile time</>",
            );
        }

        return count($found);
    }

    /**
     * The directories to lint.
     *
     * @return list<string>
     */
    protected function paths(Registry $registry): array
    {
        /** @var list<string> $paths */
        $paths = (array) $this->option('path');

        if ($this->option('package')) {
            $paths[] = $registry->views();
        }

        if ($paths === []) {
            $ejected = config('shape.components_path');

            if (is_string($ejected)) {
                $paths[] = $ejected;
            }
        }

        return array_values(array_filter(array_map(
            fn (string $path): string => rtrim($path, '/'),
            $paths,
        ), 'is_dir'));
    }

    /**
     * The Blade files under a directory.
     *
     * @return iterable<SplFileInfo>
     */
    protected function components(string $path): iterable
    {
        return Finder::create()->files()->in($path)->name('*.blade.php')->sortByName();
    }
}
