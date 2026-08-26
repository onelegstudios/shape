<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

beforeEach(fn () => Artisan::call('view:clear'));

it('resolves an icon by name', function () {
    expect(view('dynamic-icon')->render())
        ->toContain('viewBox="0 0 20 20"')
        ->toContain('data-shape-icon');
});

it('resolves icons bound from a loop variable', function () {
    expect(substr_count(view('dynamic-icon-loop')->render(), 'data-shape-icon'))->toBe(3);
});

it('renders nothing rather than recursing when no name is given', function () {
    // `shape::icon.` resolves back to the dispatcher itself, so an unguarded
    // empty name recurses until the request exhausts its memory limit.
    expect(trim(view('nameless-icon')->render()))->toBe('');
});
