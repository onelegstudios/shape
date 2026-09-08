<?php

declare(strict_types=1);

namespace Onelegstudios\Shape\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Onelegstudios\Shape\ClassBuilder classes(string|array<array-key, mixed>|null $classes = null)
 * @method static \Onelegstudios\Shape\PendingToast toast(string|null $heading = null)
 * @method static \Onelegstudios\Shape\PendingConfirm confirm(string|null $message = null)
 * @method static string|null gravatar(string|null $email, int|string $size = 'base', string|null $default = 'mp', string|null $rating = null)
 * @method static list<array{event: string, payload: array<string, mixed>}> flashedFeedback()
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
