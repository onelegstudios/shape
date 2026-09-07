<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a block that stays on the page', function () {
    $html = Blade::render('<x-shape::alert>Your trial ends on Friday.</x-shape::alert>');

    expect($html)
        ->toContain('data-shape-alert')
        ->toContain('Your trial ends on Friday.')
        ->toContain('data-shape-tone="neutral"');
});

it('carries semantics on the tone attribute', function () {
    expect(Blade::render('<x-shape::alert tone="danger">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-tone="danger"');
});

it('resolves a glyph for every state so colour is never the only signal', function (string $tone) {
    expect(Blade::render("<x-shape::alert tone=\"{$tone}\">Message</x-shape::alert>"))
        ->toContain('data-shape-icon');
})->with(['success', 'danger', 'warning', 'info']);

it('draws nothing for the emphasis tones, which are not states', function (string $tone) {
    // The brand is the ramp an application retints and the accent is the one
    // kept for what is worth noticing, so a glyph on either would make it a
    // fifth state whose colour is whatever the application happens to choose.
    // `info` is that state, in a blue that survives the retint.
    expect(Blade::render("<x-shape::alert tone=\"{$tone}\">On the beta.</x-shape::alert>"))
        ->toContain("data-shape-tone=\"{$tone}\"")
        ->not->toContain('data-shape-icon');
})->with(['brand', 'accent']);

it('gives each state a glyph of its own rather than reusing one', function () {
    $glyphs = collect(['success', 'danger', 'warning', 'info'])
        ->map(fn (string $tone) => Blade::render("<x-shape::alert tone=\"{$tone}\">Message</x-shape::alert>"))
        ->map(fn (string $html) => preg_match('/<path[^>]*d="([^"]+)"/', $html, $m) ? $m[1] : null);

    expect($glyphs->filter()->unique())->toHaveCount(4);
});

it('lets a caller opt out of the glyph', function () {
    expect(Blade::render('<x-shape::alert tone="success" :icon="false">Done</x-shape::alert>'))
        ->not->toContain('data-shape-icon');
});

it('publishes the tone as its own foreground contract', function () {
    // The point of `data-shape-surface="tint"`: a muted paragraph inside a
    // coloured alert has to read a dialled-back version of the tone, not the
    // global grey that would otherwise apply on a coloured background.
    expect(Blade::render('<x-shape::alert tone="danger">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-surface="tint"');
});

it('is quiet by default, because most alerts are not the loudest thing on the page', function () {
    expect(Blade::render('<x-shape::alert tone="danger">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-variant="subtle"')
        ->toContain('bg-[var(--shape-tone-tint)]');
});

it('paints each variant with the same tone variables rather than a colour of its own', function (string $variant, string $paint) {
    // Variant is loudness and tone is meaning, and the two stay apart: every
    // arm reads `--shape-tone-*`, so a variant never has to know a hue and the
    // set does not multiply into a variant by tone matrix.
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\">Card declined.</x-shape::alert>"))
        ->toContain("data-shape-variant=\"{$variant}\"")
        ->toContain($paint);
})->with([
    ['subtle', 'bg-[var(--shape-tone-tint)]'],
    ['solid', 'bg-[var(--shape-tone)]'],
    ['outline', 'border-[var(--shape-tone-border)]'],
]);

it('paints nothing at rest for the ghost variant', function () {
    // The quietest arm: no fill and no border, so the message sits in the flow
    // of whatever already boxes it. Only the paint is dropped — the padding is
    // added above the match, so a ghost alert lines up with the other three
    // and the dismiss control's negative margins still land in the corner.
    $rendered = Blade::render('<x-shape::alert tone="danger" variant="ghost">Card declined.</x-shape::alert>');

    expect($rendered)
        ->toContain('data-shape-variant="ghost"')
        ->toContain('[:where(&amp;)]:p-4')
        ->not->toContain('[:where(&amp;)]:bg-')
        ->not->toContain('border-[var(--shape-tone-border)]');
});

it('shows a ghost alert its own bounds on hover, in the fill it would have had', function () {
    // The tint is `subtle`'s resting background and the ghost button's hover,
    // read from the same variable, so a ghost alert and the ghost control
    // inside it agree without either knowing about the other. It is the only
    // arm that moves, so the transition is scoped to it rather than the root.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost">Card declined.</x-shape::alert>'))
        ->toContain('hover:bg-[var(--shape-tone-tint)]')
        ->toContain('transition-colors');
});

it('leaves the other variants still on hover, because only ghost is unpainted', function (string $variant) {
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\">Card declined.</x-shape::alert>"))
        ->not->toContain('hover:bg-')
        ->not->toContain('transition-colors');
})->with(['subtle', 'solid', 'outline']);

