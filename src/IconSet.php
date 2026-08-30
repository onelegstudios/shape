<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use InvalidArgumentException;

/**
 * One icon set, as a matrix of styles against sizes.
 *
 * Every set is that matrix, and most of them are sparse. Lucide is one style at
 * one size. Phosphor is six styles at one size. Heroicons is two styles at three
 * sizes with three cells empty, because it draws no outline at 16px or 20px — a
 * 1.5px stroke does not read that small. Modelling the matrix and letting a set
 * declare which cells are real covers all three without a special case for any
 * of them; a cell with no drawing borrows the largest one its style has.
 *
 * One axis of that matrix belongs to the library rather than to the set. The
 * size scale is declared once, in `shape.icon_sizes`, and every set is measured
 * against it — otherwise `<x-shape::icon.bell size="sm" />` and
 * `<x-shape::icon.check size="sm" />` could render at different sizes because
 * the icons came from different sets, which is exactly what a supplementary set
 * invites. A set says only which cells it draws.
 *
 * This is a compile-time thing. `shape:icon` reads it while writing a component
 * and bakes every value it decides — the size class, the preferred style, the
 * case labels — into that file as a literal. Nothing here is read at run time,
 * which is what keeps the generated components foldable.
 *
 * Parsed once, here, so that the command deals in typed values rather than in
 * whatever shape `config('shape.icon_sets')` happened to be left in.
 */
final class IconSet
{
    /**
     * Both are non-empty: `fromArray` refuses a set that draws nothing and a
     * scale that measures nothing, so the largest size and the first style are
     * always there to be reached for.
     *
     * @param  non-empty-array<string, array{class: string, prefer?: string}>  $sizes  The library's scale, in ascending order; the last is the default.
     * @param  non-empty-array<string, array<string, string>>  $styles  Style, then the sizes it draws its own glyph at.
     */
    private function __construct(
        public readonly string $name,
        public readonly string $notice,
        private readonly array $sizes,
        private readonly array $styles,
    ) {}

    /**
     * Read one set out of whatever the config file holds.
     *
     * The scale arrives separately from the set because it is the library's,
     * not the set's: `shape.icon_sizes` against one entry of `shape.icon_sets`.
     *
     * Everything is checked rather than assumed: both halves can come from a
     * consumer's published config, so a malformed one has to say what is wrong
     * with it instead of surfacing three methods later as a type error.
     */
    public static function fromArray(string $name, mixed $definition, mixed $sizes): self
    {
        if (! is_array($definition)) {
            throw new InvalidArgumentException("Icon set [{$name}] is not an array.");
        }

        // A config published before the scale was hoisted has this key, and no
        // `icon_sizes` to go with it. Saying so is worth more than ignoring it:
        // a set that quietly keeps its own scale is the bug this key's removal
        // exists to prevent.
        if (array_key_exists('sizes', $definition)) {
            throw new InvalidArgumentException("Icon set [{$name}] declares its own [sizes]. The size scale belongs to the library now — move it to [icon_sizes] in config/shape.php.");
        }

        $scale = self::scale($sizes);
        $styles = [];

        foreach ((array) ($definition['styles'] ?? []) as $style => $patterns) {
            if (! is_string($style) || ! is_array($patterns)) {
                throw new InvalidArgumentException("Icon set [{$name}] has a style without any drawings.");
            }

            foreach ($patterns as $size => $pattern) {
                if (! is_string($size) || ! is_string($pattern)) {
                    throw new InvalidArgumentException("Icon set [{$name}] has a drawing without a path.");
                }

                if (! isset($scale[$size])) {
                    throw new InvalidArgumentException("Icon set [{$name}] draws [{$style}] at [{$size}], which is not one of the library's sizes.");
                }

                $styles[$style][$size] = $pattern;
            }
        }

        if ($styles === []) {
            throw new InvalidArgumentException("Icon set [{$name}] needs at least one style.");
        }

        $notice = $definition['notice'] ?? null;

        return new self($name, is_string($notice) ? $notice : '', $scale, $styles);
    }

