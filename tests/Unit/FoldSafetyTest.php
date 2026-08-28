<?php

declare(strict_types=1);

use Onelegstudios\Shape\FoldSafety;

it('reports the line a hazard is on in the file', function () {
    // The blocks that are excluded are blanked rather than removed, so a line
    // number here is a line number a developer can jump to. Getting this wrong
    // is invisible in a one-line component and useless in a real one.
    $offences = (new FoldSafety)->inspect(<<<'BLADE'
    @blaze(fold: true)

    {{--
        A comment several
        lines long.
    --}}

    <p>{{ session('greeting') }}</p>
    BLADE);

    expect($offences)->toBe([
        ['pattern' => 'session(', 'hint' => 'the session', 'line' => 8],
    ]);
});

it('ignores a pattern that only appears in a comment', function () {
    expect((new FoldSafety)->inspect("@blaze(fold: true)\n\n{{-- Never calls auth() here. --}}"))->toBe([]);
});

it('ignores a pattern inside the hole a component cuts on purpose', function () {
    $source = <<<'BLADE'
    @blaze(fold: true)

    @unblaze(scope: ['name' => $name])
        {{ $errors->first($scope['name']) }}
    @endunblaze
    BLADE;

    expect((new FoldSafety)->inspect($source))->toBe([]);
});

it('has nothing to say about a component that does not fold', function () {
    expect((new FoldSafety)->inspect("@blaze(memo: true)\n\n<time>{{ now() }}</time>"))->toBe([]);
});

it('finds every occurrence of a hazard, in the order they appear', function () {
    $offences = (new FoldSafety)->inspect("@blaze(fold: true)\n{{ config('a') }}\n{{ auth()->id() }}");

    expect(array_column($offences, 'pattern'))->toBe(['config(', 'auth(']);
});