it('draws no border by default, because the fill is already the boundary', function (string $variant) {
    // Three of the four arms paint something, and where there is a fill there
    // is an edge. `border` is the opt-in for where there is not one — or where
    // the alert has to hold its own next to something already drawn with one.
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\">Card declined.</x-shape::alert>"))
        ->not->toContain('[:where(&amp;)]:border ');
})->with(['subtle', 'solid', 'ghost']);

it('draws the border in a step of the tone rather than a palette of its own', function (string $variant, string $paint) {
    // The same rule the fills follow: every arm reads `--shape-tone-*`, so a
    // border never has to know a hue and a retheme carries it with everything
    // else. Which step is the only thing that varies, and it follows what the
    // edge sits against.
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\" border>Card declined.</x-shape::alert>"))
        ->toContain('[:where(&amp;)]:border ')
        ->toContain($paint);
})->with([
    // Both arms read the same variable, because the edge is doing the same job
    // in each: bounding the wash on one, and the whole of the paint on the
    // other. `--shape-tone-border-strong` is the tone's answer to the neutral
    // `--shape-tone-border` the outline arm takes by default.
    ['subtle', 'border-[var(--shape-tone-border-strong)]'],
    ['outline', 'border-[var(--shape-tone-border-strong)]'],
    // The step past the fill, not the step past the tint — a 200 edge on a 700
    // fill reads as a highlight. Darker in light mode and brighter in dark,
    // which is why it is the tone's hover and not a fixed darkening.
    ['solid', 'border-[var(--shape-tone-hover)]'],
]);

it('leaves the outline border grey until it is asked for the tone', function () {
    // The one arm the prop adds no border to, because it has one already. All
    // it decides there is the colour, and the default stays the neutral edge
    // the outline button and badge take, so the three go on agreeing.
    expect(Blade::render('<x-shape::alert tone="danger" variant="outline">Card declined.</x-shape::alert>'))
        ->toContain('border-[var(--shape-tone-border)]');

    expect(Blade::render('<x-shape::alert tone="danger" variant="outline" border>Card declined.</x-shape::alert>'))
        ->not->toContain('border-[var(--shape-tone-border)]');
});

it('shows the ghost border only under the pointer, with the fill it arrives with', function () {
    // The arm stays unpainted at rest, so the edge waits with the tint rather
    // than drawing a box around nothing. `transition-colors` is already in the
    // ghost arm and carries the border with the background.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost" border>Card declined.</x-shape::alert>'))
        ->toContain('hover:border-[var(--shape-tone-border-strong)]')
        ->toContain('hover:bg-[var(--shape-tone-tint)]')
        ->toContain('transition-colors');
});

it('reserves the ghost border before it paints it, so the hover moves nothing', function () {
    // A border that appears on hover is a pixel of layout that appears with
    // it. Drawing it transparent at rest is what keeps the text still.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost" border>Card declined.</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:border-transparent');
});

it('keeps the other variants still on hover once they have a border', function (string $variant) {
    // Only ghost moves. A border on the other three is drawn at rest and stays
    // where it is, so none of them gains a transition it did not have.
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\" border>Card declined.</x-shape::alert>"))
        ->not->toContain('hover:border-')
        ->not->toContain('transition-colors');
})->with(['subtle', 'solid', 'outline']);

