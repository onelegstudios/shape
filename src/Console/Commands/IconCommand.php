<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Onelegstudios\Shape\IconSet;

/**
 * Turn a directory of SVGs into icon components.
 *
 * Every icon in this library is a generated file, and generating them is what
 * makes the set cheap to grow and what keeps the header on each one —
 * "Regenerate; don't hand-edit" — an honest instruction rather than a hope.
 *
 * What a set looks like is declared in `shape.icon_sets` and parsed by
 * `IconSet`: a matrix of styles against sizes, mostly sparse. This command is
 * the part that walks that matrix, reads whichever cells the source directory
 * actually holds, and writes one component per name with the answers baked in.
 *
 * The alternative, which WireUI takes, is a Composer package per icon set. That
 * is a version matrix to maintain for what is fundamentally a code generator,
 * and it puts the set a consumer actually wants — theirs — furthest out of
 * reach. This reads whatever directory it is pointed at, in whatever layout the
 * manifest describes.
 */
class IconCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'shape:icon
        {icons?* : The icon names to generate}
        {--set=heroicons : The icon set the source directory holds}
        {--from= : The directory to read SVGs from}
        {--to= : Where to write the components}
        {--all : Generate every icon in the source directory}
        {--force : Overwrite icons that already exist}';

    /**
     * The command description.
     */
    protected $description = 'Generate Shape icon components from a directory of SVGs.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        try {
            $set = $this->set();
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $from = $this->source();

        if ($from === null) {
            $this->components->error('Pass --from with the directory to read SVGs from.');

            return self::FAILURE;
        }

        $to = $this->destination();

        $names = $this->names($set, $files, $from);

        if ($names === []) {
            $this->components->error('Name at least one icon, or pass --all.');

            return self::FAILURE;
        }

        $written = 0;

        foreach ($names as $name) {
            $cells = $this->matrix($set, $files, $from, $name);

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
            $files->put($target, $this->component($set, $files, $cells));

            $written++;

            $this->components->twoColumnDetail("  {$name}", '<fg=green>'.count(array_unique($cells)).' drawing(s)</>');
        }

        $this->newLine();
        $this->components->info("{$written} icon(s) written to {$to}.");

        return self::SUCCESS;
    }

    /**
     * Which cell of the matrix is drawn where, for one name.
     *
     * Keyed the way the generated component switches on it, so that assembling
     * the arms afterwards is a grouping and nothing more.
     *
     * @return array<string, string>
     */
    protected function matrix(IconSet $set, Filesystem $files, string $from, string $name): array
    {
        $cells = [];

        foreach ($set->styles() as $style) {
            foreach ($set->sizes() as $size) {
                $pattern = $set->pattern($style, $size);

                if ($pattern === null) {
                    continue;
                }

                $path = $from.'/'.str_replace('{name}', $name, $pattern);

                if ($files->exists($path)) {
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
    protected function component(IconSet $set, Filesystem $files, array $cells): string
    {
        $body = count(array_unique($cells)) === 1
            ? $this->svg($files->get((string) reset($cells)))
            : $this->switch($set, $files, $cells);

        $notice = $set->notice === '' ? '' : $set->notice.' ';

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
    protected function switch(IconSet $set, Filesystem $files, array $cells): string
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
                ."\n".$this->svg($files->get($path));
        }

        $out[] = "<?php break; default: ?>\n".$this->svg($files->get($fallthrough));

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
     * The icons to generate.
     *
     * @return list<string>
     */
    protected function names(IconSet $set, Filesystem $files, string $from): array
    {
        if (! $this->option('all')) {
            /** @var list<string> $icons */
            $icons = (array) $this->argument('icons');

            return $icons;
        }

        $names = [];

        foreach ($set->directories() as $directory) {
            $path = rtrim("{$from}/{$directory}", '/');

            if (! $files->isDirectory($path)) {
                continue;
            }

            foreach ($files->files($path) as $file) {
                if ($file->getExtension() === 'svg') {
                    $names[] = $file->getFilenameWithoutExtension();
                }
            }
        }

        $names = array_values(array_unique($names));

        sort($names);

        return $names;
    }

    /**
     * The set the source directory is laid out in.
     */
    protected function set(): IconSet
    {
        $name = $this->option('set');
        $name = is_string($name) && $name !== '' ? $name : 'heroicons';

        $sets = config('shape.icon_sets');

        if (! is_array($sets) || ! array_key_exists($name, $sets)) {
            throw new InvalidArgumentException("No icon set named [{$name}] is configured.");
        }

        return IconSet::fromArray($name, $sets[$name]);
    }

    protected function source(): ?string
    {
        $from = $this->option('from');

        return is_string($from) && $from !== '' ? rtrim($from, '/') : null;
    }

    protected function destination(): string
    {
        $to = $this->option('to');

        if (is_string($to) && $to !== '') {
            return rtrim($to, '/');
        }

        $path = config('shape.components_path');

        return (is_string($path) ? rtrim($path, '/') : resource_path('views/shape')).'/icon';
    }
}
