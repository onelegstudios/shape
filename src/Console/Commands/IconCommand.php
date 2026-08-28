<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Turn a directory of SVGs into icon components.
 *
 * Every icon in this library is a generated file: four drawings, one per size,
 * behind a `variant` prop. Generating them is what makes the set cheap to grow
 * and what keeps the header on each file — "Regenerate; don't hand-edit" — an
 * honest instruction rather than a hope.
 *
 * The alternative, which WireUI takes, is a Composer package per icon set. That
 * is a version matrix to maintain for what is fundamentally a code generator,
 * and it puts the set a consumer actually wants — theirs — furthest out of
 * reach. This reads whatever directory it is pointed at.
 *
 * Two layouts are understood: Heroicons' own, where a name exists at four sizes
 * in `16/solid`, `20/solid`, `24/solid` and `24/outline`, and a flat directory
 * of SVGs, where each name is a single drawing and the component has no variants
 * to switch between.
 */
class IconCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'shape:icon
        {icons?* : The icon names to generate}
        {--from= : The directory to read SVGs from}
        {--to= : Where to write the components}
        {--all : Generate every icon in the source directory}
        {--force : Overwrite icons that already exist}';

    /**
     * The command description.
     */
    protected $description = 'Generate Shape icon components from a directory of SVGs.';

    /**
     * Where each variant is drawn, in the layout Heroicons ships.
     *
     * The order is the order the arms are written in, and `outline` is last
     * because it is the default — the arm a `switch` falls through to.
     *
     * @var array<string, string>
     */
    protected array $variants = [
        'micro' => '16/solid',
        'mini' => '20/solid',
        'solid' => '24/solid',
        'outline' => '24/outline',
    ];

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        $from = $this->source();

        if ($from === null) {
            $this->components->error('Pass --from with the directory to read SVGs from.');

            return self::FAILURE;
        }

        $to = $this->destination();

        $names = $this->names($files, $from);

        if ($names === []) {
            $this->components->error('Name at least one icon, or pass --all.');

            return self::FAILURE;
        }

        $written = 0;

        foreach ($names as $name) {
            $drawings = $this->drawings($files, $from, $name);

            if ($drawings === []) {
                $this->components->twoColumnDetail("  {$name}", '<fg=red>no SVG found</>');

                continue;
            }

            $target = $to.'/'.$name.'.blade.php';

            if ($files->exists($target) && ! $this->option('force')) {
                $this->components->twoColumnDetail("  {$name}", '<fg=yellow>exists, kept</>');

                continue;
            }

            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $this->component($drawings));

            $written++;

            $this->components->twoColumnDetail("  {$name}", '<fg=green>'.implode(', ', array_keys($drawings)).'</>');
        }

        $this->newLine();
        $this->components->info("{$written} icon(s) written to {$to}.");

        return self::SUCCESS;
    }

    /**
     * Assemble the component around however many drawings there are.
     *
     * One drawing needs no `switch`: a set with a single style has no variants
     * to choose between, and a component that emits a `switch` with one arm is
     * asking the reader to work out that it never branches.
     *
     * @param  array<string, string>  $drawings
     */
    protected function component(array $drawings): string
    {
        $svgs = array_map(fn (string $svg): string => $this->svg($svg), $drawings);

        $body = count($svgs) === 1
            ? reset($svgs)
            : $this->switch($svgs);

        return <<<BLADE
        @blaze(fold: true, memo: true)

        {{-- Heroicons (https://heroicons.com), MIT licensed. Regenerate; don't hand-edit. --}}

        @props([
            'variant' => 'outline',
        ])

        @php
        \$classes = Shape::classes('shrink-0')
            ->add(match (\$variant) {
                'micro' => '[:where(&)]:size-4',
                'mini' => '[:where(&)]:size-5',
                default => '[:where(&)]:size-6',
            });
        @endphp

        {$body}

        BLADE;
    }

    /**
     * The variant arms, with the last drawing as the default.
     *
     * Written as raw PHP rather than `@if` so that the arms are compiled to a
     * `switch` verbatim. Blaze folds the whole thing away when the variant is
     * static, which it is at almost every call site.
     *
     * @param  array<string, string>  $svgs
     */
    protected function switch(array $svgs): string
    {
        $default = array_key_last($svgs);
        $out = [];

        foreach ($svgs as $variant => $svg) {
            $out[] = $variant === $default
                ? "<?php break; default: ?>\n{$svg}"
                : ($out === []
                    ? "<?php switch (\$variant): case ('{$variant}'): ?>\n{$svg}"
                    : "<?php break; case ('{$variant}'): ?>\n{$svg}");
        }

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
     * Read whichever variants of an icon the source directory holds.
     *
     * @return array<string, string>
     */
    protected function drawings(Filesystem $files, string $from, string $name): array
    {
        $drawings = [];

        foreach ($this->variants as $variant => $directory) {
            $path = "{$from}/{$directory}/{$name}.svg";

            if ($files->exists($path)) {
                $drawings[$variant] = $files->get($path);
            }
        }

        if ($drawings === [] && $files->exists("{$from}/{$name}.svg")) {
            $drawings['outline'] = $files->get("{$from}/{$name}.svg");
        }

        return $drawings;
    }

    /**
     * The icons to generate.
     *
     * @return list<string>
     */
    protected function names(Filesystem $files, string $from): array
    {
        if (! $this->option('all')) {
            /** @var list<string> $icons */
            $icons = (array) $this->argument('icons');

            return $icons;
        }

        $names = [];

        foreach ([...array_values($this->variants), ''] as $directory) {
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
