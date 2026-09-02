<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Onelegstudios\Shape\Icons\DirectorySource;
use Onelegstudios\Shape\Icons\GitHubSource;
use Onelegstudios\Shape\Icons\IconSource;
use Onelegstudios\Shape\Icons\NpmSource;
use Onelegstudios\Shape\IconSet;

/**
 * The questions every icon command has to answer before it can do its work.
 *
 * Which set, read from where, at which ref, written into which directory, and
 * what was recorded there last time. All four icon commands ask all of them —
 * the one that only reports asks them so it knows what it is reporting on — so
 * they live here rather than in any one of the four.
 *
 * Two of the answers differ by command rather than by run, and are the two
 * methods below that a command overrides: whether the run walks the whole set,
 * and whether the set's subdirectory applies to it.
 */
trait ResolvesIconSets
{
    /**
     * The set to read, and the layout to read it in.
     */
    protected function set(?string $name = null): IconSet
    {
        $name ??= $this->configured();

        $sets = config('shape.icon_sets');

        if (! is_array($sets) || ! array_key_exists($name, $sets)) {
            throw new InvalidArgumentException("No icon set named [{$name}] is configured.");
        }

        // Two keys, because the two axes belong to different people: the scale
        // is the library's and the drawings are the set's.
        return IconSet::fromArray($name, $sets[$name], config('shape.icon_sizes'));
    }

    /**
     * The set a run reads when the command line names none.
     *
     * Declared in `shape.icon_set`, for the reason a set's `namespace` is
     * declared beside it: which set is yours is true of the application, and a
     * flag is true of one run. An application that has moved the library onto
     * Lucide had to say `--set=lucide` on every run forever, and the run that
     * forgot wrote a Heroicon into a directory of Lucide ones — under a slot
     * name, which says nothing about who drew it. Saying it once here is what
     * makes `shape:icon --replace` a replacement rather than a re-mixing.
     *
     * `--set` stays the override, and a supplementary set — read once, written
     * into a namespace of its own — is exactly the one-off run it is for.
     */
    protected function configured(): string
    {
        $option = $this->option('set');

        if (is_string($option) && $option !== '') {
            return $option;
        }

        $default = config('shape.icon_set');

        if (! is_string($default) || $default === '') {
            throw new InvalidArgumentException('No default icon set is configured. Add [icon_set] to config/shape.php — it names which of [icon_sets] a run reads when --set says nothing.');
        }

        return $default;
    }

    /**
     * Where this run reads drawings from.
     *
     * `--from` wins whenever it is given, and stays the way to generate from a
     * local checkout, from a designer's folder, or from a set with no upstream
     * at all. Otherwise the set says which repository draws it and this fetches
     * it — which is the difference between "regenerate this" being an
     * instruction and being a suggestion.
     */
    protected function source(IconSet $set, Filesystem $files): IconSource
    {
        if (! $this->fetched()) {
            /** @var string $from */
            $from = $this->option('from');

            return new DirectorySource($files, rtrim($from, '/'));
        }

        if ($set->npm !== null) {
            // A registry serves packages and nothing smaller, so there is no
            // whole-or-not question to answer: every run pulls the package. At
            // the sizes these come in — 1.8MB for Material Symbols against 2.8GB
            // of repository — that is cheaper than the raw fetches it replaces.
            return new NpmSource(
                $files,
                $set->name,
                $set->npm,
                $this->version($set),
                $set->path,
                $this->cache(),
                (bool) $this->option('offline'),
                $set->flatten,
            );
        }

        if ($set->repo === null) {
            throw new InvalidArgumentException("Icon set [{$set->name}] says nothing about where it is drawn, so there is nothing to fetch. Pass --from with the directory to read SVGs from, or give the set a [repo] or an [npm] package.");
        }

        return new GitHubSource(
            $files,
            $set->name,
            $set->repo,
            $this->ref($set),
            $set->path,
            $this->cache(),
            (bool) $this->option('offline'),
            // One request for the whole set, rather than one per drawing. Both
            // of these walk far more of it than a raw fetch per file could pay
            // for: `--replace` alone is twelve names over six cells.
            //
            // A flattening set has no choice about it. Its drawings are filed
            // under something their names do not say — a category — so there is
            // no path a raw fetch could ask for until the archive is in hand and
            // collapsed, whether the run wanted one icon or all of them.
            //
            // A set whose repository cannot be pulled whole is the other way
            // round: `--replace` goes back to a drawing at a time, which is
            // twenty-eight requests against an archive PHP cannot hold.
            ($set->archive && $this->walksTheSet()) || $set->flatten,
            $set->flatten,
            $set->archive,
        );
    }