it('moves the foreground contract with the variant, not just the background', function () {
    // The failure this prevents is the one the tint surface was built for, a
    // step louder: a solid alert fills with the tone, so the readable
    // foreground is the tone's own `-fg` rather than its ink. Publishing `tint`
    // here would put dark ink on a saturated fill.
    expect(Blade::render('<x-shape::alert tone="danger" variant="solid">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-surface="solid"');
});

it('leaves the unpainted variants reading as the page they sit on', function (string $variant) {
    // The border and the glyph carry the tone, so the body does not have to:
    // neither of these publishes a foreground of its own, and each takes
    // whatever it is standing on — which on a page is the ordinary ink.
    // Publishing the page's colours instead of publishing nothing would be a
    // different thing: an outline alert inside a solid card has to follow the
    // card, and inheriting is what does that.
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\">Card declined.</x-shape::alert>"))
        ->not->toContain('data-shape-surface=')
        ->toContain('text-[color:var(--shape-fg)]');
})->with(['outline', 'ghost']);

it('colours an unpainted alert when it is asked to', function (string $variant) {
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\" toned>Card declined.</x-shape::alert>"))
        ->toContain('data-shape-surface="tint"');
})->with(['outline', 'ghost']);

it('publishes the foreground a ghost alert paints itself on hover', function () {
    // The half a `hover:bg-` cannot reach. The tint arrives on hover, and the
    // heading and the body paint their own `--shape-fg` — so a `hover:text-`
    // on the root would leave both of them where they were, and the wash would
    // carry the page's grey for as long as the pointer was on it. The pair is
    // republished for exactly that long instead.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost">Card declined.</x-shape::alert>'))
        ->toContain('hover:bg-[var(--shape-tone-tint)]')
        ->toContain('data-shape-surface-hover="tint"');
});

it('leaves the hover foreground to the alert that paints one', function () {
    // Outline never moves, so it has nothing to republish; a toned ghost is
    // already reading the tint at rest, so its hover has nothing left to say.
    expect(Blade::render('<x-shape::alert tone="danger" variant="outline">Card declined.</x-shape::alert>'))
        ->not->toContain('data-shape-surface-hover');

    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost" toned>Card declined.</x-shape::alert>'))
        ->not->toContain('data-shape-surface-hover');
});

it('refuses to untone a variant that paints a fill, because the fill decides', function (string $variant, string $surface) {
    // The two failures the surface contract exists to make impossible: the
    // global grey on a pink wash, and dark ink on a saturated fill. Neither is
    // a call site's to ask for, so `toned` is ignored wherever there is a
    // background to be readable against.
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\" :toned=\"false\">Card declined.</x-shape::alert>"))
        ->toContain("data-shape-surface=\"{$surface}\"");
})->with([
    ['subtle', 'tint'],
    ['solid', 'solid'],
]);

