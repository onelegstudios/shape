<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Onelegstudios\Shape\Registry;

/**
 * The bundled skill is written from the registry, and drifts silently otherwise.
 *
 * An assistant reading a stale skill invents props that were renamed and misses
 * components that were added — which is the failure this package exists to
 * avoid at the call site, one layer up.
 */
function boostSkill(): string
{
    return (string) file_get_contents(__DIR__.'/../../resources/boost/skills/shape-development/SKILL.md');
}

it('names every component the package ships', function () {
    $skill = boostSkill();

    $missing = array_values(array_filter(
        (new Registry)->names(),
        fn (string $name): bool => ! str_contains($skill, "`{$name}`") && ! str_contains($skill, "`{$name}."),
    ));

    expect($missing)->toBe([], 'The bundled skill has fallen behind the registry.');
});

it('names only commands that exist', function () {
    preg_match_all('/`?php artisan (shape:[a-z]+)/', boostSkill(), $matches);

    expect(array_unique($matches[1]))->not->toBeEmpty();

    foreach (array_unique($matches[1]) as $command) {
        expect(array_keys(Artisan::all()))->toContain($command);
    }
});

it('points at documentation pages that exist', function () {
    preg_match_all('#^- `vendor/onelegstudios/shape/([^`]+)`#m', boostSkill(), $matches);

    expect($matches[1])->not->toBeEmpty();

    foreach (array_unique($matches[1]) as $path) {
        expect(__DIR__.'/../../'.$path)->toBeFile();
    }
});
