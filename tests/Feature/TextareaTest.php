<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a textarea carrying its slot as content', function () {
    expect(Blade::render('<x-shape::textarea name="notes">Existing copy</x-shape::textarea>'))
        ->toContain('<textarea')
        ->toContain('>Existing copy</textarea>')
        ->toContain('data-shape-control');
});

it('assembles the whole field when given a label', function () {
    expect(Blade::render('<x-shape::textarea label="Notes" name="notes" />'))
        ->toContain('data-shape-field')
        ->toContain('for="notes"')
        ->toContain('id="notes"');
});

it('resizes vertically only', function () {
    // A textarea that can be dragged wider breaks out of whatever laid it out.
    expect(Blade::render('<x-shape::textarea name="notes" />'))->toContain('[:where(&amp;)]:resize-y');
});

it('sets a row floor a caller can raise', function () {
    expect(Blade::render('<x-shape::textarea name="notes" />'))->toContain('rows="3"')
        ->and(Blade::render('<x-shape::textarea name="notes" rows="10" />'))->toContain('rows="10"');
});

it('infers its name from the livewire binding', function () {
    expect(Blade::render('<x-shape::textarea wire:model="notes" />'))->toContain('name="notes"');
});

it('shares the chrome the input uses', function () {
    $textarea = Blade::render('<x-shape::textarea name="notes" />');

    expect($textarea)
        ->toContain('focus-visible:outline-[var(--shape-ring)]')
        ->toContain('aria-invalid:border-shape-danger-500')
        ->toContain('[:where(&amp;)]:rounded-shape');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::textarea name="notes" class="resize-none" />'))
        ->toContain('[:where(&amp;)]:resize-y')
        ->toContain('resize-none');
});