it('keeps the glyph toned when the text is not, so colour is not lost with it', function () {
    // An untoned alert still has to say what it means at a glance, and a
    // one-pixel border is thin. The icon holds the tone the body gave up.
    expect(Blade::render('<x-shape::alert tone="danger" variant="outline">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-icon')
        ->toContain('text-[color:var(--shape-tone-ink)]');

    // Toned, it reads the surface with everything else rather than painting
    // itself a second time.
    expect(Blade::render('<x-shape::alert tone="danger" variant="outline" toned>Card declined.</x-shape::alert>'))
        ->not->toContain('text-[color:var(--shape-tone-ink)]');
});

it('leaves the foreground to the surface instead of restating it per variant', function (string $variant) {
    // One `text-` class serves all four, which is what keeps the arms above to
    // a background each. If a variant ever paints its own foreground, the
    // nested muted text stops agreeing with it.
    expect(Blade::render("<x-shape::alert variant=\"{$variant}\">Message</x-shape::alert>"))
        ->toContain('text-[color:var(--shape-fg)]');
})->with(['subtle', 'solid', 'outline', 'ghost']);

it('is not a live region, because it was on the page already', function () {
    // The toaster carries the live regions. Announcing markup that was present
    // at load repeats what a screen reader is about to read anyway.
    expect(Blade::render('<x-shape::alert tone="danger">Card declined.</x-shape::alert>'))
        ->not->toContain('aria-live')
        ->not->toContain('role="alert"');
});

it('renders a heading only when it is given one', function () {
    expect(Blade::render('<x-shape::alert heading="Payment failed">Retry it.</x-shape::alert>'))
        ->toContain('data-shape-heading')
        ->toContain('Payment failed');

    expect(Blade::render('<x-shape::alert>Retry it.</x-shape::alert>'))
        ->not->toContain('data-shape-heading');
});

it('is dismissible only when asked, and hands the job to the script', function () {
    expect(Blade::render('<x-shape::alert :dismissible="true">Message</x-shape::alert>'))
        ->toContain('data-shape-dismiss')
        ->toContain('aria-label="Dismiss"');

    expect(Blade::render('<x-shape::alert>Message</x-shape::alert>'))
        ->not->toContain('data-shape-dismiss');
});

it('yields to a class passed at the call site', function () {
    expect(Blade::render('<x-shape::alert class="p-8">Message</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:p-4')
        ->toContain('p-8');
});

it('publishes a surface the dismiss control can be corrected against', function (string $variant, string $toned) {
    // The Blade half of a fix that finishes in CSS. The rule at the foot of
    // shape.css is `[data-shape-surface] [data-shape-dismiss]`, so both halves
    // have to be in the markup or the close button silently goes back to
    // resolving the neutral ink from the `data-shape-tone` a button always
    // declares for itself.
    $html = Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\" {$toned} dismissible>Card declined.</x-shape::alert>");

    expect($html)
        ->toContain('data-shape-surface="'.($variant === 'solid' ? 'solid' : 'tint').'"')
        ->toContain('data-shape-dismiss');
})->with([
    ['subtle', ''],
    ['solid', ''],
    ['outline', 'toned'],
    ['ghost', 'toned'],
]);

it('leaves the dismiss control alone in an alert that publishes no foreground', function () {
    // The correction is a descendant rule on `[data-shape-surface]`, so an
    // untoned alert misses it and the close button goes on resolving its own
    // neutral ink — which is the right answer on the page background, and the
    // same miss the toast relies on. Inside something that does publish a
    // surface, that ancestor still matches and the control follows it.
    $html = Blade::render('<x-shape::alert tone="danger" variant="outline" dismissible>Card declined.</x-shape::alert>');

    expect($html)
        ->not->toContain('data-shape-surface=')
        ->toContain('data-shape-dismiss');
});

it('hands the dismiss control the foreground a ghost alert takes on hover', function () {
    // The same rule, on the attribute that publishes late: `[data-shape-surface-hover]`
    // is in the selector list beside `[data-shape-surface]`, so the × follows
    // the block into the tint rather than staying the one grey thing on it.
    $html = Blade::render('<x-shape::alert tone="danger" variant="ghost" dismissible>Card declined.</x-shape::alert>');

    expect($html)
        ->toContain('data-shape-surface-hover="tint"')
        ->toContain('data-shape-dismiss');
});

it('leaves the dismiss control untoned, because the surface is what it reads', function () {
    // Passing `:tone="$tone"` down would fix the tints and break `solid` —
    // danger-800 ink on a danger-700 fill — and it is the thing the surface
    // contract exists so that no component has to do. The control stays neutral
    // in the markup and is corrected by the surface it is standing on.
    $html = Blade::render('<x-shape::alert tone="danger" dismissible>Card declined.</x-shape::alert>');

    expect($html)->toContain('data-shape-tone="neutral"');
});
