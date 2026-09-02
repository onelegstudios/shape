<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;
use Onelegstudios\Shape\Icons\IconSource;
use Onelegstudios\Shape\IconSet;
use Onelegstudios\Shape\IconSlots;

/**
 * Turn a list of names into components, and record what each was drawn from.
 *
 * Everything from the list onwards is the same work whichever command asked for
 * it: read the cells, assemble the component, write it, pin it. What differs is
 * the list, and that is the one thing this asks the command for.
 */
trait GeneratesIcons
{
    /**
     * The names to write components under.
     *
     * The question each of the three writing commands exists to answer
     * differently, which is why it is asked rather than switched on.
     *
     * @return list<string>
     */
    abstract protected function names(IconSet $set, IconSlots $slots, IconSource $source): array;

    /**
     * Write one component per name, and record what each was drawn from.
     */
    protected function generate(IconSet $set, IconSlots $slots, IconSource $source, Filesystem $files, string $to): int
    {
        $names = $this->names($set, $slots, $source);

        if ($names === []) {
            $this->components->error('Name at least one icon, or run shape:icon:all.');

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

        $origin = $set->repo ?? $set->npm;

        // A commit is abbreviated because forty characters says nothing a
        // reader can hold; a version is already the short form of itself.
        $stamp = $revision === null || $origin === null
            ? ''
            : $origin.'@'.(preg_match('/^[0-9a-f]{40}$/', $revision) === 1 ? substr($revision, 0, 12) : $revision).'.';

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
     * Record the set, the ref, the resolved commit, and a digest per icon.
     *
     * Written beside the components, the way `shape:eject` writes
     * `shape-eject.json` beside the ones it copies. Keyed by set, because two
     * sets can legitimately write into one directory — a primary one flat and a
     * supplementary one namespaced — and each is pinned to its own upstream.
     *
     * @param  array<string, array{repo?: string, ref?: string, commit?: string, npm?: string, version?: string, icons: array<string, string>}>  $lock
     */
    protected function pin(IconSet $set, IconSource $source, Filesystem $files, string $to, array $lock): void
    {
        // Only when the run actually used the set's upstream. A `--from`
        // directory that happens to belong to a set with a `repo` was not
        // fetched from it, and recording otherwise would pin a component to a
        // commit nobody read it at.
        $record = $this->fetched()
            ? array_filter($set->npm !== null
                ? ['npm' => $set->npm, 'version' => $source->revision() ?? $this->version($set)]
                : ['repo' => $set->repo, 'ref' => $this->ref($set), 'commit' => $source->revision()],
                fn (?string $value): bool => $value !== null && $value !== '')
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
}
