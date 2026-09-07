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
        ->toContain("safe: ['initials', 'alt', 'tone', 'badgeTone']")
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

it('lets a call site name the glyph style, which no class could reach', function () {
    // `solid` is a default and not a constant. Every other decision here is a
    // class a call site can beat with its own; a style picks which of the set's
    // drawings renders, so written into the tag it would have been the one
    // setting with no way round it.
    expect(Blade::render('<x-shape::avatar icon="shape-user" icon-variant="outline" />'))
        ->toContain('stroke-width')
        ->not->toContain('fill="currentColor"');
});

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
        ->toContain('[&amp;_[data-shape-avatar]]:ring-2')
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

it('marks the corner with a dot when it is asked bare', function () {
    $html = Blade::render('<x-shape::avatar initials="AL" badge badge-tone="success" />');

    expect($html)
        ->toContain('data-shape-avatar-badge')
        ->toContain('data-shape-tone="success"')
        ->toContain('size-2.5')
        ->toContain('bg-[var(--shape-tone)]');
});

it('puts anything else it is given inside the mark as text', function () {
    // One prop for both, because a dot is a badge with nothing in it. A `badge`
    // and a `badge-count` would ask the call site to name the shape it wanted.
    expect(Blade::render('<x-shape::avatar initials="AL" badge="3" />'))
        ->toContain('data-shape-avatar-badge')
        ->toContain('>3</span>')
        ->toContain('min-w-4.5')
        ->not->toContain('size-2.5');
});

it('makes a count the dot with room for a number in it', function (string $size, string $dot, string $count) {
    // Eight pixels more than the dot at every size, never narrower than it is
    // tall, and padding at half a step on the two small circles — what makes a
    // count unreadable on a 24px avatar is the box around the digits.
    expect(Blade::render("<x-shape::avatar initials=\"AL\" size=\"{$size}\" badge />"))
        ->toContain($dot)
        ->and(Blade::render("<x-shape::avatar initials=\"AL\" size=\"{$size}\" badge=\"12\" />"))
        ->toContain($count);
})->with([
    ['xs', 'size-1.5', 'h-3.5 min-w-3.5 px-0.5'],
    ['sm', 'size-2"', 'h-4 min-w-4 px-0.5'],
    ['base', 'size-2.5', 'h-4.5 min-w-4.5 px-1 '],
    ['lg', 'size-3"', 'h-5 min-w-5 px-1.5'],
]);

it('paints nothing at all when there is no badge to paint', function () {
    // Including the wrapper. An avatar without a mark is the element it has
    // always been, with nothing around it.
    expect(Blade::render('<x-shape::avatar initials="AL" />'))
        ->not->toContain('data-shape-avatar-badge')
        ->not->toContain('relative inline-flex shrink-0');
});

it('paints no badge for a count of zero', function () {
    // The prop is read for truth rather than for presence, so `:badge="$unread"`
    // is the whole of the call site and the badge that would have said `0` is
    // one that should not have been there.
    expect(Blade::render('<x-shape::avatar initials="AL" :badge="0" />'))
        ->not->toContain('data-shape-avatar-badge')
        ->and(Blade::render('<x-shape::avatar initials="AL" :badge="null" />'))
        ->not->toContain('data-shape-avatar-badge');
});

it('gives the mark a tone of its own rather than the circle it sits on', function () {
    // A `brand` avatar wearing a green dot is the ordinary case. Inheriting
    // would have made it the one arrangement this component could not draw.
    $html = Blade::render('<x-shape::avatar initials="AL" tone="brand" badge badge-tone="success" />');

    expect($html)
        ->toContain('data-shape-tone="brand"')
        ->toContain('data-shape-tone="success"');
});

it('leaves the mark neutral when it is given no tone', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" badge />'))
        ->toContain('data-shape-avatar-badge data-shape-position="bottom-right" data-shape-tone="neutral"');
});

it('paints the mark solid whatever the circle is painted', function () {
    // `subtle` is a tint, and a tint on a photograph is a pale smudge on a face.
    // Being seen against whatever it landed on is the whole of the job.
    foreach (['subtle', 'solid', 'outline'] as $variant) {
        expect(Blade::render("<x-shape::avatar initials=\"AL\" variant=\"{$variant}\" badge badge-tone=\"success\" />"))
            ->toContain('bg-[var(--shape-tone)] text-[var(--shape-tone-fg)]');
    }
});

it('rings the mark in the page so it has an edge on any photograph', function () {
    // The same pair the group rings its children with. Without it a green dot
    // on a green-shirted photograph has no edge of its own.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" badge badge-tone="success" />'))
        ->toContain('ring-2 ring-white dark:ring-shape-900');
});

