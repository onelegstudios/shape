<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Onelegstudios\Shape\Registry;
use Onelegstudios\Shape\Shape;

it('resolves the singleton', function () {
    expect(app(Shape::class))->toBeInstanceOf(Shape::class);
});

it('returns the same instance from the container', function () {
    expect(app(Shape::class))->toBe(app(Shape::class));
});

it('merges the package config', function () {
    expect(config('shape.components_path'))->toBe(resource_path('views/shape'));
});

it('registers the component manifest as one instance', function () {
    expect(app(Registry::class))->toBe(app(Registry::class));
});

it('registers its artisan commands', function (string $command) {
    expect(array_keys(Artisan::all()))->toContain($command);
})->with([
    'shape:doctor',
    'shape:eject',
    'shape:eject:all',
    'shape:eject:status',
    'shape:icon',
    'shape:icon:all',
    'shape:icon:replace',
    'shape:icon:status',
    'shape:install',
]);
