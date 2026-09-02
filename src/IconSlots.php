<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use InvalidArgumentException;

/**
 * The icons Shape resolves on the user's behalf, as declared by the library.
 *
 * A slot is a role rather than a drawing: `shape-warning` is the glyph a warning
 * tone reaches for, and which of Lucide's or Heroicons' files ends up behind it
 * is the set's answer, given in `shape.icon_sets.*.slots`. Naming the file for
 * the role is what stops a Lucide drawing shipping as
 * `exclamation-triangle.blade.php`, with a licence header underneath saying
 * otherwise.
 *
 * The list is declared, in `shape.icon_slots`, for the reason the size scale is:
 * it is a fact about the library, and every set is measured against the same
 * one. Deriving it from the markup — which is what `Registry::icons()` used to
 * be the source of truth for — cannot express a slot that no component draws,
 * and `shape-loading` is exactly that: nothing in `resources/views/shape` renders
 * a spinner, and the icon exists to be used by an application.
 *
 * `Registry::icons()` still reads the markup, and now checks this rather than
 * replacing it: every `shape-*` a component draws has to be declared here.
 *
 * Compile-time, like everything else the generator reads. Nothing in this class
 * is touched while a request renders.
 */
final class IconSlots
{
    /**
     * @param  array<string, array{class?: string, packaged?: bool}>  $slots
     */
    private function __construct(private readonly array $slots) {}

    /**
     * Read the declared slots out of whatever the config file holds.
     *
     * Checked rather than assumed, because a consumer publishes this file and
     * then edits it. A published config from before slots existed has no
     * `icon_slots` at all, and saying so beats a `shape:icon:replace` that
     * writes nothing and reports success.
     */
    public static function fromArray(mixed $slots): self
    {
        if (! is_array($slots) || $slots === []) {
            throw new InvalidArgumentException('No icon slots are configured. Add [icon_slots] to config/shape.php — it is the list of icons Shape resolves for you, and `shape:icon:replace` generates exactly it.');
        }

        $declared = [];

        foreach ($slots as $name => $spec) {
            if (! is_string($name) || $name === '' || ! is_array($spec)) {
                throw new InvalidArgumentException('The icon slots hold a slot that is not a name against a definition.');
            }

            $class = $spec['class'] ?? null;

            if ($class !== null && ! is_string($class)) {
                throw new InvalidArgumentException("Icon slot [{$name}] has a [class] that is not a class.");
            }

            $declared[$name] = array_filter([
                'class' => $class,
                'packaged' => ($spec['packaged'] ?? false) === true,
            ], fn (mixed $value): bool => $value !== null && $value !== false);
        }

        /** @var array<string, array{class?: string, packaged?: bool}> $declared */
        return new self($declared);
    }

    public static function fromConfig(): self
    {
        return self::fromArray(config('shape.icon_slots'));
    }

    /**
     * Every slot, in the order the library declares them.
     *
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->slots);
    }

    public function has(string $name): bool
    {
        return isset($this->slots[$name]);
    }

    /**
     * Whether Shape draws this slot itself, so that no set has to.
     *
     * True of `shape-loading` and nothing else. It is what makes a set's silence
     * correct there and a warning everywhere else: a `null` on `shape-trend-up`
     * falls back to a *Heroicon*, which is the mixed-set page this whole
     * arrangement exists to prevent, while the packaged spinner belongs to no
     * set and sits beside anything.
     */
    public function isPackaged(string $name): bool
    {
        return ($this->slots[$name]['packaged'] ?? false) === true;
    }

    /**
     * The utilities a generated icon carries, for this name.
     *
     * `shrink-0` is every icon's, and a slot may add to it. The spin belongs to
     * `shape-loading` rather than to whichever set drew it — every set's loader
     * spins — which is why it is stated once on the slot instead of repeated in
     * every set's map.
     */
    public function classFor(string $name): string
    {
        $class = $this->slots[$name]['class'] ?? null;

        return $class === null || $class === '' ? 'shrink-0' : 'shrink-0 '.$class;
    }
}
