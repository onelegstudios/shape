<?php

declare(strict_types=1);

use Onelegstudios\Shape\Facades\Shape;

it('hashes the address the way gravatar hashes it', function () {
    // SHA-256 of the trimmed, lowercased address. Anything else is a URL for
    // somebody who does not exist, which Gravatar answers for rather than
    // erroring on, so the only way this bug surfaces is as a stranger's face.
    expect(Shape::gravatar('ada@example.com'))
        ->toStartWith('https://gravatar.com/avatar/'.hash('sha256', 'ada@example.com').'?');
});

it('trims and lowercases first, so one person is one hash', function () {
    expect(Shape::gravatar('  Ada@Example.COM  '))->toBe(Shape::gravatar('ada@example.com'));
});

it('returns null for an address that is not there', function () {
    // Null rather than a URL for nobody, so it drops through the avatar's
    // ladder to `icon` and then `initials` the way any absent `src` does.
    expect(Shape::gravatar(null))->toBeNull()
        ->and(Shape::gravatar(''))->toBeNull()
        ->and(Shape::gravatar('   '))->toBeNull();
});

it('takes the avatar\'s own word for a size and asks for twice the circle', function () {
    // The scale is the component's, doubled, so that no call site has to hold
    // it in its head: 24, 32, 40 and 48 drawn on a display that is not 1x.
    expect(Shape::gravatar('ada@example.com', size: 'xs'))->toContain('s=48')
        ->and(Shape::gravatar('ada@example.com', size: 'sm'))->toContain('s=64')
        ->and(Shape::gravatar('ada@example.com', size: 'base'))->toContain('s=80')
        ->and(Shape::gravatar('ada@example.com', size: 'lg'))->toContain('s=96');
});

it('defaults to base, the size the component defaults to', function () {
    expect(Shape::gravatar('ada@example.com'))->toContain('s=80');
});

it('reads a word it does not know the way the component reads it', function () {
    // An avatar told `xl` draws the base circle, so a URL for one asks for the
    // base pixels. The two readings of the same word do not come apart.
    expect(Shape::gravatar('ada@example.com', size: 'xl'))->toContain('s=80');
});

it('takes an int as pixels, for the 3x display and the call site that is not an avatar', function () {
    expect(Shape::gravatar('ada@example.com', size: 120))->toContain('s=120');
});

it('defaults to a picture rather than a broken image', function () {
    expect(Shape::gravatar('ada@example.com'))->toContain('d=mp');
});

it('takes a default of its own, including the transparent one', function () {
    expect(Shape::gravatar('ada@example.com', default: 'blank'))->toContain('d=blank');
});

it('leaves out what it was not given, so gravatar answers with its own', function () {
    expect(Shape::gravatar('ada@example.com', default: null))->not->toContain('d=')
        ->and(Shape::gravatar('ada@example.com'))->not->toContain('r=')
        ->and(Shape::gravatar('ada@example.com', rating: 'g'))->toContain('r=g');
});
