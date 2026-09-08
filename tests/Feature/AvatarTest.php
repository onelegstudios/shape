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

it('draws no edge until it is asked for one', function () {
    // Two of the three arms are a fill and nothing else. `border` is what rings
    // them, and until it is passed there is no edge to find.
    expect(Blade::render('<x-shape::avatar initials="AL" tone="brand" />'))
        ->not->toContain('border')
        ->and(Blade::render('<x-shape::avatar initials="AL" tone="brand" variant="solid" />'))
        ->not->toContain('border');
});

it('rings the arm it is asked to ring, in the tone the arm can carry', function (string $variant, string $class) {
    expect(Blade::render("<x-shape::avatar initials=\"AL\" tone=\"brand\" variant=\"{$variant}\" border />"))
        ->toContain('[:where(&amp;)]:border ')
        ->toContain($class);
})->with([
    // The same edge `outline` draws, so the prop and the variant agree about
    // what the tone's edge is.
    ['subtle', 'border-[var(--shape-tone-border-strong)]'],

    // Except on the fill, where that step would read as a highlight, so the
    // edge is the step past the fill instead.
    ['solid', 'border-[var(--shape-tone-hover)]'],

    ['outline', 'border-[var(--shape-tone-border-strong)]'],
]);

it('adds nothing to the outline arm, which is ringed already', function () {
    // The alert's `border` tones an outline that is drawn in neutral chrome.
    // An avatar's outline is the tone's own to begin with, so the ring the prop
    // would draw is the ring that is there.
    $html = Blade::render('<x-shape::avatar initials="AL" tone="brand" variant="outline" />');

    expect(Blade::render('<x-shape::avatar initials="AL" tone="brand" variant="outline" border />'))
        ->toBe($html);
});

it('rings a picture without giving up the fill under it', function () {
    // `outline` was the only edge before this, and taking it dropped the tint —
    // which is the ground a transparent picture sits on and what fills the
    // circle while any picture is arriving.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" border />'))
        ->toContain('<img')
        ->toContain('border-[var(--shape-tone-border-strong)]')
        ->toContain('bg-[var(--shape-tone-tint)]');
});

