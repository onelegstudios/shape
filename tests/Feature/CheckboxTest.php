<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('wraps the control in its own label so nothing has to be associated', function () {
    // A control inside its own label needs no `for`, so nothing can drift out of
    // sync and there is nothing for a caller to remember.
    $html = Blade::render('<x-shape::checkbox name="terms" label="I agree" />');

    expect($html)
        ->toContain('<label')
        ->toContain('type="checkbox"')
        ->toContain('I agree')
        ->toContain('data-shape-checkbox');
});

it('separates a group of checkboxes by value', function () {
    // One name, many values — so the id has to carry the value too.
    expect(Blade::render('<x-shape::checkbox name="days" value="mon" label="Monday" />'))
        ->toContain('name="days"')
        ->toContain('value="mon"')
        ->toContain('id="days-mon"');
});

it('takes its name from the field it sits in', function () {
    $html = Blade::render(<<<'BLADE'
        <x-shape::field as="fieldset" field-name="days">
            <x-shape::checkbox value="mon" label="Monday" />
        </x-shape::field>
    BLADE);

    expect($html)->toContain('name="days"')->toContain('id="days-mon"');
});

it('draws its own box rather than using the platform one', function () {
    expect(Blade::render('<x-shape::checkbox name="terms" />'))->toContain('appearance-none');
});

it('ships the indeterminate glyph even though no prop can set the state', function () {
    // Indeterminate is a DOM property, not an attribute, so no server-rendered
    // markup can set it. The glyph and its styling ship anyway, so it appears
    // the moment Livewire, Alpine or plain JS sets the property.
    expect(Blade::render('<x-shape::checkbox name="terms" />'))
        ->toContain('peer-indeterminate:opacity-100')
        ->toContain('peer-checked:opacity-100');
});

it('reads its checked fill from the tone variables', function () {
    expect(Blade::render('<x-shape::checkbox name="terms" tone="brand" />'))
        ->toContain('data-shape-tone="brand"')
        ->toContain('checked:bg-[var(--shape-tone)]');
});

it('falls back to the neutral tone like every other component', function () {
    expect(Blade::render('<x-shape::checkbox name="terms" />'))->toContain('data-shape-tone="neutral"');
});

it('dims its own label when disabled without reaching across the group', function () {
    // `peer-` cannot reach the label: the input is nested a level down, and a
    // peer has to be a previous sibling.
    expect(Blade::render('<x-shape::checkbox name="terms" label="I agree" disabled />'))
        ->toContain('group-has-disabled:opacity-50')
        ->toContain('has-disabled:cursor-not-allowed');
});

it('describes itself when it renders a description', function () {
    expect(Blade::render('<x-shape::checkbox name="terms" value="1" label="I agree" description="Really." />'))
        ->toContain('aria-describedby="terms-1-description"')
        ->toContain('id="terms-1-description"');
});

it('renders no label markup when there is no label', function () {
    expect(Blade::render('<x-shape::checkbox name="terms" />'))->not->toContain('<span class="flex flex-col');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::checkbox name="terms" wire:model="terms" checked />'))
        ->toContain('wire:model="terms"')
        ->toContain('checked');
});
