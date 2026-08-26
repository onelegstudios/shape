<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a bare control when given no label', function () {
    expect(Blade::render('<x-shape::input type="email" name="email" />'))
        ->toContain('<input')
        ->toContain('type="email"')
        ->toContain('data-shape-control')
        ->not->toContain('data-shape-field');
});

it('assembles the whole field when given a label', function () {
    // Identical output to composing the primitives by hand — this is those
    // primitives already assembled, not a second implementation.
    $html = Blade::render('<x-shape::input type="email" label="Email" name="email" />');

    expect($html)
        ->toContain('data-shape-field')
        ->toContain('data-shape-label')
        ->toContain('Email')
        ->toContain('for="email"')
        ->toContain('id="email"');
});

it('infers its name from the livewire binding', function () {
    // `wire:model="email"` alone wires up the label, the id, the name and the
    // key the error message looks up. Nothing is stated twice.
    expect(Blade::render('<x-shape::input wire:model="email" label="Email" />'))
        ->toContain('name="email"')
        ->toContain('id="email"')
        ->toContain('for="email"');
});

it('infers through a modified binding', function () {
    expect(Blade::render('<x-shape::input wire:model.live.debounce.300ms="email" />'))
        ->toContain('name="email"');
});

it('prefers an explicit name over the field it sits in', function () {
    $html = Blade::render(<<<'BLADE'
        <x-shape::field field-name="from-the-field">
            <x-shape::input name="explicit" />
        </x-shape::field>
    BLADE);

    expect($html)->toContain('name="explicit"')->not->toContain('name="from-the-field"');
});

it('prefers the field it sits in over the livewire binding', function () {
    $html = Blade::render(<<<'BLADE'
        <x-shape::field field-name="from-the-field">
            <x-shape::input wire:model="from-the-binding" />
        </x-shape::field>
    BLADE);

    expect($html)->toContain('name="from-the-field"');
});

it('describes itself only when it rendered the description', function () {
    // A dangling `aria-describedby` is worse than an absent one, so the control
    // claims the reference only when it also drew the thing being referenced.
    expect(Blade::render('<x-shape::input label="Email" description="For receipts." name="email" />'))
        ->toContain('aria-describedby="email-description"')
        ->toContain('id="email-description"')
        ->and(Blade::render('<x-shape::input label="Email" name="email" />'))
        ->not->toContain('aria-describedby');
});

it('renders no empty name or id when it can infer nothing', function () {
    expect(Blade::render('<x-shape::input type="search" />'))
        ->not->toContain('name=""')
        ->not->toContain('id=""');
});

it('applies the requested size', function (string $size, string $expected) {
    expect(Blade::render("<x-shape::input size=\"{$size}\" />"))->toContain($expected);
})->with([
    ['sm', '[:where(&amp;)]:h-8'],
    ['base', '[:where(&amp;)]:h-10'],
    ['lg', '[:where(&amp;)]:h-12'],
]);

it('drives its invalid styling from the aria state rather than a second prop', function () {
    // What a screen reader announces and what a sighted user sees cannot drift
    // apart if they are the same attribute.
    expect(Blade::render('<x-shape::input name="email" aria-invalid="true" />'))
        ->toContain('aria-invalid="true"')
        ->toContain('aria-invalid:border-shape-danger-500');
});

it('focuses with the same ring the button uses', function () {
    expect(Blade::render('<x-shape::input name="email" />'))
        ->toContain('focus-visible:outline-[var(--shape-ring)]');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::input name="email" class="rounded-full" />'))
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('rounded-full');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::input name="email" placeholder="you@example.com" required autofocus />'))
        ->toContain('placeholder="you@example.com"')
        ->toContain('required')
        ->toContain('autofocus');
});
