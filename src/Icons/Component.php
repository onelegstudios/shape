<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Icons;

use Onelegstudios\Shape\IconSet;
use Onelegstudios\Shape\IconSlots;

/**
 * One generated icon component, as a string of Blade.
 *
 * The other classes here answer for bytes coming in — a directory, a
 * repository, a published package. This is the other half of the same subject:
 * given a set, its slots, a source to read drawings from, and which cells a
 * name resolved to, it answers for the file going out.
 *
 * It reads no option and prints nothing, which is why it is not on the command.
 * Deciding *which* drawings a name resolves to stays there; turning them into a
 * component is this, and neither knows the other's job.
 */
final class Component
{
    public function __construct(
        private readonly IconSet $set,
        private readonly IconSlots $slots,
        private readonly IconSource $source,
    ) {}

    /**
     * Assemble the component around however many distinct drawings there are.
     *
     * One drawing needs no `switch`: a set with a single style drawn at a single
     * size has nothing to choose between, and a component that emits a `switch`
     * with one arm is asking the reader to work out that it never branches.
     *
     * @param  array<string, string>  $cells
     */
    public function render(string $name, array $cells): string
    {
        $body = count(array_unique($cells)) === 1
            ? $this->svg($this->source->get((string) reset($cells)))
            : $this->switch($cells);

        $notice = $this->header();

        // `shrink-0` is every icon's, and a slot may add to it. The spin on
        // `shape-loading` belongs to the slot rather than to the set that drew
        // it — every set's loader spins — so it is declared once in
        // `shape.icon_slots` and baked in here, like everything else.
        $classes = $this->slots->classFor($name);

        return <<<BLADE
        @blaze(fold: true, memo: true)

        {{-- {$notice}Regenerate; don't hand-edit. --}}

        @props([
        {$this->props()}
        ])

        @php
        {$this->resolution()}\$classes = Shape::classes('{$classes}')
            ->add(match (\$size) {
        {$this->classes()}
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
    private function header(): string
    {
        $revision = $this->source->revision();

        $origin = $this->set->repo ?? $this->set->npm;

        // A commit is abbreviated because forty characters says nothing a
        // reader can hold; a version is already the short form of itself.
        $stamp = $revision === null || $origin === null
            ? ''
            : $origin.'@'.(preg_match('/^[0-9a-f]{40}$/', $revision) === 1 ? substr($revision, 0, 12) : $revision).'.';

        $notice = trim($this->set->notice.' '.$stamp);

        return $notice === '' ? '' : $notice.' ';
    }

    /**
     * The props, which are the two axes and nothing else.
     *
     * A set with one style still declares `variant`, so that a `variant` passed
     * by a shared call site is ignored rather than falling through to the
     * attribute bag and rendering itself on the `<svg>`.
     */
    private function props(): string
    {
        $variant = $this->set->hasOneStyle()
            ? "'".$this->set->styles()[0]."'"
            : 'null';

        return implode("\n", [
            "    'variant' => {$variant},",
            "    'size' => '".$this->set->defaultSize()."',",
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
    private function resolution(): string
    {
        if ($this->set->hasOneStyle()) {
            return '';
        }

        $default = $this->set->styleFor($this->set->defaultSize());

        $grouped = [];

        foreach ($this->set->sizes() as $size) {
            $style = $this->set->styleFor($size);

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
     * The arms of the size match, with the scale's default size as the default.
     *
     * The named arms come in the scale's own order and the `default` goes last,
     * wherever in the scale the size it stands for sits. A `match` reads as a
     * list with a fallthrough at the end of it, and `base` is the middle of
     * `xs` to `xl` — writing the default in the middle would put two things in
     * the reader's way at once.
     */
    private function classes(): string
    {
        $default = $this->set->defaultSize();

        $arms = [];

        foreach ($this->set->sizes() as $size) {
            if ($size === $default) {
                continue;
            }

            $arms[] = "        '{$size}' => '".$this->set->classFor($size)."',";
        }

        $arms[] = "        default => '".$this->set->classFor($default)."',";

        return implode("\n", $arms);
    }

    /**
     * The drawing arms, with the default cell's drawing as the fallthrough.
     *
     * Every cell that resolved to the same file shares an arm, which is why
     * Heroicons — ten cells over four drawings, since `lg` and `xl` borrow the
     * 24px pair — writes three cases and a default rather than ten of anything.
     *
     * Written as raw PHP rather than `@if` so that the arms compile to a
     * `switch` verbatim. Blaze folds the whole thing away when both props are
     * static, which they are at almost every call site.
     *
     * @param  array<string, string>  $cells
     */
    private function switch(array $cells): string
    {
        $fallthrough = $cells[$this->set->styleFor($this->set->defaultSize()).':'.$this->set->defaultSize()]
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
                ."\n".$this->svg($this->source->get((string) $path));
        }

        $out[] = "<?php break; default: ?>\n".$this->svg($this->source->get($fallthrough));

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
    private function svg(string $drawing): string
    {
        preg_match('/<svg\b([^>]*)>(.*)<\/svg>/s', $drawing, $matches);

        $attributes = $this->attributes($matches[1] ?? '');
        $children = $this->children($matches[2] ?? '');

        $open = '<svg {{ $attributes->merge([\'aria-hidden\' => \'true\'])->class($classes) }} data-shape-icon'
            .($attributes === '' ? '' : ' '.$attributes).'>';

        return $open."\n".$children."\n</svg>";
    }

    /**
     * The source SVG's own attributes, minus the ones this library supplies.
     */
    private function attributes(string $attributes): string
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
    private function children(string $children): string
    {
        preg_match_all('/<([\w:-]+)\b[^>]*?\/?>/s', $children, $matches);

        return implode("\n", array_map(function (string $element): string {
            $element = (string) preg_replace('/\s+/', ' ', trim($element));

            return '    '.(string) preg_replace('/\s*\/>$/', '/>', $element);
        }, $matches[0]));
    }
}
