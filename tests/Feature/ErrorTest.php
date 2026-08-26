<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * Share an error bag the way the session middleware does on a web request.
 */
function shareErrors(array $messages, string $bag = 'default'): void
{
    View::share('errors', (new ViewErrorBag)->put($bag, new MessageBag($messages)));
}

afterEach(function () {
    View::share('errors', null);
});

it('renders the message for the field it sits in', function () {
    shareErrors(['email' => 'That address is already taken.']);

    expect(Blade::render('<x-shape::field name="email"><x-shape::error /></x-shape::field>'))
        ->toContain('That address is already taken.')
        ->toContain('data-shape-error');
});

it('renders nothing when the field is valid', function () {
    shareErrors(['name' => 'Required.']);

    expect(Blade::render('<x-shape::field name="email"><x-shape::error /></x-shape::field>'))
        ->not->toContain('data-shape-error');
});

it('renders nothing at all when no error bag was shared', function () {
    // A mailable, a queued render, a component rendered outside the session
    // middleware. Reading the bag defensively is the difference between "this
    // field has no message" and a fatal.
    expect(Blade::render('<x-shape::field name="email"><x-shape::error /></x-shape::field>'))
        ->not->toContain('data-shape-error');
});

it('stays quiet rather than showing a neighbour\'s message when it has no name', function () {
    // An empty key makes MessageBag::has() answer for *any* error in the bag,
    // which is how a nameless field ends up displaying someone else's failure.
    shareErrors(['email' => 'That address is already taken.']);

    expect(Blade::render('<x-shape::error />'))
        ->not->toContain('data-shape-error');
});

it('reads a named bag when asked', function () {
    shareErrors(['email' => 'Wrong password.'], 'login');

    expect(Blade::render('<x-shape::error name="email" bag="login" />'))
        ->toContain('Wrong password.')
        ->and(Blade::render('<x-shape::error name="email" />'))
        ->not->toContain('Wrong password.');
});

it('announces itself to assistive technology', function () {
    shareErrors(['email' => 'Required.']);

    // `role="alert"` already implies an assertive live region. Pairing it with
    // `aria-live="polite"` asks for both at once and browsers disagree.
    expect(Blade::render('<x-shape::error name="email" />'))
        ->toContain('role="alert"')
        ->not->toContain('aria-live');
});

it('carries the danger tone rather than a hard-coded red', function () {
    shareErrors(['email' => 'Required.']);

    expect(Blade::render('<x-shape::error name="email" />'))
        ->toContain('data-shape-tone="danger"')
        ->toContain('text-[color:var(--shape-tone-ink)]');
});

it('lets a caller add a class', function () {
    // The only attribute that crosses the boundary, and it has to be handed in
    // through the scope rather than read from the bag inside the block.
    shareErrors(['email' => 'Required.']);

    expect(Blade::render('<x-shape::error name="email" class="mt-4" />'))->toContain('mt-4');
});

it('sets no outer margin of its own', function () {
    shareErrors(['email' => 'Required.']);

    // The field owns the space between its children.
    expect(Blade::render('<x-shape::error name="email" />'))->not->toContain('class="mt-');
});
