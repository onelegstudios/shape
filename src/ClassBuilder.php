<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

use Illuminate\Support\Arr;
use Stringable;

/**
 * Collects component class fragments into a single, de-duplicated class string.
 *
 * Component views build their classes through this rather than string
 * concatenation so that the fragments stay readable next to the `match`
 * expressions that produce them.
 */
final class ClassBuilder implements Stringable
{
    /**
     * The collected classes, keyed by class name to preserve first-seen order.
     *
     * @var array<string, string>
     */
    private array $classes = [];

    /**
     * @param  string|array<array-key, mixed>|null  $classes
     */
    public function __construct(string|array|null $classes = null)
    {
        $this->add($classes);
    }

    /**
     * Add one or more classes.
     *
     * Arrays are resolved through Laravel's conditional class syntax, so
     * `['hidden' => $isHidden]` behaves the way it does in Blade.
     *
     * @param  string|array<array-key, mixed>|bool|null  $classes
     */
    public function add(string|array|bool|null $classes): self
    {
        if ($classes === null || is_bool($classes)) {
            return $this;
        }

        $normalized = is_array($classes) ? Arr::toCssClasses($classes) : $classes;

        $fragments = preg_split('/\s+/', trim($normalized), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($fragments === false ? [] : $fragments as $class) {
            $this->classes[$class] = $class;
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return array_values($this->classes);
    }

    public function __toString(): string
    {
        return implode(' ', $this->classes);
    }
}
