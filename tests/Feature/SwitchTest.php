<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is a real checkbox that announces as a switch', function () {
    // `role="switch"` on a native checkbox keeps every keyboard and form
    // behaviour while changing what a screen reader says: on and off, rather
    // than checked and unchecked.
    expect(Blade::render('<x-shape::switch name="notify" label="Email me" />'))
        ->toContain('type="checkbox"')
        ->toContain('role="switch"')
        ->toContain('data-shape-switch');
});

it('moves its knob without any javascript', function () {
    // The knob moves on `:checked`, which the browser handles. This component
    // works before the Alpine layer exists and keeps working if it never loads.
    expect(Blade::render('<x-shape::switch name="notify" />'))
        ->toContain('peer-checked:translate-x-4')
        ->not->toContain('x-data');
});

it('holds still for anyone who asked for less motion', function () {
    expect(Blade::render('<x-shape::switch name="notify" />'))->toContain('motion-reduce:transition-none');
});

it('defaults to the brand tone because a switch is a live setting', function () {
    expect(Blade::render('<x-shape::switch name="notify" />'))->toContain('data-shape-tone="brand"');
});

it('takes a colour like every other component that carries semantics', function () {
    expect(Blade::render('<x-shape::switch name="notify" tone="success" />'))
        ->toContain('data-shape-tone="success"')
        ->toContain('checked:bg-[var(--shape-tone)]');
});

it('infers its name from the livewire binding', function () {
    expect(Blade::render('<x-shape::switch wire:model.live="notify" />'))
        ->toContain('name="notify"')
        ->toContain('id="notify"');
});

it('describes itself when it renders a description', function () {
    expect(Blade::render('<x-shape::switch name="notify" label="Email me" description="Once a week at most." />'))
        ->toContain('aria-describedby="notify-description"')
        ->toContain('id="notify-description"');
});

it('keeps the track, the knob and the travel one number', function (string $size, string $track, string $knob, string $travel) {
    // The track is two knobs plus the four pixels of inset it keeps at every
    // step, and the travel is one knob. Setting them apart is how a switch ends
    // up with its knob stopping short of the end.
    $html = Blade::render("<x-shape::switch name=\"notify\" label=\"Email me\" size=\"{$size}\" />");

    expect($html)
        ->toContain("appearance-none {$track} shrink-0")
        ->toContain("left-0.5 {$knob} peer-checked:{$travel}")
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['xs', 'h-3.5 w-6', 'size-2.5', 'translate-x-2.5'],
    ['sm', 'h-4 w-7', 'size-3', 'translate-x-3'],
    ['base', 'h-5 w-9', 'size-4', 'translate-x-4'],
    ['lg', 'h-6 w-11', 'size-5', 'translate-x-5'],
    ['xl', 'h-7 w-13', 'size-6', 'translate-x-6'],
]);