it('rings a grounded picture and a control the same way', function () {
    // The edge rides the circle's own classes, so it lands on whichever element
    // is the circle rather than on one arrangement of it.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" ground border />'))
        ->toContain('border-[var(--shape-tone-border-strong)]')
        ->and(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" as="button" border />'))
        ->toContain('border-[var(--shape-tone-border-strong)]');
});

it('gives the edge zero specificity, so a heavier one is a class away', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" border />'))
        ->toContain('[:where(&amp;)]:border [:where(&amp;)]:border-[var(--shape-tone-border-strong)]');
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

it('lets a call site ring the circle with a class, in every arrangement', function (string $call) {
    // The docs promise an outside ring is a class rather than a prop, which only
    // holds because the bag reaches the circle however the circle is drawn — the
    // same property that makes `class="object-contain"` reach the picture. A
    // badged avatar is the one worth naming: its bag stays on the face rather
    // than moving to the wrapper the mark is positioned against.
    expect(Blade::render($call))->toContain('ring-2 ring-[#0d1117]');
})->with([
    '<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" class="ring-2 ring-[#0d1117]" />',
    '<x-shape::avatar initials="AL" class="ring-2 ring-[#0d1117]" />',
    '<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" as="button" class="ring-2 ring-[#0d1117]" />',
    '<x-shape::avatar src="/ada.jpg" initials="AL" ground class="ring-2 ring-[#0d1117]" />',
    '<x-shape::avatar initials="AL" badge class="ring-2 ring-[#0d1117]" />',
]);

it('rings nothing of its own, so a class is the whole of the colour', function () {
    // There is no `ring` prop, and the reason is in the docs: a ring is asked for
    // where the ground is not the page, so its colour is the one colour this
    // component cannot know. A default would only be thrown away.
    expect(Blade::render('<x-shape::avatar initials="AL" border />'))->not->toContain('ring');
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

it('leaves the remainder of a group to the call site as an ordinary avatar', function () {
    // The group has no `max`: a slot is already rendered by the time it arrives,
    // so the slice happens where the collection is. What the docs promise is
    // that the remainder needs nothing new — `+3` is initials, hidden the way
    // initials are, with the sentence in `alt`, and the group's ring finds it
    // because it is an avatar like the ones beside it.
    $html = Blade::render(<<<'BLADE'
    <x-shape::avatar.group>
        <x-shape::avatar initials="AL" />
        <x-shape::avatar initials="+3" alt="3 more" />
    </x-shape::avatar.group>
    BLADE);

    expect($html)
        ->toContain('<span aria-hidden="true">+3</span>')
        ->toContain('<span class="sr-only">3 more</span>')
        ->toContain('[&amp;_[data-shape-avatar]]:ring-2')
        ->toContain('data-shape-avatar');
});

it('puts the picture\'s own attributes on the picture in every arrangement', function (string $blade) {
    // The bag reaches the `<img>` on its own in exactly one of the three. A
    // `loading` is not a class, so no selector stands in for it, and an avatar
    // list of photographs is the call site that most wants one.
    expect(Blade::render($blade))->toMatch('/<img[^>]*loading="lazy"/');
})->with([
    '<x-shape::avatar src="/ada.webp" alt="Ada" loading="lazy" />',
    '<x-shape::avatar src="/ada.webp" alt="Ada" initials="AL" ground loading="lazy" />',
    '<x-shape::avatar src="/ada.webp" alt="Ada" as="button" loading="lazy" />',
]);

it('does not leave them on the element the picture sits in', function () {
    // The other half. A `loading` on the `<button>` a control renders is not an
    // attribute that element has, and one on a ground's `<span>` does nothing —
    // so they are lifted off the bag rather than copied from it.
    $html = Blade::render('<x-shape::avatar src="/ada.webp" alt="Ada" as="button" srcset="/ada@2x.webp 2x" />');

    expect($html)
        ->toMatch('/<img[^>]*srcset=/')
        ->not->toMatch('/<button[^>]*srcset=/');
});

it('keeps the rest of the bag where it was', function () {
    // Only the picture's own names move. `class` is the one that most has to
    // stay: it paints the circle, which is the element the picture sits in.
    $html = Blade::render('<x-shape::avatar src="/ada.webp" alt="Ada" as="button" class="ring-2" data-testid="face" referrerpolicy="no-referrer" />');

    expect($html)
        ->toMatch('/<button[^>]*class="[^"]*ring-2/')
        ->toMatch('/<button[^>]*data-testid="face"/')
        ->toMatch('/<img[^>]*referrerpolicy="no-referrer"/');
});

it('gives its own defaults zero specificity so caller classes win', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" class="rounded-shape" />'))
        ->toContain('[:where(&amp;)]:rounded-full')
        ->toContain('rounded-shape');
});

it('lets a call site paint the circle without declaring a tone', function () {
    // The docs answer auto-colour this way: a palette resolved per person and
    // passed as `class`. Both halves of `subtle` have to be beatable for it to
    // work, and the element goes on reporting the tone it actually carries —
    // which is the caveat the docs state rather than the reader discovering it.
    $html = Blade::render('<x-shape::avatar initials="AL" class="bg-indigo-100 text-indigo-800" />');

    expect($html)
        ->toContain('[:where(&amp;)]:bg-[var(--shape-tone-tint)]')
        ->toContain('[:where(&amp;)]:text-[var(--shape-tone-ink)]')
        ->toContain('bg-indigo-100')
        ->toContain('text-indigo-800')
        ->toContain('data-shape-tone="neutral"');
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

it('stays a span until it is asked for a control', function () {
    // The default is a picture of a person and nothing that can be pressed, so
    // none of the chrome a control owes is paid for by the avatars that are not
    // one.
    $html = Blade::render('<x-shape::avatar initials="AL" alt="Ada Lovelace" />');

    expect($html)
        ->toContain('<span')
        ->not->toContain('<button')
        ->not->toContain('focus-visible:outline-2')
        ->not->toContain('hover:opacity-80');
});

it('becomes a button when it is asked to be one', function () {
    $html = Blade::render('<x-shape::avatar initials="AL" alt="Ada Lovelace" as="button" />');

    expect($html)
        ->toContain('<button type="button"')
        ->toContain('data-shape-avatar')
        ->toContain('AL');
});

it('becomes a link on an href without being told to', function () {
    // Middle-click, "open in new tab" and the status bar all work for a link and
    // none of them work for a button pretending to be one — the same resolution
    // the tab and the menu item make.
    expect(Blade::render('<x-shape::avatar initials="AL" alt="Ada" href="/people/ada" />'))
        ->toContain('<a ')
        ->toContain('href="/people/ada"')
        ->not->toContain('<button');
});

it('lets as beat an href, for a link that is really a control', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" href="/people/ada" />'))
        ->toContain('<button type="button"')
        ->not->toContain('<a ');
});

it('makes the control the circle rather than a button around one', function () {
    // Same element, same classes, same bag. A wrapper would have moved every
    // class a call site has ever passed onto something that is not the circle,
    // which is the argument the badge's shell loses from the other side.
    $html = Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" class="ring-4" wire:click="open" />');

    expect($html)
        ->toContain('<button type="button"')
        ->toContain('[:where(&amp;)]:size-10')
        ->toContain('[:where(&amp;)]:rounded-full')
        ->toContain('ring-4')
        ->toContain('wire:click="open"')
        ->and(substr_count($html, 'data-shape-avatar'))
        ->toBe(1);
});

it('takes an as of div, for an avatar inside something already clickable', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="div" />'))
        ->toContain('<div ')
        ->not->toContain('<button');
});

it('puts a picture inside the control, because an img cannot be pressed', function () {
    // `<img>` takes no children and takes no press. It is the one arm the
    // element cannot render, so under `as` the photograph becomes a child and
    // the control takes the circle's classes.
    $html = Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" as="button" />');

    expect($html)
        ->toContain('<button type="button"')
        ->toContain('<img src="/ada.jpg"')
        ->toContain('size-full')
        ->toContain('[:where(&amp;)]:object-cover');
});

it('leaves the crop reachable past the control', function () {
    // The bag lands on the control the way it always lands on the circle, so
    // letterboxing a picture that is now a child is one selector further out.
    // The inner rule is zero-specificity, so the call site's wins.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada" as="button" class="[&>img]:object-contain" />'))
        ->toContain('[&>img]:object-contain')
        ->toContain('[:where(&amp;)]:object-cover');
});

it('announces a picture in a control once, not twice', function () {
    // Inside a control the photograph is a picture of a name exactly as the
    // initials are, so it is hidden and the name is carried in the same
    // screen-reader text. The bare picture keeps its real `alt`, because there
    // is no element around it to put the text in.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" as="button" />'))
        ->toContain('alt=""')
        ->toContain('<span class="sr-only">Ada Lovelace</span>')
        ->and(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada Lovelace" />'))
        ->toContain('alt="Ada Lovelace"')
        ->not->toContain('sr-only');
});

it('gives a control the library\'s focus ring and nothing louder', function () {
    // The ring is chrome, and a control that focused differently from every
    // other control would be reporting a difference that is not there.
    expect(Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" />'))
        ->toContain('focus-visible:outline-2')
        ->toContain('focus-visible:outline-offset-2')
        ->toContain('focus-visible:outline-[var(--shape-ring)]');
});

it('dims on hover rather than repainting, because the paint is the meaning', function () {
    // The button's paint is chrome and its hover is a louder version of it. An
    // avatar's paint is what the avatar means, and no `--shape-tone-hover`
    // reaches a photograph.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" alt="Ada" tone="brand" as="button" />'))
        ->toContain('hover:opacity-80')
        ->toContain('transition-opacity')
        ->not->toContain('hover:bg-');
});

it('dims and stops the pointer on both spellings of disabled', function () {
    // An anchor cannot be disabled, which is why the button carries both.
    expect(Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" />'))
        ->toContain('disabled:pointer-events-none')
        ->toContain('disabled:opacity-50')
        ->toContain('aria-disabled:pointer-events-none')
        ->toContain('aria-disabled:opacity-50');
});

it('dims a badged control from its shell, so the mark dims with it', function () {
    // The mark is a sibling of the circle, so a dim on the circle would leave a
    // presence dot at full strength on a face that had faded.
    $html = Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" badge badge-tone="success" />');

    expect($html)
        ->toContain('<span class="relative inline-flex shrink-0 transition-opacity duration-100 has-hover:opacity-80 has-disabled:opacity-50 has-aria-disabled:opacity-50">')
        ->toContain('aria-disabled:pointer-events-none');
});

it('dims a badged control once rather than twice', function () {
    // `opacity` on the mark as well as the shell would fade the pair and then
    // fade the mark again inside it — and `opacity` on the mark at all is what
    // shows the circle's own edge through the dot that is covering it.
    $html = Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" badge badge-tone="success" />');

    expect($html)
        ->not->toContain('peer')
        ->and(substr_count($html, 'transition-opacity duration-100'))->toBe(1)
        ->and(substr_count($html, 'opacity-80'))->toBe(1)
        ->and(substr_count($html, 'opacity-50'))->toBe(2);
});

it('leaves an unbadged control dimming itself', function () {
    // There is no shell on an avatar with no mark to position, and nothing
    // beside the circle to keep in step with.
    expect(Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" />'))
        ->toContain('hover:opacity-80')
        ->toContain('disabled:opacity-50')
        ->toContain('aria-disabled:opacity-50')
        ->not->toContain('has-hover:opacity-80');
});

it('leaves a badged avatar that is not a control with a bare shell', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" badge badge-tone="success" />'))
        ->toContain('<span class="relative inline-flex shrink-0">')
        ->not->toContain('has-hover:opacity-80');
});

it('squares and badges a control the way it squares and badges a span', function () {
    // The control is the circle, so everything resolved above it lands on it.
    $html = Blade::render('<x-shape::avatar initials="OL" alt="One Leg Studios" as="button" square badge="12" badge-tone="brand" />');

    expect($html)
        ->toContain('<span class="relative inline-flex shrink-0 transition-opacity')
        ->toContain('<button type="button"')
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain('data-shape-avatar-badge')
        ->toContain('rounded-shape ring-2');
});

it('rings a control inside a group like any other face', function () {
    // The group finds `[data-shape-avatar]`, which is whichever element the
    // avatar turned out to be.
    $html = Blade::render(<<<'BLADE'
    <x-shape::avatar.group>
        <x-shape::avatar initials="AL" alt="Ada" href="/people/ada" />
        <x-shape::avatar initials="GH" alt="Grace" href="/people/grace" />
    </x-shape::avatar.group>
    BLADE);

    expect($html)
        ->toContain('[&amp;_[data-shape-avatar]]:ring-2')
        ->and(substr_count($html, '<a '))
        ->toBe(2);
});

it('still resolves a glyph and initials inside a control', function () {
    expect(Blade::render('<x-shape::avatar icon="shape-user" alt="Unassigned" as="button" />'))
        ->toContain('<button type="button"')
        ->toContain('data-shape-icon')
        ->and(Blade::render('<x-shape::avatar initials="AL" alt="Ada" as="button" />'))
        ->toContain('<span aria-hidden="true">AL</span>');
});

it('does not borrow the button\'s element, whose default is a button', function () {
    // `button.element` defaults to a `<button>`, because the button, the tab and
    // the menu item are controls before they are anything else. An avatar's
    // default is a `<span>`, and reusing that file would have made every avatar
    // that passed no `as` a control.
    $source = (string) file_get_contents(__DIR__.'/../../resources/views/shape/avatar/element.blade.php');

    expect($source)
        ->toContain('<span {{ $attributes }}>')
        ->and(Blade::render('<x-shape::avatar.element>AL</x-shape::avatar.element>'))
        ->toContain('<span')
        ->not->toContain('<button');
});

it('lays a picture over the letters when it is asked for a ground', function () {
    // A Gravatar asked for `d=blank` answers for a stranger with a transparent
    // GIF, and a transparent GIF over a tint is an empty circle where two
    // letters would have done.
    $html = Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" alt="Ada Lovelace" ground />');

    expect($html)
        ->toContain('>AL<')
        ->toContain('<img src="/ada.jpg"')
        ->toContain('absolute inset-0');
});

it('takes the same ladder for the ground that it takes for the circle', function () {
    // The glyph sits above the initials under a picture exactly as it does
    // without one. There is no second order to learn.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" icon="shape-user" initials="AL" ground />'))
        ->toContain('data-shape-icon')
        ->not->toContain('>AL<');
});

it('makes the circle a positioning context only when something is laid over it', function () {
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" ground />'))
        ->toContain('relative')
        ->and(Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" />'))
        ->not->toContain('relative')
        ->and(Blade::render('<x-shape::avatar initials="AL" />'))
        ->not->toContain('relative');
});

it('goes on replacing the letters when it is not asked', function () {
    // The ladder is unchanged for every call site that never wrote the word:
    // `src` wins outright and the letters are not rendered at all.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" />'))
        ->toContain('<img src="/ada.jpg"')
        ->not->toContain('>AL<');
});

it('keeps the bare picture bare, so the bag stays on the picture', function () {
    // Which is the whole reason `ground` is asked for rather than assumed: a
    // ground needs an element to sit in, and that moves the bag one element out
    // exactly as `as` does.
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" class="object-contain" />'))
        ->toContain('<img src="/ada.jpg"')
        ->toContain('object-contain" data-shape-avatar')
        ->and(Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" ground class="[&>img]:object-contain" />'))
        ->toContain('<span')
        ->toContain('[&>img]:object-contain');
});

it('names the person once under a ground, not twice', function () {
    // The picture is a picture of a name exactly as the letters are, so it is
    // hidden and the name is carried in the text beside them — the same answer
    // the control arm gives.
    $html = Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" alt="Ada Lovelace" ground />');

    expect($html)
        ->toContain('alt=""')
        ->toContain('<span class="sr-only">Ada Lovelace</span>')
        ->and(substr_count($html, 'Ada Lovelace'))->toBe(1);
});

it('grounds a picture inside a control as well', function () {
    expect(Blade::render('<x-shape::avatar src="/ada.jpg" initials="AL" alt="Ada" as="button" ground />'))
        ->toContain('<button type="button"')
        ->toContain('>AL<')
        ->toContain('absolute inset-0');
});

it('ignores a ground when there is no picture to lay over one', function () {
    expect(Blade::render('<x-shape::avatar initials="AL" ground />'))
        ->toContain('>AL<')
        ->not->toContain('<img')
        ->not->toContain('absolute');
});
