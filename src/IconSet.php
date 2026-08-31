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
 * `<x-shape::icon.shape-plus size="sm" />` could render at different sizes
 * because the icons came from different sets, which is exactly what a
 * supplementary set invites. A set says only which cells it draws.
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
     * @param  array<string, string|null>  $slots  Shape's slots, against this set's own spelling of the drawing that fills each; null where the set has none.
     * @param  string  $ref  Only meaningful with a repo; ignored otherwise.
     * @param  string  $path  The subdirectory of the repository the drawings live in, if any.
     * @param  string|null  $namespace  The subdirectory of the components path this set is written into, if it wants one of its own.
     */
    private function __construct(
        public readonly string $name,
        public readonly string $notice,
        private readonly array $sizes,
        private readonly array $styles,
        private readonly array $slots = [],
        public readonly ?string $repo = null,
        public readonly string $ref = 'main',
        public readonly string $path = '',
        public readonly ?string $namespace = null,
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

        // And a config published before slots existed has this one. An alias
        // keyed `exclamation-triangle` says nothing about where the drawing is
        // used, and ignoring the key would leave the set covering no slot at
        // all — a `--replace` that writes nothing and reports success.
        if (array_key_exists('aliases', $definition)) {
            throw new InvalidArgumentException("Icon set [{$name}] declares [aliases]. Shape asks for slots now, not for another set's spellings — rewrite it as [slots], keyed by the slot names in [icon_slots].");
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

        $repo = self::text($name, $definition, 'repo');
        $ref = self::text($name, $definition, 'ref');
        $path = self::text($name, $definition, 'path');

        return new self(
            $name,
            is_string($notice) ? $notice : '',
            $scale,
            $styles,
            self::slots($name, $definition),
            $repo,
            $ref ?? 'main',
            trim($path ?? '', '/'),
            self::namespace($name, $definition),
        );
    }

    /**
     * The subdirectory this set is written into, if it declares one.
     *
     * Where a set lives is a property of the set, not of the run that generated
     * it. A flag alone is remembered by nobody: the next `shape:icon bell
     * --set=lucide` without it writes a second copy flat, into a namespace the
     * first one was moved out of precisely to avoid, and pins it in a second
     * lockfile. Declaring it here is what makes "lucide lives under
     * `icon/lucide/`" true of every run rather than of the ones that remembered
     * to say so.
     *
     * One kebab-case segment and nothing else, checked here rather than where
     * it is used — this is the only value in a set definition that decides
     * where a file is written, so `../..` is the one way a set can put a
     * component outside the components path.
     *
     * @param  array<mixed>  $definition
     */
    private static function namespace(string $name, array $definition): ?string
    {
        $namespace = self::text($name, $definition, 'namespace');

        if ($namespace !== null && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $namespace) !== 1) {
            throw new InvalidArgumentException("Icon set [{$name}] has a [namespace] of [{$namespace}], which is not one. Give it one lower-case segment, like [lucide].");
        }

        return $namespace;
    }

    /**
     * Which of this set's drawings fills each of Shape's slots.
     *
     * Read the same way as everything else here — checked rather than assumed,
     * because this half of the config is the half a consumer writes by hand.
     *
     * `null` is a value and not an omission. A set saying `null` has been asked
     * the question and has no drawing for it; a set saying nothing has not been
     * asked. Only the first is something `shape:icon` can report as a decision
     * rather than as a hole.
     *
     * @param  array<mixed>  $definition
     * @return array<string, string|null>
     */
    private static function slots(string $name, array $definition): array
    {
        $slots = $definition['slots'] ?? [];

        if (! is_array($slots)) {
            throw new InvalidArgumentException("Icon set [{$name}] has a [slots] that is not an array.");
        }

        $map = [];

        foreach ($slots as $slot => $source) {
            if (! is_string($slot) || $slot === '' || ($source !== null && (! is_string($source) || $source === ''))) {
                throw new InvalidArgumentException("Icon set [{$name}] has a slot that is not a name against a name.");
            }

            $map[$slot] = $source;
        }

        return $map;
    }

    /**
     * One optional string key of a set, checked rather than assumed.
     *
     * `repo`, `ref` and `path` are the three that say where a set can be
     * fetched from, and all three arrive from a config file a consumer is
     * invited to edit. A misspelling that surfaced as a type error inside an
     * HTTP call would be a poor way to learn that `ref` was written as an array.
     *
     * @param  array<mixed>  $definition
     */
    private static function text(string $name, array $definition, string $key): ?string
    {
        $value = $definition[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException("Icon set [{$name}] has a [{$key}] that is not a name.");
        }

        return $value;
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
     * The drawing this set fills one of Shape's slots with.
     *
     * The matrix model generalised across sets; the vocabulary did not. Lucide
     * has `x` where Heroicons has `x-mark`, `info` where it has
     * `information-circle`, `circle-check` where it has `check-circle`. Shape
     * asks neither of those questions: it asks which drawing fills
     * `shape-close`, and writes the answer to `shape-close.blade.php`. The
     * source moves and the slot does not, which is what lets a set be swapped
     * under the whole library without any filename claiming a vendor it is not.
     *
     * Three answers, and the difference between the last two is the point:
     *
     * - A slot the set names resolves to that drawing.
     * - A slot the set names `null` resolves to nothing. The set has been asked
     *   and has no drawing for it, which `shape:icon` can report as a decision.
     * - Anything the set says nothing about is itself. That covers every icon
     *   outside the slots — `bell`, `plus`, a set's own file under `--all` — and
     *   it is also how a set that has simply not been asked about a slot is told
     *   apart from one that answered `null`.
     */
    public function sourceName(string $name): ?string
    {
        return array_key_exists($name, $this->slots) ? $this->slots[$name] : $name;
    }

    /**
     * Whether this set has been asked about a slot at all.
     *
     * A slot missing from the map is an oversight and a slot mapped to `null` is
     * an answer, so the two cannot be one method. Only `shape.icon_slots` knows
     * which names are slots; this only knows what the set said about them.
     */
    public function declares(string $slot): bool
    {
        return array_key_exists($slot, $this->slots);
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
