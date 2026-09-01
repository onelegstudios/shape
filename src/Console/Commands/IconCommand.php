<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Onelegstudios\Shape\Console\Commands\Concerns\ClearsCompiledViews;
use Onelegstudios\Shape\Icons\DirectorySource;
use Onelegstudios\Shape\Icons\GitHubSource;
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
 * to overwrite the first; a subdirectory gives each set its own. It is not the
 * default, and shouldn't be: the primary set stays flat so that a call site has
 * one spelling for an icon whichever set drew it.
 *
 * Which subdirectory is the set's own answer, declared as `namespace` in
 * `shape.icon_sets` beside everything else that is true of it. A flag alone was
 * not enough: it is remembered only for the run it is typed on, so the next run
 * without it wrote a second copy flat and pinned it in a second lockfile —
 * exactly the collision the subdirectory existed to prevent. `--namespace`
 * survives as the override for a one-off run, and `--namespace=` as the way to
 * say flat out loud.
 *
 * Which set is read at all is the same kind of fact, and is declared the same
 * way: `shape.icon_set` names it, so an application that has moved the library
 * onto Lucide says so once instead of on every run it ever types. `--set` is
 * the override for the run that means it.
 */
class IconCommand extends Command
{
    use ClearsCompiledViews;

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
    public function handle(Filesystem $files): int
    {
        try {
            $set = $this->set();
            $slots = IconSlots::fromConfig();
            $namespace = $this->namespace($set);
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
            // `shape::icon.shape-close`, and a file under `icon/lucide/`
            // answers to `shape::icon.lucide.shape-close`. The run would write
            // fourteen files and change nothing.
            $this->components->error("--replace writes over the names Shape draws, which are flat, and [{$set->name}] is written into [icon/{$namespace}/]. Pass --namespace= to write this run flat, or drop --replace.");

            return self::FAILURE;
        }

        // `--all` is a listing, and a listing is the one thing a drawing-at-a-
        // time source cannot produce. Said here, before anything is fetched,
        // because the alternative is what this used to do: pull a repository
        // measured in gigabytes and be killed unpacking it, with a stack trace
        // where the reason should be. A local checkout still lists fine, so
        // `--from` is the way to have this run anyway.
        if ($this->option('all') && ! $set->archive && $this->fetched()) {
            $this->components->error("Icon set [{$set->name}] is read one drawing at a time, because its repository is too large to fetch whole — so there is no listing for --all to walk. Name the icons you want, use --replace for Shape's own, or pass --from with a local checkout.");

            return self::FAILURE;
        }

        try {
            $source = $this->source($set, $files);

            return $this->generate($set, $slots, $source, $files, $this->destination($namespace));
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Write one component per name, and record what each was drawn from.
     */
    protected function generate(IconSet $set, IconSlots $slots, IconSource $source, Filesystem $files, string $to): int
    {
        $names = $this->names($set, $slots, $source);

        if ($names === []) {
            $this->components->error('Name at least one icon, or pass --all.');

            return self::FAILURE;
        }

        $lock = $this->lock($files, $to);
        $written = 0;
        $unfilled = [];

        foreach ($names as $name) {
            $cells = $this->matrix($set, $source, $name);

            if ($cells === []) {
                [$report, $error] = $this->unfilled($set, $slots, $name);

                $this->components->twoColumnDetail("  {$name}", $report);

                if ($error !== null) {
                    $unfilled[$name] = $error;
                }

                continue;
            }

            $target = $to.'/'.$name.'.blade.php';

            if ($files->exists($target) && ! $this->option('force')) {
                $this->components->twoColumnDetail("  {$name}", '<fg=yellow>exists, kept</>');

                continue;
            }

            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $this->component($set, $slots, $source, $name, $cells));

            $lock[$set->name]['icons'][$name] = $this->digest($source, $cells);

            $written++;

            $this->components->twoColumnDetail("  {$name}", '<fg=green>'.count(array_unique($cells)).' drawing(s)</>');
        }

        if ($written > 0) {
            $this->pin($set, $source, $files, $to, $lock);
        }

        $this->newLine();
        $this->components->info("{$written} icon(s) written to {$to}.");

        if ($written > 0) {
            $this->clearCompiledViews();
        }

        if ($unfilled === []) {
            return self::SUCCESS;
        }

        foreach ($unfilled as $message) {
            $this->components->error($message);
        }

        return self::FAILURE;
    }

    /**
     * What to say about a name that produced no drawing, and whether to fail.
     *
     * An ordinary name reads as it always did: `shape:icon bicycle` asking for a
     * drawing the set has not got is a typo, reported and shrugged off, because
     * nothing in the library was depending on it.
     *
     * A slot is not that. Every one of them is resolved by a component, so a
     * slot this run could not fill is a component that goes on drawing the
     * packaged Heroicon on a page otherwise wearing somebody else's set — the
     * silence `shape:doctor` exists for, arriving one step earlier. The error
     * names the set and the config key, because the answer to most of these is
     * an entry in `slots` rather than anything to do with this command.
     *
     * The exception is a packaged slot. Shape draws `shape-loading` itself, so a
     * set with nothing to fill it has answered correctly and the fallback is the
     * designed outcome.
     *
     * @return array{0: string, 1: string|null}
     */
    protected function unfilled(IconSet $set, IconSlots $slots, string $name): array
    {
        if (! $slots->has($name)) {
            return ['<fg=red>no SVG found</>', null];
        }

        $key = "shape.icon_sets.{$set->name}.slots";

        if ($slots->isPackaged($name)) {
            return ['<fg=gray>packaged by Shape</>', null];
        }

        if (! $set->declares($name)) {
            return [
                '<fg=red>unfilled</>',
                "Icon set [{$set->name}] says nothing about slot [{$name}], so nothing was written for it and the component that resolves it keeps the icon this package ships. Name the drawing that fills it in [{$key}], or say null there if this set has none.",
            ];
        }

        $drawn = $set->sourceName($name);

        if ($drawn === null) {
            return [
                '<fg=yellow>no drawing in ['.$set->name.']</>',
                null,
            ];
        }

        return [
            '<fg=red>no SVG found</>',
            "Icon set [{$set->name}] fills slot [{$name}] from [{$drawn}], and there is no such drawing. Correct it in [{$key}].",
        ];
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
     *
     * Every directory that holds a lockfile is reported, not only the one this
     * run resolved to. A set in a subdirectory keeps its own lock beside its own
     * components, and a status that read one lockfile would answer "nothing has
     * been generated" for a set sitting right there — the one answer worse than
     * no answer, because it reads like a clean bill of health.
     */
    protected function status(Filesystem $files, string $to): int
    {
        $directories = $this->recorded($files, $to);
        $locks = [];

        foreach ($directories as $directory) {
            $lock = $this->lock($files, $directory);

            if ($lock !== []) {
                $locks[$directory] = $lock;
            }
        }

        if ($locks === []) {
            $this->components->info("No icons have been generated into {$to}.");

            return self::SUCCESS;
        }

        $stale = 0;

        // Named whenever a reader could be in any doubt about which directory a
        // row belongs to — several of them, or one that is not the destination
        // this run resolved to. A run that reports on exactly where it was
        // pointed reads as it always did.
        $named = count($locks) > 1 || array_key_first($locks) !== $to;

        foreach ($locks as $directory => $lock) {
            if ($named) {
                $this->newLine();
                $this->components->info("In {$directory}.");
            }

            $stale += $this->report($files, (string) $directory, $lock);
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
     * Report on one directory's worth of generated icons, and count the stale.
     *
     * @param  array<string, array{repo?: string, ref?: string, commit?: string, icons: array<string, string>}>  $lock
     */
    protected function report(Filesystem $files, string $to, array $lock): int
    {
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

        return $stale;
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
     * Assemble the component around however many distinct drawings there are.
     *
     * One drawing needs no `switch`: a set with a single style drawn at a single
     * size has nothing to choose between, and a component that emits a `switch`
     * with one arm is asking the reader to work out that it never branches.
     *
     * @param  array<string, string>  $cells
     */
    protected function component(IconSet $set, IconSlots $slots, IconSource $source, string $name, array $cells): string
    {
        $body = count(array_unique($cells)) === 1
            ? $this->svg($source->get((string) reset($cells)))
            : $this->switch($set, $source, $cells);

        $notice = $this->header($set, $source);

        // `shrink-0` is every icon's, and a slot may add to it. The spin on
        // `shape-loading` belongs to the slot rather than to the set that drew
        // it — every set's loader spins — so it is declared once in
        // `shape.icon_slots` and baked in here, like everything else.
        $classes = $slots->classFor($name);

        return <<<BLADE
        @blaze(fold: true, memo: true)

        {{-- {$notice}Regenerate; don't hand-edit. --}}

        @props([
        {$this->props($set)}
        ])

        @php
        {$this->resolution($set)}\$classes = Shape::classes('{$classes}')
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
     * The names to write components under.
     *
     * Three ways to arrive at that list. Names on the command line are taken as
     * given. `--replace` is the library's declared slots, which is what makes it
     * a complete answer: a slot no component happens to draw is still generated,
     * and `shape-loading` is exactly that.
     *
     * `--all` walks the set's own files and writes each under its own name,
     * flat. It used to have to run the alias map backwards, and the hairy case
     * was a file whose name was itself an alias key — a Heroicons-named
     * `check-circle.svg` sitting beside Lucide's `circle-check.svg` would have
     * shadowed the alias with the wrong glyph, so it was written under nothing.
     * Slots live in a namespace no set uses, so that case cannot arise and there
     * is nothing left to reverse.
     *
     * @return list<string>
     */
    protected function names(IconSet $set, IconSlots $slots, IconSource $source): array
    {
        if ($this->option('replace')) {
            return $slots->names();
        }

        if (! $this->option('all')) {
            /** @var list<string> $icons */
            $icons = (array) $this->argument('icons');

            return $icons;
        }

        $names = [];

        foreach ($set->directories() as $directory) {
            foreach ($source->names($directory) as $file) {
                // The set's own patterns decide what a listed file is called,
                // because a listing is filenames and a filename is not always a
                // name: Phosphor's `assets/fill/heart-fill.svg` is the fill
                // drawing of `heart`, and a file no pattern accounts for is not
                // this set's to write at all.
                $name = $set->nameFor($directory, $file);

                if ($name !== null) {
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
            //
            // A flattening set has no choice about it. Its drawings are filed
            // under something their names do not say — a category — so there is
            // no path a raw fetch could ask for until the archive is in hand and
            // collapsed, whether the run wanted one icon or all of them.
            //
            // A set whose repository cannot be pulled whole is the other way
            // round: `--replace` goes back to a drawing at a time, which is
            // twenty-eight requests against an archive PHP cannot hold.
            ($set->archive && ((bool) $this->option('all') || (bool) $this->option('replace'))) || $set->flatten,
            $set->flatten,
            $set->archive,
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
     * The subdirectory this set is written into, if it has one.
     *
     * Two sets generated into `icon/` share one namespace, and the second one
     * to spell `check` is refused rather than allowed to overwrite the first.
     * A subdirectory is the way to keep both: `icon/lucide/bell.blade.php` is
     * `<x-shape::icon.lucide.bell />`, and a flat `bell` is no longer in its
     * way.
     *
     * The set answers this, and the flag only overrides it. A flag on its own
     * is remembered by nobody: the run after it, without the flag, would write
     * a second copy flat and pin it in a second lockfile, which is the
     * collision the subdirectory was for. `--namespace=` with nothing after it
     * is the way to say flat out loud, for the one run that means it.
     *
     * Three states, so all three are distinguishable: the option absent is
     * null and defers to the set, the option empty is "flat", and anything
     * else is one kebab-case segment, checked — `--namespace=../..` is the one
     * way this can write outside the components path.
     */
    protected function namespace(IconSet $set): ?string
    {
        $namespace = $this->option('namespace');

        if ($namespace === null) {
            return $set->namespace;
        }

        if ($namespace === '') {
            return null;
        }

        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $namespace) !== 1) {
            throw new InvalidArgumentException("[{$namespace}] is not a namespace. Pass one lower-case segment, like --namespace=lucide.");
        }

        return $namespace;
    }

    /**
     * Every directory a generated set could be recorded in.
     *
     * `--status` is asked "what is stale", not "what is stale in this one
     * directory", and answering only for the destination this run resolved to
     * would have it report nothing at all for a set that lives in a
     * subdirectory — a silence indistinguishable from a clean bill of health.
     * Namespaces come out of the config rather than off the command line now,
     * so the set of places to look is knowable without scanning for it.
     *
     * @return list<string>
     */
    protected function recorded(Filesystem $files, string $to): array
    {
        $directories = [$to];

        if (is_string($this->option('namespace'))) {
            return $directories;
        }

        $sets = config('shape.icon_sets');

        foreach (is_array($sets) ? $sets : [] as $name => $definition) {
            try {
                $namespace = $this->set((string) $name)->namespace;
            } catch (InvalidArgumentException) {
                continue;
            }

            $directory = $this->destination($namespace);

            if ($namespace !== null && ! in_array($directory, $directories, true) && $files->exists($directory.'/'.$this->lockName())) {
                $directories[] = $directory;
            }
        }

        return $directories;
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
