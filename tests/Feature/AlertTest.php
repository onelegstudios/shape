<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a block that stays on the page', function () {
    $html = Blade::render('<x-shape::alert>Your trial ends on Friday.</x-shape::alert>');

    expect($html)
        ->toContain('data-shape-alert')
        ->toContain('Your trial ends on Friday.')
        ->toContain('data-shape-tone="neutral"');
});

it('carries semantics on the tone attribute', function () {
    expect(Blade::render('<x-shape::alert color="danger">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-tone="danger"');
});

it('resolves a glyph for every state so colour is never the only signal', function (string $color) {
    expect(Blade::render("<x-shape::alert color=\"{$color}\">Message</x-shape::alert>"))
        ->toContain('data-shape-icon');
})->with(['success', 'danger', 'warning', 'accent']);

it('gives each state a glyph of its own rather than reusing one', function () {
    $glyphs = collect(['success', 'danger', 'warning', 'accent'])
        ->map(fn (string $color) => Blade::render("<x-shape::alert color=\"{$color}\">Message</x-shape::alert>"))
        ->map(fn (string $html) => preg_match('/<path[^>]*d="([^"]+)"/', $html, $m) ? $m[1] : null);

    expect($glyphs->filter()->unique())->toHaveCount(4);
});

it('lets a caller opt out of the glyph', function () {
    expect(Blade::render('<x-shape::alert color="success" :icon="false">Done</x-shape::alert>'))
        ->not->toContain('data-shape-icon');
});

it('publishes the tone as its own foreground contract', function () {
    // The point of `data-shape-surface="tint"`: a muted paragraph inside a
    // coloured alert has to read a dialled-back version of the tone, not the
    // global grey that would otherwise apply on a coloured background.
    expect(Blade::render('<x-shape::alert color="danger">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-surface="tint"');
});

it('is not a live region, because it was on the page already', function () {
    // The toaster carries the live regions. Announcing markup that was present
    // at load repeats what a screen reader is about to read anyway.
    expect(Blade::render('<x-shape::alert color="danger">Card declined.</x-shape::alert>'))
        ->not->toContain('aria-live')
        ->not->toContain('role="alert"');
});

it('renders a heading only when it is given one', function () {
    expect(Blade::render('<x-shape::alert heading="Payment failed">Retry it.</x-shape::alert>'))
        ->toContain('data-shape-heading')
        ->toContain('Payment failed');

    expect(Blade::render('<x-shape::alert>Retry it.</x-shape::alert>'))
        ->not->toContain('data-shape-heading');
});

it('is dismissible only when asked, and hands the job to the script', function () {
    expect(Blade::render('<x-shape::alert :dismissible="true">Message</x-shape::alert>'))
        ->toContain('data-shape-dismiss')
        ->toContain('aria-label="Dismiss"');

    expect(Blade::render('<x-shape::alert>Message</x-shape::alert>'))
        ->not->toContain('data-shape-dismiss');
});

it('yields to a class passed at the call site', function () {
    expect(Blade::render('<x-shape::alert class="p-8">Message</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:p-4')
        ->toContain('p-8');
});
