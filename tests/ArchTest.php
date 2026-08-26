<?php

declare(strict_types=1);
use Illuminate\View\Component;

arch()->preset()->php();

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('Onelegstudios\Shape')
    ->toUseStrictTypes();

arch('components stay anonymous so that blaze can fold them')
    ->expect('Onelegstudios\Shape')
    ->not->toExtend(Component::class);
