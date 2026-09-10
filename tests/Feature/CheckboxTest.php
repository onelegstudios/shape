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

it('moves the box, the tick, the gap and the text with one word', function (string $size, string $box, string $tick, string $gap, string $type) {
    // The whole point of the prop: a box that grew and left its label at 14px
    // would read as one control set next to another rather than as a larger one.
    $html = Blade::render("<x-shape::checkbox name=\"terms\" label=\"I agree\" size=\"{$size}\" />");

    expect($html)
        ->toContain("appearance-none {$box} shrink-0")
        ->toContain("pointer-events-none {$tick}")
        ->toContain("inline-flex items-start {$gap}")
        ->toContain("{$type} font-medium")
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['xs', 'size-3', 'size-2.5', 'gap-1.5', 'text-xs'],
    ['sm', 'size-3.5', 'size-3', 'gap-2', 'text-sm'],
    ['base', 'size-4', 'size-3.5', 'gap-2.5', 'text-sm'],
    ['lg', 'size-5', 'size-4', 'gap-3', 'text-base'],
    ['xl', 'size-6', 'size-5', 'gap-3.5', 'text-lg'],
]);

it('draws the tick at the box it is given rather than at the icon step it fetched', function () {
    // `size` picks which drawing is fetched — the 16px solid is a different path
    // from the 20px one — and the class picks the box it is drawn in. It works
    // because an icon's own size class carries zero specificity.
    expect(Blade::render('<x-shape::checkbox name="terms" size="xl" />'))
        ->toContain('viewBox="0 0 20 20"')
        ->toContain('[:where(&amp;)]:size-5')
        ->toContain('pointer-events-none size-5');
});

it('keeps the box on the first line at every step', function () {
    // Half the difference between the line and the box is two pixels all the way
    // up the scale, so the offset is not in the match.
    foreach (['xs', 'base', 'xl'] as $size) {
        expect(Blade::render("<x-shape::checkbox name=\"terms\" size=\"{$size}\" />"))
            ->toContain('grid place-items-center pt-0.5');
    }
});