    /**
     * The library's size scale, read out of whatever the config file holds.
     *
     * @return non-empty-array<string, array{class: string, prefer?: string}>
     */
    private static function scale(mixed $sizes): array
    {
        if (! is_array($sizes) || $sizes === []) {
            throw new InvalidArgumentException('No icon sizes are configured. Add [icon_sizes] to config/shape.php — the size scale moved there out of the individual sets.');
        }

        $scale = [];

        foreach ($sizes as $size => $spec) {
            if (! is_string($size) || ! is_array($spec) || ! isset($spec['class']) || ! is_string($spec['class'])) {
                throw new InvalidArgumentException('The icon sizes hold a size without a class.');
            }

            $scale[$size] = isset($spec['prefer']) && is_string($spec['prefer'])
                ? ['class' => $spec['class'], 'prefer' => $spec['prefer']]
                : ['class' => $spec['class']];
        }

        return $scale;
    }

    /**
     * The size scale, smallest first.
     *
     * The library's, not this set's: two sets generate the same size match, so
     * one word at a call site means one size whichever set answered it.
     *
     * @return non-empty-list<string>
     */
    public function sizes(): array
    {
        return array_keys($this->sizes);
    }

    /**
     * @return non-empty-list<string>
     */
    public function styles(): array
    {
        return array_keys($this->styles);
    }

    /**
     * The size a call site gets when it names none.
     *
     * The largest, which is the last declared — `base` everywhere else in this
     * library, and the same word here.
     */
    public function defaultSize(): string
    {
        $sizes = $this->sizes();

        return $sizes[array_key_last($sizes)];
    }

    /**
     * The style a size reaches for when the call site names none.
     *
     * This is the whole reason the two axes are worth separating. Eleven of the
     * twelve places this library renders an icon want a solid drawing at a small
     * size, and one — the empty state — wants an outline at a large one. Stated
     * once on the scale, those call sites ask for a size and nothing else, and a
     * set that has no such style answers with the one it does have: a preference
     * the set cannot honour falls through rather than failing.
     */
    public function styleFor(string $size): string
    {
        $prefer = $this->sizes[$size]['prefer'] ?? null;

        if (is_string($prefer) && isset($this->styles[$prefer])) {
            return $prefer;
        }

        $default = $this->sizes[$this->defaultSize()]['prefer'] ?? null;

        return is_string($default) && isset($this->styles[$default])
            ? $default
            : $this->styles()[0];
    }

    /**
     * The utility that sizes a drawing, wrapped the way this library wraps every
     * class a consumer is meant to be able to override.
     */
    public function classFor(string $size): string
    {
        return '[:where(&)]:'.$this->sizes[$size]['class'];
    }

    /**
     * Where one cell of the matrix is drawn, relative to the source directory.
     *
     * A style that has no drawing of its own at this size borrows its largest,
     * which is then scaled down by the size class. Never scaled up: the fallback
     * is the largest declared rather than the nearest, because a 16px glyph
     * stretched to 24px looks like a mistake and a 24px one shrunk to 16px looks
     * like a smaller icon.
     */
    public function pattern(string $style, string $size): ?string
    {
        $drawn = $this->styles[$style] ?? [];

        if (isset($drawn[$size])) {
            return $drawn[$size];
        }

        $largest = null;

        foreach ($this->sizes() as $candidate) {
            if (isset($drawn[$candidate])) {
                $largest = $drawn[$candidate];
            }
        }

        return $largest;
    }

    /**
     * Every directory a name could be drawn in, for `--all` to walk.
     *
     * Derived from the patterns rather than declared, so a flat set — whose only
     * pattern is `{name}.svg` — resolves to the source directory itself without
     * needing a case of its own.
     *
     * @return list<string>
     */
    public function directories(): array
    {
        $directories = [];

        foreach ($this->styles as $drawn) {
            foreach ($drawn as $pattern) {
                $directory = trim(dirname($pattern), '.');

                $directories[] = trim($directory, '/');
            }
        }

        return array_values(array_unique($directories));
    }

    /**
     * Whether a call site can meaningfully ask for a style.
     *
     * A set with one style still declares the `variant` prop, so that a `variant`
     * arriving from a shared call site is ignored rather than falling through to
     * the attribute bag and rendering on the `<svg>`.
     */
    public function hasOneStyle(): bool
    {
        return count($this->styles) === 1;
    }
}
