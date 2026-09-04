<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders every element the script has to fill, whether or not it has content', function () {
    // Nothing in a toast is wrapped in `@if`. The toaster renders it into a
    // template and shape.js fills it afterwards, so an element that isn't there
    // at compile time is an element that can never be filled.
    $html = Blade::render('<x-shape::toast />');

    expect($html)
        ->toContain('data-shape-toast')
        ->toContain('data-shape-toast-heading')
        ->toContain('data-shape-toast-description')
        ->toContain('data-shape-toast-icon');
});

it('collapses the parts that stay empty', function () {
    expect(Blade::render('<x-shape::toast />'))
        ->toContain('empty:hidden');
});

it('carries semantics on the tone attribute, with a glyph to match', function () {
    $html = Blade::render('<x-shape::toast color="success" />');

    expect($html)
        ->toContain('data-shape-tone="success"')
        ->toContain('data-shape-icon');
});

it('is dismissible unless told otherwise', function () {
    expect(Blade::render('<x-shape::toast />'))
        ->toContain('data-shape-dismiss');

    expect(Blade::render('<x-shape::toast :dismissible="false" />'))
        ->not->toContain('data-shape-dismiss');
});

it('sets no z-index, because the toaster is in the top layer', function () {
    expect(Blade::render('<x-shape::toast color="danger" />'))
        ->not->toContain('z-');
});

it('renders the toaster as a manual popover, which is where the top layer comes from', function () {
    $html = Blade::render('<x-shape::toaster />');

    expect($html)
        ->toContain('popover="manual"')
        ->toContain('data-shape-toaster')
        ->toContain('data-shape-position="bottom-right"');
});

it('carries two live regions, because a failure is not announced like a success', function () {
    $html = Blade::render('<x-shape::toaster />');

    expect($html)
        ->toContain('data-shape-toast-region="polite"')
        ->toContain('aria-live="polite"')
        ->toContain('data-shape-toast-region="assertive"')
        ->toContain('aria-live="assertive"');
});

it('ships a template per tone, so the glyph is resolved in blade rather than in the script', function (string $tone) {
    expect(Blade::render('<x-shape::toaster />'))
        ->toContain("data-shape-toast-template=\"{$tone}\"");
})->with(['neutral', 'accent', 'info', 'success', 'warning', 'danger']);

it('keeps its templates inert until something clones them', function () {
    // A `<template>` is parsed but not rendered, which is what lets six toasts
    // sit in the layout without appearing in it.
    expect(Blade::render('<x-shape::toaster />'))
        ->toContain('<template');
});

it('is positioned from a key', function () {
    expect(Blade::render('<x-shape::toaster position="top-center" />'))
        ->toContain('data-shape-position="top-center"');
});
