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
     * @param  non-empty-array<string, array<string, non-empty-list<string>>>  $styles  Style, then the sizes it draws its own glyph at, each a list of candidate patterns.
     * @param  array<string, string|null>  $slots  Shape's slots, against this set's own spelling of the drawing that fills each; null where the set has none.
     * @param  string  $ref  Only meaningful with a repo; ignored otherwise.
     * @param  string  $path  The subdirectory of the repository the drawings live in, if any.
     * @param  string|null  $namespace  The subdirectory of the components path this set is written into, if it wants one of its own.
     * @param  bool  $flatten  Whether the set's own subdirectories under `path` are collapsed into one when it is fetched.
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
        public readonly bool $flatten = false,
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

            foreach ($patterns as $size => $cell) {
                if (! is_string($size)) {
                    throw new InvalidArgumentException("Icon set [{$name}] has a drawing without a path.");
                }

                if (! isset($scale[$size])) {
                    throw new InvalidArgumentException("Icon set [{$name}] draws [{$style}] at [{$size}], which is not one of the library's sizes.");
                }

                $styles[$style][$size] = self::drawings($name, $cell);
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
            self::flatten($name, $definition),
        );
    }

    /**
     * Whether this set's own layout has to be collapsed before it can be read.
     *
     * A pattern places a name into a path, which covers every set whose layout
     * is a fact about the style and the size — `24/solid/{name}.svg` is the same
     * two directories for every drawing in it. It cannot cover a set that files
     * its drawings by something the name says nothing about: Remix Icon nests by
     * category, so `close-line` is under `System` and there is no pattern that
     * knows that without being told each name's category one at a time.
     *
     * Flattening answers it where the pattern cannot. The set is fetched whole
     * and unpacked into one directory by filename, and what is left on disk is
     * an ordinary flat set that `{name}-line.svg` reads the way it reads Lucide.
     * The cost is that a drawing can no longer be fetched on its own — nothing
     * can say where one file is without the archive in hand — so a set that
     * declares this always pulls the whole of itself.
     *
     * It is the set's own property rather than a flag, for the reason
     * `namespace` is: how a set is laid out is true of the set on every run.
     *
     * @param  array<mixed>  $definition
     */
    private static function flatten(string $name, array $definition): bool
    {
        $flatten = $definition['flatten'] ?? false;

        if (! is_bool($flatten)) {
            throw new InvalidArgumentException("Icon set [{$name}] has a [flatten] that is not true or false.");
        }

        return $flatten;
    }

    /**
     * The patterns one cell of the matrix may be drawn by, in the order they
     * are tried.
     *
     * One string is the ordinary case and stays one string in the config file.
     * A list is for a set that spells the same cell two ways, where the first
     * pattern that has a file behind it wins — Bootstrap draws its fill either
     * as a suffix or as an infix, and only the file itself says which.
     *
     * Each pattern places the name one of two ways, checked here so that a
     * misspelling is a sentence about the config rather than a component full
     * of `{tail}`:
     *
     * - `{name}` is the whole name, and is what nearly every set wants.
     * - `{head}` and `{tail}` are the name split at its last hyphen, and only
     *   ever appear together. That is the pair Bootstrap needs: `person-fill-x`
     *   is the fill of `person-x`, because the marker attaches to the glyph and
     *   not to the badge hanging off it.
     * - Neither is a fixed file, which is the same drawing whatever is asked
     *   for. Allowed, and named by nothing in a listing.
     *
     * @return non-empty-list<string>
     */
    private static function drawings(string $name, mixed $cell): array
    {
        $patterns = is_array($cell) ? array_values($cell) : [$cell];

        if ($patterns === []) {
            throw new InvalidArgumentException("Icon set [{$name}] has a drawing without a path.");
        }

        foreach ($patterns as $pattern) {
            if (! is_string($pattern) || $pattern === '') {
                throw new InvalidArgumentException("Icon set [{$name}] has a drawing without a path.");
            }

            $head = str_contains($pattern, '{head}');
            $tail = str_contains($pattern, '{tail}');

            if ($head !== $tail) {
                throw new InvalidArgumentException("Icon set [{$name}] has a drawing at [{$pattern}] with only half of the split. A pattern that splits a name uses [{head}] and [{tail}] together, like [{head}-fill-{tail}.svg].");
            }

            if ($head && str_contains($pattern, '{name}')) {
                throw new InvalidArgumentException("Icon set [{$name}] has a drawing at [{$pattern}] that places the name twice. Use [{name}] for the whole name, or [{head}] and [{tail}] for the name split at its last hyphen.");
            }
        }

        return $patterns;
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
     * Where one name could be drawn for one cell of the matrix, relative to the
     * source directory, in the order the files should be looked for.
     *
     * Usually one path, and more only where a cell declares more than one
     * pattern. Which of them is the drawing is not a question this can answer —
     * only the source knows which file is there — so it hands back every
     * candidate and the caller takes the first that exists.
     *
     * Empty where the cell has nothing to offer: a style that draws nothing at
     * all, or a pattern that splits a name with no hyphen in it to split.
     *
     * @return list<string>
     */
    public function paths(string $style, string $size, string $name): array
    {
        $paths = [];

        foreach ($this->patterns($style, $size) as $pattern) {
            $path = self::place($pattern, $name);

            if ($path !== null) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * How one cell of the matrix is spelled.
     *
     * A style that has no drawing of its own at this size borrows its largest,
     * which is then scaled down by the size class. Never scaled up: the fallback
     * is the largest declared rather than the nearest, because a 16px glyph
     * stretched to 24px looks like a mistake and a 24px one shrunk to 16px looks
     * like a smaller icon.
     *
     * @return list<string>
     */
    public function patterns(string $style, string $size): array
    {
        $drawn = $this->styles[$style] ?? [];

        if (isset($drawn[$size])) {
            return $drawn[$size];
        }

        $largest = [];

        foreach ($this->sizes() as $candidate) {
            if (isset($drawn[$candidate])) {
                $largest = $drawn[$candidate];
            }
        }

        return $largest;
    }

    /**
     * One pattern with a name put into it, or null where the name cannot fill
     * it.
     *
     * The split half is the only way that happens: `{head}-fill-{tail}.svg`
     * needs a name with a hyphen in it, and `bell` has not got one, so the set
     * has no such drawing for it rather than a path with an empty half.
     */
    private static function place(string $pattern, string $name): ?string
    {
        if (str_contains($pattern, '{name}')) {
            return str_replace('{name}', $name, $pattern);
        }

        if (! str_contains($pattern, '{head}')) {
            return $pattern;
        }

        $cut = strrpos($name, '-');

        if ($cut === false) {
            return null;
        }

        return str_replace(
            ['{head}', '{tail}'],
            [substr($name, 0, $cut), substr($name, $cut + 1)],
            $pattern,
        );
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
            foreach ($drawn as $patterns) {
                foreach ($patterns as $pattern) {
                    $directories[] = self::directory($pattern);
                }
            }
        }

        return array_values(array_unique($directories));
    }

    /**
     * The name a file found in one of those directories is written under.
     *
     * `directories()` says where to look and this says what a file found there
     * is called, because for some sets those are two questions rather than one.
     * Phosphor puts the weight in the filename — `assets/fill/heart-fill.svg` is
     * the fill drawing of `heart`, not an icon called `heart-fill` — so reading
     * a listing as names would write fifteen hundred components whose every
     * other cell resolves to `heart-fill-fill.svg` and is never there.
     *
     * Running the pattern backwards is that same declaration read the other way:
     * `{name}-fill` against `heart-fill` is `heart`, and a split pattern reads
     * the same way — `{head}-fill-{tail}` against `person-fill-x` is `person-x`.
     * A file no pattern matches is not this set's to write, which is `null` and
     * a skip rather than a name.
     *
     * The shortest answer wins where one directory holds two patterns, which is
     * a flat set that draws its solid style by suffix: `{name}` reads
     * `heart-fill` as itself and `{name}-fill` reads it as `heart`, and the
     * second is the drawing it actually is.
     */
    public function nameFor(string $directory, string $file): ?string
    {
        $names = [];

        foreach ($this->styles as $drawn) {
            foreach ($drawn as $patterns) {
                foreach ($patterns as $pattern) {
                    $name = self::read($directory, $pattern, $file);

                    if ($name !== null) {
                        $names[] = $name;
                    }
                }
            }
        }

        usort($names, fn (string $a, string $b): int => strlen($a) <=> strlen($b));

        return $names[0] ?? null;
    }

    /**
     * One pattern run backwards over one filename.
     *
     * `{tail}` is a single segment and `{head}` is the rest, which is what makes
     * this the exact inverse of the split `place()` does: it cuts a name at its
     * last hyphen, so the half after the marker can hold no hyphen of its own.
     */
    private static function read(string $directory, string $pattern, string $file): ?string
    {
        if (self::directory($pattern) !== $directory) {
            return null;
        }

        $quoted = preg_quote(pathinfo($pattern, PATHINFO_FILENAME), '/');

        if (str_contains($pattern, '{name}')) {
            $expression = str_replace(preg_quote('{name}', '/'), '(.+)', $quoted);

            return preg_match('/^'.$expression.'$/', $file, $matches) === 1
                ? $matches[1]
                : null;
        }

        // A pattern that places no name names no icon — it is the same file
        // whatever is asked for, so there is nothing in a listing for it to have
        // drawn.
        if (! str_contains($pattern, '{head}')) {
            return null;
        }

        $expression = str_replace(
            [preg_quote('{head}', '/'), preg_quote('{tail}', '/')],
            ['(.+)', '([^-]+)'],
            $quoted,
        );

        return preg_match('/^'.$expression.'$/', $file, $matches) === 1
            ? $matches[1].'-'.$matches[2]
            : null;
    }

    /**
     * The directory half of a pattern, which is empty for a flat set.
     */
    private static function directory(string $pattern): string
    {
        return trim(trim(dirname($pattern), '.'), '/');
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
