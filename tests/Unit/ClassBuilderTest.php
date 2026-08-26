<?php

declare(strict_types=1);

use Onelegstudios\Shape\ClassBuilder;
use Onelegstudios\Shape\Facades\Shape;

it('collects classes in the order they were added', function () {
    $classes = (new ClassBuilder('inline-flex'))->add('h-10 px-4');

    expect((string) $classes)->toBe('inline-flex h-10 px-4');
});

it('keeps the first occurrence of a repeated class', function () {
    $classes = (new ClassBuilder('h-10'))->add('px-4')->add('h-10');

    expect((string) $classes)->toBe('h-10 px-4');
});

it('resolves conditional arrays the way blade does', function () {
    $classes = (new ClassBuilder)->add(['hidden' => true, 'opacity-50' => false]);

    expect((string) $classes)->toBe('hidden');
});

it('ignores null and booleans so match arms can fall through', function () {
    $classes = (new ClassBuilder)->add('h-10')->add(null)->add(false)->add(true);

    expect((string) $classes)->toBe('h-10');
});

it('collapses whitespace between fragments', function () {
    $classes = (new ClassBuilder)->add("  h-10\n   px-4  ");

    expect((string) $classes)->toBe('h-10 px-4');
});

it('exposes the classes as a list', function () {
    expect((new ClassBuilder('h-10 px-4'))->toArray())->toBe(['h-10', 'px-4']);
});

it('is built from the facade', function () {
    expect(Shape::classes('h-10'))
        ->toBeInstanceOf(ClassBuilder::class)
        ->and((string) Shape::classes('h-10'))->toBe('h-10');
});
