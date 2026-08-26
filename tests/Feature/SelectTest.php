<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('takes its options as children', function () {
    // Options compose with @foreach, with optgroup, and with a caller's own
    // markup. An array prop does none of that without inventing a convention.
    $html = Blade::render(<<<'BLADE'
        <x-shape::select name="plan">
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
        </x-shape::select>
    BLADE);

    expect($html)
        ->toContain('<select')
        ->toContain('<option value="monthly">Monthly</option>')
        ->toContain('<option value="yearly">Yearly</option>');
});

it('renders a placeholder that cannot be chosen', function () {
    // Disabled, selected and hidden together are the only way a native select
    // shows prompt text without offering it as an answer.
    $html = Blade::render('<x-shape::select name="plan" placeholder="Choose a plan" />');

    expect($html)->toContain('<option value="" disabled selected hidden>Choose a plan</option>');
});

it('draws its own arrow and reserves the room for it', function () {
    $html = Blade::render('<x-shape::select name="plan" />');

    expect($html)
        ->toContain('appearance-none')
        ->toContain('data-shape-icon')
        // The arrow is decoration; the select keeps its native semantics.
        ->toContain('aria-hidden="true"')
        ->toContain('[:where(&amp;)]:pr-10');
});

it('assembles the whole field when given a label', function () {
    expect(Blade::render('<x-shape::select label="Plan" name="plan" />'))
        ->toContain('data-shape-field')
        ->toContain('for="plan"')
        ->toContain('id="plan"');
});

it('infers its name from the livewire binding', function () {
    expect(Blade::render('<x-shape::select wire:model="plan" />'))->toContain('name="plan"');
});

it('dims the arrow along with the control it belongs to', function () {
    expect(Blade::render('<x-shape::select name="plan" />'))->toContain('has-disabled:opacity-50');
});

it('offers a styled option that renders as a plain one', function () {
    expect(Blade::render('<x-shape::select.option value="monthly" label="Monthly" />'))
        ->toContain('<option value="monthly"')
        ->toContain('Monthly')
        ->toContain('data-shape-option');
});
