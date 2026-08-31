<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Onelegstudios\Shape\Icons\DirectorySource;
use Onelegstudios\Shape\Icons\GitHubSource;
use Onelegstudios\Shape\Icons\IconSource;
use Onelegstudios\Shape\IconSet;
use Onelegstudios\Shape\Registry;
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
 * the twelve names the library draws swaps the icon set out from under the whole
 * of it. What made that impossible before aliases was not the mechanism but the
 * vocabulary: a set that spells `x-mark` as `x` can add icons here, and never
 * replace one.
 *
 * `--namespace` is the other end of the same problem. Two sets written flat
 * share one namespace, and the second to spell `check` is refused rather than
 * allowed to overwrite the first; a subdirectory gives each set its own. It is
 * not the default, and shouldn't be: the primary set stays flat so that a call
 * site has one spelling for an icon whichever set drew it.
 */
class IconCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'shape:icon
        {icons?* : The icon names to generate}
        {--set=heroicons : The icon set to read}
        {--from= : A directory to read SVGs from, instead of fetching the set}
        {--ref= : The branch, tag or commit to fetch, overriding the set\'s own}
        {--offline : Work from what has already been fetched, and fail rather than fetch}
        {--to= : Where to write the components}
        {--namespace= : Write into a subdirectory, so a set has a namespace of its own}
        {--all : Generate every icon in the set}
        {--replace : Generate exactly the icons Shape draws itself}
        {--status : Report which generated icons have been redrawn upstream}
        {--force : Overwrite icons that already exist}';

    /**
     * The command description.
     */
    protected $description = 'Generate Shape icon components from a set of SVGs.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files, Registry $registry): int
    {
        try {
            $set = $this->set();
            $namespace = $this->namespace();
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('status')) {
            return $this->status($files, $this->destination($namespace));
        }

        if ($this->option('replace') && ((array) $this->argument('icons') !== [] || $this->option('all'))) {
            $this->components->error('--replace already knows which icons to generate. Drop the names and --all, or drop --replace.');

            return self::FAILURE;
        }

        if ($this->option('replace') && $namespace !== null) {
            // A namespaced icon replaces nothing: this library asks for
            // `shape::icon.x-mark`, and a file under `icon/lucide/` answers to
            // `shape::icon.lucide.x-mark`. The run would write twelve files and
            // change nothing.
            $this->components->error('--replace writes over the names Shape draws, which are flat. Drop --namespace, or drop --replace.');

            return self::FAILURE;
        }

        try {
            $source = $this->source($set, $files);

            return $this->generate($set, $source, $files, $registry, $this->destination($namespace));
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Write one component per name, and record what each was drawn from.
     */
    protected function generate(IconSet $set, IconSource $source, Filesystem $files, Registry $registry, string $to): int
    {
        $names = $this->names($set, $source, $registry);

        if ($names === []) {
            $this->components->error('Name at least one icon, or pass --all.');

            return self::FAILURE;
        }

        $lock = $this->lock($files, $to);
        $written = 0;

        foreach ($names as $name) {
            $cells = $this->matrix($set, $source, $name);

            if ($cells === []) {
                $this->components->twoColumnDetail("  {$name}", '<fg=red>no SVG found</>');

                continue;
            }

            $target = $to.'/'.$name.'.blade.php';

            if ($files->exists($target) && ! $this->option('force')) {
                $this->components->twoColumnDetail("  {$name}", '<fg=yellow>exists, kept</>');

                continue;
            }

            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $this->component($set, $source, $cells));

            $lock[$set->name]['icons'][$name] = $this->digest($source, $cells);

            $written++;

            $this->components->twoColumnDetail("  {$name}", '<fg=green>'.count(array_unique($cells)).' drawing(s)</>');
        }

        if ($written > 0) {
            $this->pin($set, $source, $files, $to, $lock);
        }

        $this->newLine();
        $this->components->info("{$written} icon(s) written to {$to}.");

        return self::SUCCESS;
    }

    /**
     * Report which generated icons have been redrawn since they were generated.
     *
     * The lockfile records what each icon was drawn from, which is the only way
     * to tell the interesting case apart from the ordinary one. A component that
     * differs from the current upstream drawing might have been hand-edited, or
     * upstream might have moved underneath it; without the record both look the
     * same, and only the second is a reason to regenerate.
     *
     * This is the same trade `shape:eject --status` makes against the package,
     * one layer further out — there, the package is upstream; here, the icon set
     * is.
     */
    protected function status(Filesystem $files, string $to): int
    {
        $lock = $this->lock($files, $to);

        if ($lock === []) {
            $this->components->info("No icons have been generated into {$to}.");

            return self::SUCCESS;
        }

        $stale = 0;

        foreach ($lock as $name => $record) {
            $this->components->twoColumnDetail("<fg=default>{$name}</>", $this->pinned($record));

            // Icons generated from a directory record no upstream, and this
            // command does not know which directory it was. Reaching for the
            // set's repository instead would compare them against drawings they
            // never came from, over a network nobody asked it to use.
            if (! isset($record['repo']) && $this->fetched()) {
                $this->components->twoColumnDetail('  generated from a directory', '<fg=gray>pass --from to check</>');

                continue;
            }

            try {
                $set = $this->set($name);
                $source = $this->source($set, $files);
            } catch (InvalidArgumentException|RuntimeException $e) {
                $this->components->twoColumnDetail('  '.$e->getMessage(), '<fg=yellow>not checked</>');

                continue;
            }

            foreach ($record['icons'] as $icon => $digest) {
                [$state, $counts] = $this->state($set, $source, $files, $to, $icon, $digest);

                $stale += $counts;

                $this->components->twoColumnDetail("  {$icon}", $state);
            }
        }

        $this->newLine();

        if ($stale > 0) {
            $this->components->warn("{$stale} icon(s) have been redrawn upstream. Regenerate them with --force.");
        } else {
            $this->components->info('Every generated icon is level with the set it came from.');
        }

        return self::SUCCESS;
    }

    /**
     * How one recorded icon stands against the set as it is now.
     *
     * @return array{0: string, 1: int}
     */
    protected function state(IconSet $set, IconSource $source, Filesystem $files, string $to, string $icon, string $digest): array
    {
        if (! $files->exists($to.'/'.$icon.'.blade.php')) {
            return ['<fg=gray>gone</>', 0];
        }

        $cells = $this->matrix($set, $source, $icon);

        if ($cells === []) {
            return ['<fg=yellow>no longer in the set</>', 1];
        }

        return $this->digest($source, $cells) === $digest
            ? ['<fg=green>unchanged</>', 0]
            : ['<fg=yellow>redrawn upstream</>', 1];
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

    /**
     * Record the set, the ref, the resolved commit, and a digest per icon.
     *
     * Written beside the components, the way `shape:eject` writes
     * `shape-eject.json` beside the ones it copies. Keyed by set, because two
     * sets can legitimately write into one directory — a primary one flat and a
     * supplementary one namespaced — and each is pinned to its own upstream.
     *
     * @param  array<string, array{repo?: string, ref?: string, commit?: string, icons: array<string, string>}>  $lock
     */
    protected function pin(IconSet $set, IconSource $source, Filesystem $files, string $to, array $lock): void
    {
        // Only when the run actually used the set's upstream. A `--from`
        // directory that happens to belong to a set with a `repo` was not
        // fetched from it, and recording otherwise would pin a component to a
        // commit nobody read it at.
        $record = $this->fetched()
            ? array_filter([
                'repo' => $set->repo,
                'ref' => $this->ref($set),
                'commit' => $source->revision(),
            ], fn (?string $value): bool => $value !== null && $value !== '')
            : [];

        ksort($lock[$set->name]['icons']);

        $lock[$set->name] = [...$record, 'icons' => $lock[$set->name]['icons']];

        ksort($lock);

        $files->ensureDirectoryExists($to);
        $files->put(
            $to.'/'.$this->lockName(),
            json_encode($lock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n",
        );
    }

    /**
     * What was recorded for one set, as one line.
     *
     * @param  array{repo?: string, ref?: string, commit?: string, icons: array<string, string>}  $record
     */
    protected function pinned(array $record): string
    {
        $repo = $record['repo'] ?? null;
        $commit = $record['commit'] ?? null;

        if ($repo === null) {
            return '<fg=gray>a directory</>';
        }

        return '<fg=gray>'.$repo.'@'.($commit === null ? ($record['ref'] ?? '?') : substr($commit, 0, 12)).'</>';
    }

    /**
     * What has been generated into this directory before, if anything.
     *
     * @return array<string, array{repo?: string, ref?: string, commit?: string, icons: array<string, string>}>
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
                /** @var array{repo?: string, ref?: string, commit?: string, icons: array<string, string>} $record */
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
     * `$name` is Shape's name for the drawing throughout; only the path it is
     * substituted into takes the set's own spelling of it. That asymmetry is the
     * point of an alias — `x-mark` reads `x.svg` and is still written to
     * `x-mark.blade.php`, so the checkbox and the close button keep asking for
     * the names they have always asked for.
     *
     * @return array<string, string>
     */
    protected function matrix(IconSet $set, IconSource $source, string $name): array
    {
        $cells = [];
        $drawn = $set->sourceName($name);

        foreach ($set->styles() as $style) {
            foreach ($set->sizes() as $size) {
                $pattern = $set->pattern($style, $size);

                if ($pattern === null) {
                    continue;
                }

                $path = str_replace('{name}', $drawn, $pattern);

                if ($source->has($path)) {
                    $cells["{$style}:{$size}"] = $path;
                }
            }
        }

        return $cells;
    }

    /**
     * Assemble the component around however many distinct drawings there are.
     *
     * One drawing needs no `switch`: a set with a single style drawn at a single
     * size has nothing to choose between, and a component that emits a `switch`
     * with one arm is asking the reader to work out that it never branches.
     *
     * @param  array<string, string>  $cells
     */
    protected function component(IconSet $set, IconSource $source, array $cells): string
    {
        $body = count(array_unique($cells)) === 1
            ? $this->svg($source->get((string) reset($cells)))
            : $this->switch($set, $source, $cells);

        $notice = $this->header($set, $source);

        return <<<BLADE
        @blaze(fold: true, memo: true)

        {{-- {$notice}Regenerate; don't hand-edit. --}}

        @props([
        {$this->props($set)}
        ])

        @php
        {$this->resolution($set)}\$classes = Shape::classes('shrink-0')
            ->add(match (\$size) {
        {$this->classes($set)}
            });
        @endphp

        {$body}

        BLADE;
    }

    /**
     * What the file says about where it came from, before the instruction.
     *
     * The licence notice was always here, and it has to be: Font Awesome Free is
     * CC BY 4.0 and Material is Apache 2.0, and a consumer redistributing
     * generated components inherits the attribution those ask for.
     *
     * The commit joins it when there is one to state. A fetched set is fetched
     * at a ref, and a ref is usually a branch — so recording `master` would say
     * nothing about which drawing ended up in the file. The resolved commit
     * makes each generated component traceable on its own, without the lockfile
     * beside it. A `--from` directory has no revision to state, and says nothing.
     */
    protected function header(IconSet $set, IconSource $source): string
    {
        $revision = $source->revision();

        $stamp = $revision === null || $set->repo === null
            ? ''
            : $set->repo.'@'.substr($revision, 0, 12).'.';

        $notice = trim($set->notice.' '.$stamp);

        return $notice === '' ? '' : $notice.' ';
    }

    /**
     * The props, which are the two axes and nothing else.
     *
     * A set with one style still declares `variant`, so that a `variant` passed
     * by a shared call site is ignored rather than falling through to the
     * attribute bag and rendering itself on the `<svg>`.
     */
    protected function props(IconSet $set): string
    {
        $variant = $set->hasOneStyle()
            ? "'".$set->styles()[0]."'"
            : 'null';

        return implode("\n", [
            "    'variant' => {$variant},",
            "    'size' => '".$set->defaultSize()."',",
        ]);
    }

    /**
     * The line that lets a size choose a style, when there is a choice to make.
     *
     * Written as `??=` rather than as a default in `@props` because the answer
     * depends on the other prop. Both are static at almost every call site, so
     * Blaze folds the whole thing away and the generated file is the only place
     * this ever runs.
     */
    protected function resolution(IconSet $set): string
    {
        if ($set->hasOneStyle()) {
            return '';
        }

        $default = $set->styleFor($set->defaultSize());

        $grouped = [];

        foreach ($set->sizes() as $size) {
            $style = $set->styleFor($size);

            if ($style !== $default) {
                $grouped[$style][] = "'{$size}'";
            }
        }

        $arms = [];

        foreach ($grouped as $style => $sizes) {
            $arms[] = '    '.implode(', ', $sizes)." => '{$style}',";
        }

        $arms[] = "    default => '{$default}',";

        return implode("\n", [
            '$variant ??= match ($size) {',
            ...$arms,
            '};',
            '',
            '',
        ]);
    }

    /**
     * The arms of the size match, with the largest size as the default.
     */
    protected function classes(IconSet $set): string
    {
        $sizes = $set->sizes();
        $default = $set->defaultSize();

        $arms = [];

        foreach ($sizes as $size) {
            $arms[] = $size === $default
                ? "        default => '".$set->classFor($size)."',"
                : "        '{$size}' => '".$set->classFor($size)."',";
        }

        return implode("\n", $arms);
    }

    /**
     * The drawing arms, with the default cell's drawing as the fallthrough.
     *
     * Every cell that resolved to the same file shares an arm, which is why
     * Heroicons — six cells over four drawings — writes three cases and a
     * default rather than six of anything.
     *
     * Written as raw PHP rather than `@if` so that the arms compile to a
     * `switch` verbatim. Blaze folds the whole thing away when both props are
     * static, which they are at almost every call site.
     *
     * @param  array<string, string>  $cells
     */
    protected function switch(IconSet $set, IconSource $source, array $cells): string
    {
        $fallthrough = $cells[$set->styleFor($set->defaultSize()).':'.$set->defaultSize()]
            ?? (string) reset($cells);

        $grouped = [];

        foreach ($cells as $cell => $path) {
            if ($path !== $fallthrough) {
                $grouped[$path][] = $cell;
            }
        }

        $out = [];

        foreach ($grouped as $path => $group) {
            $labels = implode(' ', array_map(
                fn (string $cell): string => "case ('{$cell}'):",
                $group,
            ));

            $out[] = ($out === []
                ? "<?php switch (\$variant.':'.\$size): {$labels} ?>"
                : "<?php break; {$labels} ?>")
                ."\n".$this->svg($source->get((string) $path));
        }

        $out[] = "<?php break; default: ?>\n".$this->svg($source->get($fallthrough));

        return implode("\n", $out)."\n<?php endswitch; ?>";
    }

    /**
     * Rewrite one source SVG as the markup a component renders.
     *
     * The attributes that describe how the drawing is *used* are dropped —
     * `width`, `height`, `class`, `aria-hidden`, `data-slot` — because those are
     * this library's decisions and they arrive through the attribute bag. What
     * describes the drawing itself is kept, in the order the source states it.
     */
    protected function svg(string $source): string
    {
        preg_match('/<svg\b([^>]*)>(.*)<\/svg>/s', $source, $matches);

        $attributes = $this->attributes($matches[1] ?? '');
        $children = $this->children($matches[2] ?? '');

        $open = '<svg {{ $attributes->merge([\'aria-hidden\' => \'true\'])->class($classes) }} data-shape-icon'
            .($attributes === '' ? '' : ' '.$attributes).'>';

        return $open."\n".$children."\n</svg>";
    }

    /**
     * The source SVG's own attributes, minus the ones this library supplies.
     */
    protected function attributes(string $attributes): string
    {
        $dropped = ['width', 'height', 'class', 'aria-hidden', 'data-slot', 'focusable', 'role'];

        preg_match_all('/([\w:-]+)\s*=\s*"([^"]*)"/', $attributes, $matches, PREG_SET_ORDER);

        $kept = [];

        foreach ($matches as $match) {
            if (in_array(strtolower($match[1]), $dropped, true)) {
                continue;
            }

            $kept[] = $match[1].'="'.$match[2].'"';
        }

        return implode(' ', $kept);
    }

    /**
     * The elements inside the SVG, one per line, indented.
     *
     * Each element keeps the source's own attribute order and spelling; only the
     * whitespace between attributes is normalised, so that a source file with a
     * path broken across three lines produces the same component as one without.
     */
    protected function children(string $children): string
    {
        preg_match_all('/<([\w:-]+)\b[^>]*?\/?>/s', $children, $matches);

        return implode("\n", array_map(function (string $element): string {
            $element = (string) preg_replace('/\s+/', ' ', trim($element));

            return '    '.(string) preg_replace('/\s*\/>$/', '/>', $element);
        }, $matches[0]));
    }

    /**
     * The icons to generate, as Shape's names for them.
     *
     * Three ways to arrive at that list, and they differ in what has to be
     * translated. Names on the command line are already Shape's. `--replace` is
     * the list the library draws itself, derived from the markup so that a
     * component gaining an icon does not quietly leave a hole here. `--all` is
     * the odd one: what it finds are *files*, which are the set's names, and
     * they have to come back through the alias map before they can be written.
     *
     * @return list<string>
     */
    protected function names(IconSet $set, IconSource $source, Registry $registry): array
    {
        if ($this->option('replace')) {
            return $registry->icons();
        }

        if (! $this->option('all')) {
            /** @var list<string> $icons */
            $icons = (array) $this->argument('icons');

            return $icons;
        }

        $names = [];

        foreach ($set->directories() as $directory) {
            foreach ($source->names($directory) as $file) {
                // A file may answer to more than one of Shape's names, or —
                // when its own name is spoken for by an alias — to none.
                foreach ($set->canonicalNames($file) as $name) {
                    $names[] = $name;
                }
            }
        }

        $names = array_values(array_unique($names));

        sort($names);

        return $names;
    }

    /**
     * The set to read, and the layout to read it in.
     */
    protected function set(?string $name = null): IconSet
    {
        if ($name === null) {
            $option = $this->option('set');
            $name = is_string($option) && $option !== '' ? $option : 'heroicons';
        }

        $sets = config('shape.icon_sets');

        if (! is_array($sets) || ! array_key_exists($name, $sets)) {
            throw new InvalidArgumentException("No icon set named [{$name}] is configured.");
        }

        // Two keys, because the two axes belong to different people: the scale
        // is the library's and the drawings are the set's.
        return IconSet::fromArray($name, $sets[$name], config('shape.icon_sizes'));
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

        if ($set->repo === null) {
            throw new InvalidArgumentException("Icon set [{$set->name}] says nothing about where it is drawn, so there is nothing to fetch. Pass --from with the directory to read SVGs from, or give the set a [repo].");
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
            (bool) $this->option('all') || (bool) $this->option('replace'),
        );
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
     * The subdirectory this set is written into, if it asked for one.
     *
     * Two sets generated into `icon/` share one namespace, and the second one
     * to spell `check` is refused rather than allowed to overwrite the first.
     * A subdirectory is the way to keep both: `icon/lucide/bell.blade.php` is
     * `<x-shape::icon.lucide.bell />`, and a flat `bell` is no longer in its
     * way.
     *
     * One kebab-case segment and nothing else. This is the only option that
     * can put a file outside the components path — `--namespace=../..` would —
     * so it is the one that has to be checked rather than trusted.
     */
    protected function namespace(): ?string
    {
        $namespace = $this->option('namespace');

        if (! is_string($namespace) || $namespace === '') {
            return null;
        }

        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $namespace) !== 1) {
            throw new InvalidArgumentException("[{$namespace}] is not a namespace. Pass one lower-case segment, like --namespace=lucide.");
        }

        return $namespace;
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
}
