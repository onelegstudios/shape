<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Onelegstudios\Shape\IconSlots;

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

it('crops a picture to the circle rather than squashing it into one', function () {
    // `size` is a width and a height, and a photograph of a person is taller
    // than it is wide. Without this the face arrives stretched.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" />'))
        ->toContain('[:where(&amp;)]:object-cover');
});

it('does not ask the initials to fit anything', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" />'))->not->toContain('object-cover');
});

it('squares the circle when it is asked to', function () {
    // `square` is the corners and nothing else. The size, the paint and the
    // crop are all the same avatar; only the radius moves.
    expect(Blade::render('<x-shape::avatar initials="AL" square />'))
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->not->toContain('rounded-full');
});

it('is a circle when it is not', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" />'))
        ->toContain('[:where(&amp;)]:rounded-full')
        ->not->toContain('rounded-shape');
});

it('takes the library radius rather than one of its own per size', function () {
    // Every size squares to the same `--radius-shape` the button and the card
    // take, so a squared avatar beside either of them agrees with it.
    foreach (['xs', 'sm', 'base', 'lg'] as $size) {
        expect(Blade::render("<x-shape::avatar initials=\"AL\" size=\"{$size}\" square />"))
            ->toContain('[:where(&amp;)]:rounded-shape ');
    }
});

it('squares a picture as well as initials', function () {
    // One set of classes for both elements. The crop is unchanged: a squared
    // avatar is still a fixed box, so the photograph still has to fill it.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" square />'))
        ->toContain('<img')
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('[:where(&amp;)]:object-cover')
        ->not->toContain('rounded-full');
});

it('holds a glyph where a call site has neither a face nor a name', function () {
    $html = Blade::render('<x-shape::avatar icon="shape-user" alt="Unassigned" />');

    expect($html)
        ->toContain('data-shape-avatar')
        ->toContain('data-shape-icon')
        ->not->toContain('<img');
});

it('has a person to wear that is an example rather than vocabulary', function () {
    // `shape-user` ships so the previews on this component's page render for a
    // reader who has generated nothing. It is deliberately not a slot: nothing
    // resolves it, so there is nothing for a set to be kept level with, and
    // declaring it would report a coverage gap to every application that has
    // already replaced its icons. The person in real avatars is generated.
    expect(IconSlots::fromConfig()->has('shape-user'))->toBeFalse()
        ->and(Blade::render('<x-shape::avatar icon="shape-user" />'))->toContain('data-shape-icon');
});

it('still shows nothing when it has nothing to show', function () {
    // `shape-user` ships and this component does not default to it. A default
    // would have had to ask whether the initials were there to be preferred,
    // which is the branch `initials` stays safe by never having.
    expect(Blade::render('<x-shape::avatar />'))
        ->toContain('data-shape-avatar')
        ->not->toContain('data-shape-icon');
});

it('fills the circle with the first of src, icon and initials it was given', function () {
    // One order, and a prop bound to null drops through to the next. This is
    // not a runtime fallback: a picture that fails to load leaves a broken
    // image rather than a glyph, exactly as it does over initials.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" icon="shape-user" initials="AL" />'))
        ->toContain('<img src="/ada.jpg"')
        ->not->toContain('data-shape-icon')
        ->and(Blade::render('<x-shape::avatar icon="shape-user" initials="AL" />'))
        ->toContain('data-shape-icon')
        ->not->toContain('>AL<')
        ->and(Blade::render('<x-shape::avatar initials="AL" />'))
        ->toContain('>AL<')
        ->not->toContain('data-shape-icon');
});

it('leaves the initials safe to fold by branching on the icon instead', function () {
    // The order above is settled by this: asking whether initials are present
    // would make `initials` a prop this component branches on, and it is
    // declared safe. Only one of the two can take the branch.
    $source = (string) file_get_contents(__DIR__.'/../../resources/views/shape/avatar/avatar.blade.php');

    expect($source)
        ->toContain("safe: ['initials', 'alt', 'tone']")
        ->toContain('@if ($icon)')
        ->not->toContain('($initials)');
});

it('sizes the glyph from the circle rather than from a prop of its own', function (string $size, string $class) {
    // About half the circle, and no `icon-size` to restate what `size` said.
    // `xs` is the one that misses: 16px is the smallest drawing a set has.
    expect(Blade::render("<x-shape::avatar icon=\"shape-user\" size=\"{$size}\" />"))
        ->toContain($class);
})->with([
    ['xs', '[:where(&amp;)]:size-4'],
    ['sm', '[:where(&amp;)]:size-4'],
    ['base', '[:where(&amp;)]:size-5'],
    ['lg', '[:where(&amp;)]:size-6'],
]);

it('draws the glyph solid at every size', function (string $size) {
    // An icon left to itself takes the stroked drawing at `base`, which is the
    // size an `lg` avatar asks for. Four avatars in a row wear one weight.
    expect(Blade::render("<x-shape::avatar icon=\"shape-user\" size=\"{$size}\" />"))
        ->toContain('fill="currentColor"')
        ->not->toContain('stroke-width');
})->with(['xs', 'sm', 'base', 'lg']);

it('keeps the name in text beside the glyph rather than on it', function () {
    // The glyph is a picture of a name the same way the initials are, so it
    // arrives `aria-hidden` from the icon component and `alt` is carried in the
    // same screen-reader-only span.
    $html = Blade::render('<x-shape::avatar icon="shape-user" alt="Unassigned" />');

    expect($html)
        ->toContain('aria-hidden="true"')
        ->toContain('<span class="sr-only">Unassigned</span>');
});

it('paints the glyph with the variant, which it inherits rather than is given', function () {
    // `currentColor`, so the ink the circle resolved is the ink the glyph takes.
    expect(Blade::render('<x-shape::avatar icon="shape-user" tone="brand" variant="solid" />'))
        ->toContain('text-[var(--shape-tone-fg)]')
        ->toContain('data-shape-icon');
});

it('squares a glyph as well as initials and pictures', function () {
    expect(Blade::render('<x-shape::avatar icon="shape-user" alt="Unassigned" square />'))
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('data-shape-icon')
        ->not->toContain('rounded-full');
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
