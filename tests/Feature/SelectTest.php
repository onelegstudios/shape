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

it('takes the input\'s heights and keeps room for its own arrow at each of them', function (string $size, string $box, string $arrow) {
    $html = Blade::render("<x-shape::select name=\"plan\" size=\"{$size}\"><option>One</option></x-shape::select>");

    expect($html)->toContain($box)->toContain($arrow);
})->with([
    ['xs', '[:where(&amp;)]:h-6', 'right-1.5'],
    ['sm', '[:where(&amp;)]:h-8', 'right-2.5'],
    ['base', '[:where(&amp;)]:h-10', 'right-3'],
    ['lg', '[:where(&amp;)]:h-12', 'right-4'],
    ['xl', '[:where(&amp;)]:h-14', 'right-5'],
]);

it('grows the arrow with the control rather than drawing one size of it', function () {
    // A 20px chevron in a 24px box is a control that reads as an arrow with a
    // label attached; the same chevron in a 56px one disappears into it.
    expect(Blade::render('<x-shape::select name="plan" size="xs"><option>One</option></x-shape::select>'))
        ->toContain('size-4')
        ->and(Blade::render('<x-shape::select name="plan" size="xl"><option>One</option></x-shape::select>'))
        ->toContain('size-6');
});
