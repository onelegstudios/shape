<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a picture when it has one and initials when it does not', function () {
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" />'))
        ->toContain('<img src="/ada.jpg"')
        ->toContain('data-shape-avatar')
        ->and(Blade::render('<x-shape::avatar initials="AL" alt="Ada Lovelace" />'))
        ->toContain('AL')
        ->not->toContain('<img');
});

it('never emits an image element with no source', function () {
    // `<img src="">` re-requests the current page. This is the branch that makes
    // `src` unsafe, and it is worth the fold it costs.
    expect(Blade::render('<x-shape::avatar initials="AL" />'))->not->toContain('src=""');
});

it('makes the person the accessible name, not their initials', function () {
    // Initials are a picture of a name. Announcing "A L" is worse than
    // announcing nothing, so they are hidden and the name is carried in text.
    $html = Blade::render('<x-shape::avatar initials="AL" alt="Ada Lovelace" />');

    expect($html)
        ->toContain('aria-hidden="true"')
        ->toContain('<span class="sr-only">Ada Lovelace</span>');
});

it('announces nothing when it sits beside a name already on the page', function () {
    $html = Blade::render('<x-shape::avatar initials="AL" />');

    expect($html)->toContain('<span class="sr-only"></span>');
});

it('takes one of four sizes', function (string $size, string $class) {
    expect(Blade::render("<x-shape::avatar initials=\"AL\" size=\"{$size}\" />"))
        ->toContain($class)
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['xs', '[:where(&amp;)]:size-6'],
    ['sm', '[:where(&amp;)]:size-8'],
    ['base', '[:where(&amp;)]:size-10'],
    ['lg', '[:where(&amp;)]:size-12'],
]);

it('overlaps a group with two utilities and no stylesheet rule', function () {
    // The ring is what keeps the face underneath from reading as a smudge.
    // Which face is on top is DOM order, because choosing it would be a z-index.
    $html = Blade::render(<<<'BLADE'
    <x-shape::avatar.group>
        <x-shape::avatar initials="AL" />
        <x-shape::avatar initials="GH" />
    </x-shape::avatar.group>
    BLADE);

    expect($html)
        ->toContain('data-shape-avatar-group')
        ->toContain('[:where(&amp;)]:-space-x-2')
        ->toContain('[&amp;&gt;*]:ring-2')
        ->not->toContain('z-');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" class="rounded-shape" />'))
        ->toContain('[:where(&amp;)]:rounded-full')
        ->toContain('rounded-shape');
});

it('passes attributes straight through', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" wire:key="ada" title="Ada" />'))
        ->toContain('wire:key="ada"')
        ->toContain('title="Ada"');
});