    /**
     * Whether this command reads far enough into the set to want it whole.
     *
     * A run that was handed its names reads one drawing per name, and a run
     * that reports reads one per icon it has a record of. Neither pays for an
     * archive. The two commands that walk the set say so by overriding this.
     */
    protected function walksTheSet(): bool
    {
        return false;
    }

    /**
     * Whether this run reads the set's upstream rather than a local directory.
     */
    protected function fetched(): bool
    {
        $from = $this->option('from');

        return ! is_string($from) || $from === '';
    }

    /**
     * The ref to read the set at — the one asked for, or the one it declares.
     */
    protected function ref(IconSet $set): string
    {
        $ref = $this->option('ref');

        return is_string($ref) && $ref !== '' ? $ref : $set->ref;
    }

    /**
     * The version to read a published set at.
     *
     * `--ref` answers for this too, because it is the same question asked of a
     * different kind of source: read this set at something other than what it
     * declares. `--ref=0.46.0` on a package reads that release.
     */
    protected function version(IconSet $set): string
    {
        $ref = $this->option('ref');

        return is_string($ref) && $ref !== '' ? $ref : $set->version;
    }

    /**
     * Where fetched sets are kept.
     *
     * Under `storage/framework`, beside the other things the framework caches on
     * an application's behalf, because that is what this is: a copy of somebody
     * else's repository that exists only to save fetching it twice. Nothing here
     * is read at render time and nothing is lost by deleting it.
     */
    protected function cache(): string
    {
        return storage_path('framework/shape/icons');
    }

    /**
     * The subdirectory this set is written into, if it has one.
     *
     * Two sets generated into `icon/` share one namespace, and the second one
     * to spell `check` is refused rather than allowed to overwrite the first.
     * A subdirectory is the way to keep both: `icon/lucide/bell.blade.php` is
     * `<x-shape::icon.lucide.bell />`, and a flat `bell` is no longer in its
     * way.
     *
     * The set answers this — with its `namespace`, or with its own name where it
     * declares none — and the flag only overrides it. A flag on its own is
     * remembered by nobody: the run after it, without the flag, would write a
     * second copy elsewhere and pin it in a second lockfile, which is the
     * collision the subdirectory was for. `--namespace=` with nothing after it
     * is the way to say flat out loud, for the one run that means it.
     *
     * Three states, so all three are distinguishable: the option absent defers
     * to the set, the option empty is "flat", and anything else is one
     * kebab-case segment, checked — `--namespace=../..` is the one way this can
     * write outside the components path.
     */
    protected function namespace(IconSet $set): ?string
    {
        $namespace = $this->option('namespace');

        if ($namespace === '') {
            return null;
        }

        if (is_string($namespace)) {
            if (! $this->isSegment($namespace)) {
                throw new InvalidArgumentException("[{$namespace}] is not a namespace. Pass one lower-case segment, like --namespace=lucide.");
            }

            return $namespace;
        }

        if (! $this->namespaces($set)) {
            return null;
        }

        return $this->subdirectory($set);
    }

    /**
     * Whether the set's own subdirectory applies to this command at all.
     *
     * It does to every command but one. `shape:icon:replace` is one set standing
     * in for the library's own, whatever `icon_set` says, and the names it
     * writes are flat by definition.
     */
    protected function namespaces(IconSet $set): bool
    {
        return true;
    }

    /**
     * Where a set lives when neither the flag nor the set itself says.
     *
     * A set that is not the one `shape.icon_set` names is a supplementary set:
     * read for the icons the library's own set has not got, and written beside
     * them rather than among them. So it is written under its own name, which
     * is the answer a `namespace` would have given and the name the set is
     * already known by — `shape:icon bell --set=lucide` puts a Lucide bell in
     * `icon/lucide/bell.blade.php` and leaves `icon/bell.blade.php` to the set
     * the library is on.
     *
     * Flat is still the default for the set that *is* the library's, and that
     * is the whole of the distinction: one spelling at every call site for the
     * icons that come from the set the application chose, and a namespace for
     * the ones that came from somewhere else. A supplementary set that wants a
     * different subdirectory declares `namespace`, and a run that wants none
     * says `--namespace=`.
     *
     * A set whose name is not a namespace is told so rather than quietly
     * written flat, since flat is where it would collide.
     */
    protected function subdirectory(IconSet $set): ?string
    {
        if ($set->namespace !== null) {
            return $set->namespace;
        }

        $default = config('shape.icon_set');

        // Nothing to be supplementary to. A library with no set of its own has
        // no primary namespace to keep clear, so a run writes where it always
        // wrote.
        if (! is_string($default) || $default === '' || $default === $set->name) {
            return null;
        }

        if (! $this->isSegment($set->name)) {
            throw new InvalidArgumentException("Icon set [{$set->name}] is not the set named in [shape.icon_set], so it is written into a subdirectory of its own — and its name is not one. Give it a [namespace] of one lower-case segment in [shape.icon_sets.{$set->name}], or pass --namespace= to write this run flat.");
        }

        return $set->name;
    }

