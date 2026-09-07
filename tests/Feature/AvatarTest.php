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

it('paints one of three variants from the tone variables', function (string $variant, string $class) {
    expect(Blade::render("<x-shape::avatar initials=\"AL\" tone=\"brand\" variant=\"{$variant}\" />"))
        ->toContain($class)
        ->toContain("data-shape-variant=\"{$variant}\"");
})->with([
    ['subtle', 'bg-[var(--shape-tone-tint)]'],
    ['solid', 'bg-[var(--shape-tone)]'],
    ['outline', 'border-[var(--shape-tone-border-strong)]'],
]);

it('rings the outline arm in the tone rather than the neutral chrome border', function () {
    // The outline badge and button take `--shape-tone-border`, which never takes
    // a tone, because a control is a control whatever it means. An avatar with no
    // fill has nothing else carrying the tone, so its ring carries it.
    $html = Blade::render('<x-shape::avatar initials="AL" tone="brand" variant="outline" />');

    expect($html)
        ->toContain('border-[var(--shape-tone-border-strong)]')
        ->not->toContain('border-[var(--shape-tone-border)]');
});

it('carries a tone so the variants have variables to read', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" tone="danger" />'))
        ->toContain('data-shape-tone="danger"')
        ->and(Blade::render('<x-shape::avatar initials="AL" />'))
        ->toContain('data-shape-tone="neutral"');
});

it('takes its ink from the tone rather than from whatever surface it landed on', function () {
    // `--shape-fg-muted` is republished by every surface, so initials inside a
    // solid alert used to take that alert's white onto their own pale circle.
    $html = Blade::render('<x-shape::avatar initials="AL" />');

    expect($html)
        ->toContain('text-[var(--shape-tone-ink)]')
        ->not->toContain('--shape-fg-muted');
});

it('paints the variant on a picture as well as on initials', function () {
    // One set of classes for both elements, so this is no new branch. The fill
    // is the ground under a transparent image and while any image is arriving.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" tone="brand" variant="outline" />'))
        ->toContain('<img')
        ->toContain('border-[var(--shape-tone-border-strong)]')
        ->toContain('data-shape-variant="outline"');
});

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
