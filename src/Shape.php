<?php

declare(strict_types=1);

namespace Onelegstudios\Shape;

class Shape
{
    /**
     * Begin building a class string for a component view.
     *
     * @param  string|array<array-key, mixed>|null  $classes
     */
    public function classes(string|array|null $classes = null): ClassBuilder
    {
        return new ClassBuilder($classes);
    }
}