    /**
     * Whether a namespace is one segment this command can safely write into.
     *
     * The one check that keeps a generated component inside the components
     * path, so it is made of every namespace whatever said it: the flag, the
     * set's own `namespace` — checked where the set is parsed — and the set's
     * name standing in for one.
     */
    protected function isSegment(string $namespace): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $namespace) === 1;
    }

    protected function destination(?string $namespace = null): string
    {
        $to = $this->option('to');

        if (is_string($to) && $to !== '') {
            $base = rtrim($to, '/');
        } else {
            $path = config('shape.components_path');

            $base = (is_string($path) ? rtrim($path, '/') : resource_path('views/shape')).'/icon';
        }

        return $namespace === null ? $base : $base.'/'.$namespace;
    }

    /**
     * What has been generated into this directory before, if anything.
     *
     * @return array<string, array{repo?: string, ref?: string, commit?: string, npm?: string, version?: string, icons: array<string, string>}>
     */
    protected function lock(Filesystem $files, string $to): array
    {
        $path = $to.'/'.$this->lockName();

        if (! $files->exists($path)) {
            return [];
        }

        $decoded = json_decode($files->get($path), true);

        if (! is_array($decoded)) {
            return [];
        }

        $lock = [];

        foreach ($decoded as $name => $record) {
            if (is_string($name) && is_array($record) && is_array($record['icons'] ?? null)) {
                /** @var array{repo?: string, ref?: string, commit?: string, npm?: string, version?: string, icons: array<string, string>} $record */
                $lock[$name] = $record;
            }
        }

        return $lock;
    }

    protected function lockName(): string
    {
        return 'shape-icons.json';
    }

    /**
     * Which cell of the matrix is drawn where, for one name.
     *
     * Keyed the way the generated component switches on it, so that assembling
     * the arms afterwards is a grouping and nothing more.
     *
     * `$name` is the name the component is written under throughout; only the
     * path it is substituted into takes the set's own spelling. That asymmetry
     * is the point of a slot — `shape-close` reads `x.svg` under Lucide and
     * `x-mark.svg` under Heroicons, and is written to `shape-close.blade.php`
     * either way, so the close button asks for a role and gets whichever set is
     * installed.
     *
     * Nothing, when the set answers `null`: it has been asked about the slot and
     * has no drawing for it, which the caller reports rather than treats as a
     * missing file.
     *
     * @return array<string, string>
     */
    protected function matrix(IconSet $set, IconSource $source, string $name): array
    {
        $cells = [];
        $drawn = $set->sourceName($name);

        if ($drawn === null) {
            return [];
        }

        foreach ($set->styles() as $style) {
            foreach ($set->sizes() as $size) {
                // A cell may be spelled more than one way, and only the source
                // says which of them is a drawing: Bootstrap fills `person-x` as
                // `person-fill-x` and `check-circle` as `check-circle-fill`, so
                // the first candidate with a file behind it is the cell, and a
                // cell with none is empty rather than wrong.
                foreach ($set->paths($style, $size, $drawn) as $path) {
                    if ($source->has($path)) {
                        $cells["{$style}:{$size}"] = $path;

                        break;
                    }
                }
            }
        }

        return $cells;
    }

    /**
     * What one drawing is, boiled down to something comparable.
     *
     * Over the cells rather than over the generated component, because the
     * question `--status` answers is whether *upstream* moved. A component
     * regenerated by a later version of this command would differ byte for byte
     * while the drawing behind it had not changed at all.
     *
     * @param  array<string, string>  $cells
     */
    protected function digest(IconSource $source, array $cells): string
    {
        $parts = [];

        foreach ($cells as $cell => $path) {
            $parts[] = $cell.' '.hash('sha256', $source->get($path));
        }

        return hash('sha256', implode("\n", $parts));
    }
}
