<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is the modal, with a message and two buttons', function () {
    $html = Blade::render('<x-shape::confirm />');

    expect($html)
        ->toContain('<dialog')
        ->toContain('data-shape-modal')
        ->toContain('data-shape-confirm')
        ->toContain('id="shape-confirm"');
});

it('has a literal default name, which a generated one could not be', function () {
    // The rule everywhere else is that an overlay's `name` has no default,
    // because an id generated inside a folded component is generated once. A
    // literal is already one value, so baking it is the correct result.
    expect(Blade::render('<x-shape::confirm />'))
        ->toContain('id="shape-confirm"');

    expect(Blade::render('<x-shape::confirm name="confirm-billing" />'))
        ->toContain('id="confirm-billing"')
        ->toContain('aria-labelledby="confirm-billing-heading"');
});

it('renders every element the payload fills in', function () {
    $html = Blade::render('<x-shape::confirm />');

    expect($html)
        ->toContain('id="shape-confirm-heading"')
        ->toContain('data-shape-confirm-message')
        ->toContain('data-shape-confirm-accept');
});

it('takes placeholder text that a caller can set and the payload can replace', function () {
    $html = Blade::render('<x-shape::confirm heading="Delete project?" message="This cannot be undone." accept="Delete" cancel="Keep it" />');

    expect($html)
        ->toContain('Delete project?')
        ->toContain('This cannot be undone.')
        ->toContain('Delete')
        ->toContain('Keep it');
});

it('closes with the platform command and accepts with something that is not one', function () {
    // The accept button has to dispatch before the dialog goes away, so it is
    // deliberately not a `command="close"` button — shape.js closes it after the
    // event has left.
    $html = Blade::render('<x-shape::confirm />');

    expect($html)
        ->toContain('command="close"')
        ->toContain('commandfor="shape-confirm"');

    expect((string) preg_replace('/<button(?:(?!<\/button>).)*data-shape-confirm-accept.*?<\/button>/s', '', $html))
        ->not->toContain('data-shape-confirm-accept');
});

it('lets an application translate its labels at the call site', function () {
    // Not `__()` inside the component: a folded component resolves a translation
    // once, at compile time, and serves that locale to everybody.
    expect(Blade::render('<x-shape::confirm :accept="__(\'Radera\')" />'))
        ->toContain('Radera');
});
