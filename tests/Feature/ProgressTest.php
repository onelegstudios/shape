<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is the platform element, which is what lets the value stay dynamic', function () {
    // The browser computes the width from `value` and `max`, so the template
    // never divides one by the other and never branches on either.
    $html = Blade::render('<x-shape::progress :value="42" />');

    expect($html)
        ->toContain('<progress')
        ->toContain('value="42"')
        ->toContain('max="100"')
        ->toContain('data-shape-progress');
});

it('prints no number, because formatting one is the call site\'s job', function () {
    expect(Blade::render('<x-shape::progress :value="42" />'))
        ->not->toContain('42%');
});

it('drops the value attribute in the indeterminate state', function () {
    // Not "no value given" — an explicit prop, so that branching on the state
    // never means branching on the value.
    expect(Blade::render('<x-shape::progress :indeterminate="true" />'))
        ->not->toContain('value=');
});

it('defaults to the accent tone and takes any other', function () {
    expect(Blade::render('<x-shape::progress :value="10" />'))
        ->toContain('data-shape-tone="accent"');

    expect(Blade::render('<x-shape::progress :value="10" color="danger" />'))
        ->toContain('data-shape-tone="danger"');
});

it('names itself when given a label, and adds no empty attribute when not', function () {
    expect(Blade::render('<x-shape::progress :value="10" label="Upload progress" />'))
        ->toContain('aria-label="Upload progress"');

    expect(Blade::render('<x-shape::progress :value="10" />'))
        ->not->toContain('aria-label');
});

it('lets a caller name it some other way', function () {
    expect(Blade::render('<x-shape::progress :value="10" aria-labelledby="upload-label" />'))
        ->toContain('aria-labelledby="upload-label"');
});

it('sizes itself from a key rather than from a value', function (string $size, string $class) {
    expect(Blade::render("<x-shape::progress :value=\"10\" size=\"{$size}\" />"))
        ->toContain($class)
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['sm', '[:where(&amp;)]:h-1'],
    ['base', '[:where(&amp;)]:h-2'],
    ['lg', '[:where(&amp;)]:h-3'],
]);

it('takes a max other than a hundred', function () {
    expect(Blade::render('<x-shape::progress :value="3" :max="7" />'))
        ->toContain('value="3"')
        ->toContain('max="7"');
});
