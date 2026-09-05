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
