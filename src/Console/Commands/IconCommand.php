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
 * Turn a set of SVGs into icon components.
 *
 * Every icon in this library is a generated file, and generating them is what
 * makes the set cheap to grow and what keeps the header on each one —
 * "Regenerate; don't hand-edit" — an honest instruction rather than a hope.
 *
 * What a set looks like is declared in `shape.icon_sets`, measured against the
 * scale in `shape.icon_sizes`, and parsed by `IconSet`: a matrix of styles
 * against sizes, mostly sparse. This command is the part that walks that
 * matrix, reads whichever cells the set actually holds, and writes one
 * component per name with the answers baked in.
 *
 * The alternative, which WireUI takes, is a Composer package per icon set. That
 * is a version matrix to maintain for what is fundamentally a code generator,
 * and it puts the set a consumer actually wants — theirs — furthest out of
 * reach. This reads whatever set it is pointed at, in whatever layout the
 * manifest describes.
 *
 * Where the bytes come from is not this command's business. An `IconSource`
 * answers for a path, and it is either a directory somebody already has
 * (`--from`) or a repository fetched and cached on their behalf. That second
 * one is what makes the header's instruction followable: Heroicons is not a
 * dependency of this package, so before it, regenerating meant cloning
 * something nobody had been told to clone.
 *
 * `--replace` is the generator taken to its conclusion. Writing an icon into
 * `components_path` replaces the packaged one everywhere, including inside this
 * library's own components, because that path resolves first — so generating
 * the library's slots swaps the icon set out from under the whole of it. What
 * made that impossible before slots was not the mechanism but the vocabulary: a
 * set that spells `x-mark` as `x` could add icons here, and never replace one.
 *
 * A slot is that vocabulary made Shape's own. `shape-close` is the dismiss glyph
 * whoever drew it, and `shape.icon_slots` declares the list while each set says
 * which of its files fills each one. So the filename states the role, the header
 * states the vendor, and a set that has nothing for a slot says `null` rather
 * than leaving a hole nobody can see.
 *
 * A namespace is the other end of the same problem. Two sets written flat share
 * one namespace, and the second to spell `check` is refused rather than allowed
 * to overwrite the first; a subdirectory gives each set its own. The set the
 * library is on stays flat, so that a call site has one spelling for an icon
 * whichever set drew it. Every other set is a supplementary one — read for what
 * the library's set has not got — and is written under its own name, because
 * flat is precisely where it would collide.
 *
 * Which subdirectory is the set's own answer, declared as `namespace` in
 * `shape.icon_sets` beside everything else that is true of it, and its own name
 * where it declares none. A flag alone was not enough: it is remembered only for
 * the run it is typed on, so the next run without it wrote a second copy flat
 * and pinned it in a second lockfile — exactly the collision the subdirectory
 * existed to prevent. `--namespace` survives as the override for a one-off run,
 * and `--namespace=` as the way to say flat out loud.
 *
 * Which set is read at all is the same kind of fact, and is declared the same
 * way: `shape.icon_set` names it, so an application that has moved the library
 * onto Lucide says so once instead of on every run it ever types. `--set` is
 * the override for the run that means it.
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
