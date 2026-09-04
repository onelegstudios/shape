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
 * A name may be spelled the way the component is — `shape:icon lucide.bell`
 * writes the component that `<x-shape::icon.lucide.bell />` resolves — which is
 * the same run as `shape:icon bell --set=lucide` with the answer moved from a
 * flag onto the name it is true of. Two reasons to prefer it: it is the
 * spelling already in your templates, so there is nothing to translate in
 * either direction; and being per name rather than per run, it is the only form
 * that can ask for two sets at once.
 *
 * The prefix names a set, and where a set is written stays the set's own
 * business — `shape.icon_sets`, as everything else about it is. So the two are
 * the same run in every respect, including the one case where the directory is
 * not what was typed: the set the library is on is written flat, and
 * `hero.bell` on a Heroicons application lands where `--set=hero` lands. A run
 * that resolves to a spelling other than the one it was given says so, since
 * the spelling is the whole reason to type a name this way.
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
        {icons?* : The icon names to generate, spelled as the component is: bell, or lucide.bell}
        {--set= : The icon set to read, for the names that do not name one}
        {--from= : A directory to read SVGs from, instead of fetching the set}
        {--ref= : The branch, tag or commit to fetch, overriding the set\'s own}
        {--offline : Work from what has already been fetched, and fail rather than fetch}
        {--to= : Where to write the components}
        {--namespace= : Override the set\'s own subdirectory, for the names that do not name one; empty writes flat}
        {--force : Overwrite icons that already exist}';

    /**
     * The command description.
     */
    protected $description = 'Generate Shape icon components from a set of SVGs.';

    /**
     * The names of the group being written, which is what `names` answers with.
     *
     * @var list<string>
     */
    protected array $group = [];

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        try {
            $slots = IconSlots::fromConfig();
            $groups = $this->groups();
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        // Said before anything is resolved or fetched, because a run with no
        // names has nothing to fetch it for.
        if ($groups === []) {
            $this->components->error('Name at least one icon, or run shape:icon:all.');

            return self::FAILURE;
        }

        $status = self::SUCCESS;

        foreach ($groups as $prefix => $names) {
            $this->group = $names;

            // One set, one source, one destination, one lockfile: a group is
            // exactly the run this command has always been, and a run that
            // names two sets is two of them in sequence. Grouping rather than
            // resolving per name is what keeps it to one fetch per set.
            try {
                $prefix = (string) $prefix;
                $set = $this->set($prefix === '' ? null : $this->setFor($prefix));

                // `--namespace` is true of a run, so it answers for the names
                // that named nothing themselves and for those only. A group
                // that named a set is written wherever that set is written.
                $namespace = $prefix === '' ? $this->namespace($set) : $this->subdirectory($set);

                if ($prefix !== '') {
                    $this->resolved($set, $prefix, $namespace);
                }

                if ($this->generate($set, $slots, $this->source($set, $files), $files, $this->destination($namespace)) !== self::SUCCESS) {
                    $status = self::FAILURE;
                }
            } catch (InvalidArgumentException|RuntimeException $e) {
                $this->components->error($e->getMessage());

                $status = self::FAILURE;
            }
        }

        return $status;
    }

    /**
     * The names this run was given, grouped by the namespace each asked for.
     *
     * Keyed by the prefix, with the empty string standing for the names that
     * carried none — the group the flags answer for.
     *
     * @return array<string, list<string>>
     */
    protected function groups(): array
    {
        /** @var list<string> $icons */
        $icons = (array) $this->argument('icons');

        $groups = [];

        foreach ($icons as $icon) {
            [$prefix, $name] = $this->split($icon);

            $groups[$prefix][] = $name;
        }

        // In the order they were typed, and each name once: `shape:icon bell
        // bell` is a run that wrote one icon, not one that reported it twice.
        return array_map(
            fn (array $names): array => array_values(array_unique($names)),
            $groups,
        );
    }

    /**
     * One typed name, split into the namespace it asked for and the name.
     *
     * The dot is the component separator and nothing else here has any use for
     * it: a drawing is one kebab-case segment in every set this package knows,
     * and a slot is one too.
     *
     * @return array{0: string, 1: string}
     */
    protected function split(string $icon): array
    {
        if (! str_contains($icon, '.')) {
            return ['', $icon];
        }

        $segments = explode('.', $icon);

        if (count($segments) !== 2 || $segments[0] === '' || $segments[1] === '') {
            throw new InvalidArgumentException("[{$icon}] is not an icon name. Spell it the way the component is: one namespace and one name, like lucide.bell.");
        }

        if (! $this->isSegment($segments[0])) {
            throw new InvalidArgumentException("[{$segments[0]}] is not a namespace. Pass one lower-case segment, like lucide.bell.");
        }

        return [$segments[0], $segments[1]];
    }

    /**
     * Which set a name's prefix names.
     *
     * Matched against both what a set is called and the namespace it declares,
     * because the prefix is read off a component and a component is spelled
     * with whichever of the two that set is written under. A set that renames
     * its own subdirectory is therefore reachable by either, and lands in the
     * one it declared regardless — where a set lives is true of the set, and a
     * generator that took the prefix instead would write the second copy the
     * `namespace` key exists to prevent.
     *
     * Two sets answering to one namespace is the case with no answer — one of
     * them has declared the other's name — and is reported rather than guessed
     * at.
     */
    protected function setFor(string $namespace): string
    {
        $sets = config('shape.icon_sets');

        if (! is_array($sets)) {
            throw new InvalidArgumentException('No icon sets are configured. Add [icon_sets] to config/shape.php.');
        }

        $matches = [];
        $names = [];

        foreach ($sets as $name => $definition) {
            if (! is_string($name)) {
                continue;
            }

            $names[] = $name;

            $declared = is_array($definition) && is_string($definition['namespace'] ?? null)
                ? $definition['namespace']
                : $name;

            if ($name === $namespace || $declared === $namespace) {
                $matches[] = $name;
            }
        }

        $matches = array_values(array_unique($matches));

        if (count($matches) > 1) {
            throw new InvalidArgumentException('Icon sets ['.implode('] and [', $matches)."] are both written into [{$namespace}], so there is no saying which one [{$namespace}.…] means. Give one of them a [namespace] of its own in config/shape.php.");
        }

        if ($matches === []) {
            throw new InvalidArgumentException("No icon set is written into [{$namespace}]. Configured sets are [".implode('], [', $names).'], each reached by its own name or by the [namespace] it declares.');
        }

        return $matches[0];
    }

    /**
     * Say where a group landed, when that is not where it was typed.
     *
     * Which happens for the set the library is on, written flat because that is
     * what makes one spelling at every call site possible, and for a set that
     * declares a namespace other than its name. Both are the set answering a
     * question about itself, and neither is a run's to override — but a name
     * typed as a component and written as another one is worth a sentence, or
     * the next thing that happens is a template reaching for a component that
     * was never written.
     */
    protected function resolved(IconSet $set, string $prefix, ?string $namespace): void
    {
        if ($namespace === $prefix) {
            return;
        }

        $this->components->warn($namespace === null
            ? "Icon set [{$set->name}] is the set the library is on, so it is written flat — these are <x-shape::icon.NAME /> rather than <x-shape::icon.{$prefix}.NAME />."
            : "Icon set [{$set->name}] declares the namespace [{$namespace}], so these are <x-shape::icon.{$namespace}.NAME /> rather than <x-shape::icon.{$prefix}.NAME />.");
    }

    /**
     * The names to write components under: the ones that were typed.
     *
     * @return list<string>
     */
    protected function names(IconSet $set, IconSlots $slots, IconSource $source): array
    {
        return $this->group;
    }
}