it('takes the mark to any of the four corners', function (string $position, string $inset, string $pull) {
    expect(Blade::render("<x-shape::avatar initials=\"AL\" badge badge-position=\"{$position}\" />"))
        ->toContain($inset)
        ->toContain($pull)
        ->toContain("data-shape-position=\"{$position}\"");
})->with([
    ['bottom-right', 'bottom-[14.6%] right-[14.6%]', 'translate-x-1/2 translate-y-1/2'],
    ['bottom-left', 'bottom-[14.6%] left-[14.6%]', '-translate-x-1/2 translate-y-1/2'],
    ['top-right', 'top-[14.6%] right-[14.6%]', 'translate-x-1/2 -translate-y-1/2'],
    ['top-left', 'top-[14.6%] left-[14.6%]', '-translate-x-1/2 -translate-y-1/2'],
]);

it('centres the mark on the edge rather than inscribing it in the corner', function () {
    // The corner of the box is not the corner of the shape: a circle's arc
    // crosses the diagonal 14.6% of the width inside it. Anchored by its own
    // corner instead, a three-digit count would walk across the face while the
    // dot beside it sat on the edge.
    expect(Blade::render('<x-shape::avatar initials="AL" badge="123" />'))
        ->toContain('translate-x-1/2 translate-y-1/2')
        ->toContain('bottom-[14.6%] right-[14.6%]')
        ->not->toContain('bottom-0 right-0');
});

it('squares the mark when the avatar is squared', function () {
    // The same `--radius-shape` the circle took, and the corner it is anchored
    // to becomes that radius rather than half the width.
    expect(Blade::render('<x-shape::avatar initials="AC" badge="12" square />'))
        ->toContain('rounded-shape')
        ->toContain('bottom-[calc(var(--radius-shape)*0.293)] right-[calc(var(--radius-shape)*0.293)]')
        ->and(Blade::render('<x-shape::avatar initials="AC" badge="12" />'))
        ->toContain('bottom-[14.6%]')
        ->not->toContain('rounded-shape');
});

it('sizes the mark from the circle rather than from a prop of its own', function (string $size, string $class) {
    expect(Blade::render("<x-shape::avatar initials=\"AL\" size=\"{$size}\" badge />"))
        ->toContain($class);
})->with([
    ['xs', 'size-1.5'],
    ['sm', 'size-2"'],
    ['base', 'size-2.5'],
    ['lg', 'size-3"'],
]);

it('takes the mark no room, so a row of faces does not move when one lights up', function () {
    // The mark is absolute and its ring is a shadow. It draws outside the box,
    // which is what being centred on an edge means, but it lays out nothing.
    expect(Blade::render('<x-shape::avatar initials="AL" badge />'))
        ->toContain('pointer-events-none absolute')
        ->toContain('<span class="relative inline-flex shrink-0">');
});

it('marks a picture on the same corner it marks initials', function () {
    // One wrapper around whichever element the branch above rendered, so the
    // mark is not a second copy of itself.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" badge badge-tone="success" />'))
        ->toContain('<img src="/ada.jpg"')
        ->toContain('data-shape-avatar-badge')
        ->and(substr_count(Blade::render('<x-shape::avatar src="/ada.jpg" badge />'), 'data-shape-avatar-badge'))
        ->toBe(1);
});

it('keeps the attributes on the avatar rather than moving them to the wrapper', function () {
    // The answer that keeps `class="object-contain"` landing on the picture it
    // was written for. A wrapper holding the bag would move every class a call
    // site has ever passed onto an element that is not the circle.
    $html = Blade::render('<x-shape::avatar src="/ada.jpg" class="object-contain" wire:key="ada" badge />');

    expect($html)
        ->toContain('<span class="relative inline-flex shrink-0">')
        ->toContain('object-contain')
        ->toContain('wire:key="ada"')
        ->not->toContain('<span class="relative inline-flex shrink-0" wire:key');
});

it('says nothing about the mark, because a dot has no words in it', function () {
    // A dot is a colour and a count is a number with no noun attached. Both
    // mean something only in a sentence, and the sentence is `alt`.
    $html = Blade::render('<x-shape::avatar initials="AL" alt="Ada Lovelace, online" badge badge-tone="success" />');

    expect($html)
        ->toContain('<span class="sr-only">Ada Lovelace, online</span>')
        ->and(substr_count($html, 'aria-hidden="true"'))
        ->toBe(2);
});

it('rings the face inside a group rather than the shell around it', function () {
    // A badged avatar arrives wrapped in the shell its mark is positioned
    // against, so ringing the child would draw a square ring around a circle.
    $html = Blade::render(<<<'BLADE'
    <x-shape::avatar.group>
        <x-shape::avatar initials="AL" />
        <x-shape::avatar initials="GH" badge badge-tone="success" />
    </x-shape::avatar.group>
    BLADE);

    expect($html)
        ->toContain('[&amp;_[data-shape-avatar]]:ring-2')
        ->toContain('data-shape-avatar-badge')
        ->not->toContain('[&amp;&gt;*]:ring-2');
});
