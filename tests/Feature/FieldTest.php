<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('states the field name once and wires the label to the control', function () {
    // The point of the field: `name` is written here and nowhere else.
    $html = Blade::render(<<<'BLADE'
        <x-shape::field name="email">
            <x-shape::label>Email</x-shape::label>
            <x-shape::input type="email" />
        </x-shape::field>
    BLADE);

    expect($html)
        ->toContain('for="email"')
        ->toContain('id="email"')
        ->toContain('name="email"');
});

it('gives the description an id the control can point at', function () {
    $html = Blade::render(<<<'BLADE'
        <x-shape::field name="email">
            <x-shape::description>For receipts.</x-shape::description>
        </x-shape::field>
    BLADE);

    expect($html)->toContain('id="email-description"');
});

it('lets an explicit target override the field it sits in', function () {
    $html = Blade::render(<<<'BLADE'
        <x-shape::field name="email">
            <x-shape::label for="something-else">Email</x-shape::label>
        </x-shape::field>
    BLADE);

    expect($html)->toContain('for="something-else"');
});

it('renders a label with no target rather than an empty for', function () {
    expect(Blade::render('<x-shape::label>Loose</x-shape::label>'))
        ->toContain('data-shape-label')
        ->not->toContain('for=');
});

it('becomes a real fieldset with a legend for a group', function () {
    $html = Blade::render(<<<'BLADE'
        <x-shape::field as="fieldset" name="billing">
            <x-shape::label as="legend">Billing period</x-shape::label>
        </x-shape::field>
    BLADE);

    expect($html)
        ->toContain('<fieldset')
        ->toContain('<legend')
        // A legend names its fieldset by being inside it, so it takes no `for`.
        ->not->toContain('for="billing"');
});

it('resets the fieldset styling the browser supplies', function () {
    // `min-w-0` is the load-bearing one: without it a fieldset refuses to shrink
    // below its content and breaks whatever flex parent it was placed in.
    expect(Blade::render('<x-shape::field as="fieldset" />'))
        ->toContain('min-w-0')
        ->toContain('[:where(&amp;)]:border-0');
});

it('dims only its own direct children when a control is disabled', function () {
    // A checkbox carries its own label inside itself. Matching descendants would
    // mean one disabled radio dimming the labels of every sibling in the group.
    expect(Blade::render('<x-shape::field name="email" />'))
        ->toContain('[&amp;:has(&gt;[data-shape-control]:disabled)&gt;[data-shape-label]]:opacity-50');
});

it('owns the space between its children rather than leaving it to them', function () {
    expect(Blade::render('<x-shape::field name="email" />'))->toContain('gap-1.5');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::field name="email" wire:key="f" class="max-w-sm" />'))
        ->toContain('wire:key="f"')
        ->toContain('max-w-sm');
});
