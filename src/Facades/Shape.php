<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Onelegstudios\Shape\ClassBuilder classes(string|array<array-key, mixed>|null $classes = null)
 *
 * @see \Onelegstudios\Shape\Shape
 */
class Shape extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Onelegstudios\Shape\Shape::class;
    }
}
