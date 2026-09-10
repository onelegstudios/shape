<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a radio inside its own label', function () {
    expect(Blade::render('<x-shape::radio name="billing" value="monthly" label="Monthly" />'))
        ->toContain('type="radio"')
        ->toContain('Monthly')
        ->toContain('data-shape-radio');
});

it('takes the shared group name from the fieldset it sits in', function () {
    // A radio only means anything as part of a group, and no call site should
    // have to repeat the name that defines the group.
    $html = Blade::render(<<<'BLADE'
        <x-shape::field as="fieldset" field-name="billing">
            <x-shape::label as="legend">Billing period</x-shape::label>
            <x-shape::radio value="monthly" label="Monthly" />
            <x-shape::radio value="yearly" label="Yearly" />
        </x-shape::field>
    BLADE);

    expect(substr_count($html, 'name="billing"'))->toBe(2)
        ->and($html)->toContain('id="billing-monthly"')
        ->and($html)->toContain('id="billing-yearly"')
        // The group gets its accessible name from a real legend.
        ->and($html)->toContain('<legend');
});

it('draws its dot rather than using the platform control', function () {
    expect(Blade::render('<x-shape::radio name="billing" value="monthly" />'))
        ->toContain('appearance-none')
        ->toContain('[:where(&amp;)]:rounded-full')
        ->toContain('peer-checked:opacity-100');
});

it('reads its checked fill from the tone variables', function () {
    expect(Blade::render('<x-shape::radio name="billing" value="monthly" tone="brand" />'))
        ->toContain('data-shape-tone="brand"')
        ->toContain('checked:bg-[var(--shape-tone)]');
});

it('describes itself when it renders a description', function () {
    expect(Blade::render('<x-shape::radio name="billing" value="yearly" label="Yearly" description="Two months free." />'))
        ->toContain('aria-describedby="billing-yearly-description"')
        ->toContain('id="billing-yearly-description"');
});

it('dims only its own label when disabled', function () {
    $html = Blade::render(<<<'BLADE'
        <x-shape::field as="fieldset" field-name="billing">
            <x-shape::radio value="monthly" label="Monthly" disabled />
            <x-shape::radio value="yearly" label="Yearly" />
        </x-shape::field>
    BLADE);

    // The field dims direct children only, so a disabled radio cannot dim the
    // labels of its siblings.
    expect($html)->toContain('[&amp;:has(&gt;[data-shape-control]:disabled)&gt;[data-shape-label]]:opacity-50');
});

it('takes the checkbox\'s boxes and puts a third of each in the middle', function (string $size, string $box, string $dot) {
    $html = Blade::render("<x-shape::radio name=\"plan\" value=\"pro\" label=\"Pro\" size=\"{$size}\" />");

    expect($html)
        ->toContain("appearance-none {$box} shrink-0")
        ->toContain("pointer-events-none {$dot} rounded-full")
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['xs', 'size-3', 'size-1'],
    ['sm', 'size-3.5', 'size-1.5'],
    ['base', 'size-4', 'size-1.5'],
    ['lg', 'size-5', 'size-2'],
    ['xl', 'size-6', 'size-2.5'],
]);
