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
    // It names its properties rather than taking `transition-colors`, because
    // the cast under `shadow` has to fade in with the fill and `box-shadow` is
    // not a colour.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost">Card declined.</x-shape::alert>'))
        ->toContain('hover:bg-[var(--shape-tone-tint)]')
        ->toContain('transition-[color,background-color,border-color,box-shadow]');
});

it('leaves the other variants still on hover, because only ghost is unpainted', function (string $variant) {
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\">Card declined.</x-shape::alert>"))
        ->not->toContain('hover:bg-')
        ->not->toContain('transition-');
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
    // than drawing a box around nothing. The ghost arm's transition already
    // names `border-color`, so the edge fades in with the background.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost" border>Card declined.</x-shape::alert>'))
        ->toContain('hover:border-[var(--shape-tone-border-strong)]')
        ->toContain('hover:bg-[var(--shape-tone-tint)]')
        ->toContain('transition-[color,background-color,border-color,box-shadow]');
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
        ->not->toContain('transition-');
})->with(['subtle', 'solid', 'outline']);

it('sits flat in the flow of the page until it is asked to lift', function (string $variant) {
    // An alert is part of the content it is about, so there is nothing to lift
    // away from by default. `shadow` is the opt-in for the alert that has to
    // read as laid on the page instead — over a dense table, or beside a card
    // drawn with a resting shadow of its own.
    expect(Blade::render("<x-shape::alert tone=\"info\" variant=\"{$variant}\">Deploy finished.</x-shape::alert>"))
        ->not->toContain('shadow-');
})->with(['subtle', 'outline', 'solid', 'ghost']);

it('lifts the alert on the raised step, the one everything resting on the page takes', function (string $variant) {
    // One step, and it is the same `shadow-sm` the button, the card and the
    // input already reach for. Shape defines no elevation scale of its own, so
    // this is Tailwind's own token and a retheme of it carries the alert with
    // the rest of the interface.
    expect(Blade::render("<x-shape::alert tone=\"info\" variant=\"{$variant}\" shadow>Deploy finished.</x-shape::alert>"))
        ->toContain('[:where(&amp;)]:shadow-sm');
})->with(['subtle', 'outline', 'solid']);

it('applies the lift at zero specificity, so a call site can take another step', function () {
    // Elevation is a convention rather than a token set, and the discipline is
    // in which step a component reaches for — not in stopping a caller who
    // needs a different one.
    expect(Blade::render('<x-shape::alert tone="info" shadow class="shadow-lg">Deploy finished.</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:shadow-sm')
        ->toContain('shadow-lg');
});

it('holds the ghost lift back until the pointer paints it a surface', function () {
    // A shadow is a cast from a surface and the ghost arm has none at rest, so
    // drawn there it would ring a transparent block with an edge nothing in it
    // drew. It arrives with the tint, the same way the border does.
    expect(Blade::render('<x-shape::alert tone="info" variant="ghost" shadow>Deploy finished.</x-shape::alert>'))
        ->toContain('hover:shadow-sm')
        ->toContain('hover:bg-[var(--shape-tone-tint)]')
        ->not->toContain('[:where(&amp;)]:shadow-sm');
});

it('fades the ghost cast in with the fill rather than letting it appear at once', function () {
    // `box-shadow` is not a colour and `transition-colors` does not carry it, so
    // the arm names the properties it changes. A fill easing in over a hundred
    // milliseconds under a shadow that snapped into place is the one way to make
    // a hover look broken.
    expect(Blade::render('<x-shape::alert tone="info" variant="ghost" shadow>Deploy finished.</x-shape::alert>'))
        ->toContain('transition-[color,background-color,border-color,box-shadow]')
        ->toContain('duration-100');
});

it('keeps the other variants still on hover once they are lifted', function (string $variant) {
    // Only ghost waits. A shadow on the other three is drawn at rest and stays
    // where it is, so none of them gains a hover it did not have.
    expect(Blade::render("<x-shape::alert tone=\"info\" variant=\"{$variant}\" shadow>Deploy finished.</x-shape::alert>"))
        ->not->toContain('hover:shadow-')
        ->not->toContain('transition-');
})->with(['subtle', 'solid', 'outline']);

it('carries the lift with the edge and the fill when a ghost alert takes both', function () {
    // The whole block arrives at once under the pointer: the tint it would have
    // had at rest, the tone's edge, and the cast that lifts it off the page.
    expect(Blade::render('<x-shape::alert tone="info" variant="ghost" shadow border>Deploy finished.</x-shape::alert>'))
        ->toContain('hover:shadow-sm')
        ->toContain('hover:border-[var(--shape-tone-border-strong)]')
        ->toContain('hover:bg-[var(--shape-tone-tint)]');
});

it('leaves the lift out of the tone, because elevation is not a colour', function () {
    // Every other paint prop here reads a `--shape-tone-*` variable. This one
    // reads Tailwind's shadow token and nothing else, so a retheme of the tone
    // never moves it and a retheme of the scale always does.
    $danger = Blade::render('<x-shape::alert tone="danger" shadow>Card declined.</x-shape::alert>');
    $success = Blade::render('<x-shape::alert tone="success" shadow>Payment received.</x-shape::alert>');

    expect($danger)->toContain('[:where(&amp;)]:shadow-sm')
        ->and($success)->toContain('[:where(&amp;)]:shadow-sm')
        ->and($danger)->not->toContain('shadow-[var(');
});

it('draws no bar by default, because the variant is already how an alert says itself', function (string $variant) {
    // Four variants say the same thing four ways. The bar is the fifth, for the
    // alert that has to be findable down a long page without being filled — and
    // one more edge on a block that already reads is noise.
    expect(Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\">Card declined.</x-shape::alert>"))
        ->not->toContain('border-l-4')
        ->not->toContain('border-r-4')
        ->not->toContain('border-t-4')
        ->not->toContain('border-b-4');
})->with(['subtle', 'outline', 'solid', 'ghost']);

it('draws the toast\'s edge down whichever side it is given', function (string $side, string $paint) {
    // The same rule a toast carries its whole tone in, with a side to choose.
    // Every side is a different border utility rather than a different value of
    // one, which is why the component spells all four out.
    expect(Blade::render("<x-shape::alert tone=\"danger\" bar=\"{$side}\">Card declined.</x-shape::alert>"))
        ->toContain($paint);
})->with([
    ['left', '[:where(&amp;)]:border-l-4 [:where(&amp;)]:border-l-[var(--shape-tone)]'],
    ['right', '[:where(&amp;)]:border-r-4 [:where(&amp;)]:border-r-[var(--shape-tone)]'],
    ['top', '[:where(&amp;)]:border-t-4 [:where(&amp;)]:border-t-[var(--shape-tone)]'],
    ['bottom', '[:where(&amp;)]:border-b-4 [:where(&amp;)]:border-b-[var(--shape-tone)]'],
]);

it('reads a bare bar as the left one, rather than drawing nothing at all', function () {
    // `bar` with no value is `true`, and the toast's side is the answer that
    // makes forgetting the value harmless. An attribute that silently painted
    // nothing would be the easiest way to use this prop wrong.
    expect(Blade::render('<x-shape::alert tone="danger" bar>Card declined.</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:border-l-4');
});

it('paints the bar in the tone itself rather than the step the border takes', function () {
    // A border bounds the block and lets the fill speak, so it sits a step back
    // down the ramp. A bar is the speaking: four pixels of the pale edge colour
    // would say less than the one pixel it replaced.
    expect(Blade::render('<x-shape::alert tone="danger" bar="left">Card declined.</x-shape::alert>'))
        ->toContain('border-l-[var(--shape-tone)]')
        ->not->toContain('border-l-[var(--shape-tone-border-strong)]');
});

it('takes the step past the fill on solid, where the tone is already the background', function () {
    // The same break the border has on this variant, for the same reason: the
    // fill is `--shape-tone` and a rule painted in it is no rule at all.
    expect(Blade::render('<x-shape::alert tone="danger" variant="solid" bar="left">Card declined.</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:border-l-[var(--shape-tone-hover)]')
        ->toContain('[:where(&amp;)]:bg-[var(--shape-tone)]');
});

it('draws the ghost bar at rest, where the border and the shadow wait', function () {
    // Both of those ring the block, and a ring around something that paints
    // nothing is an edge nothing drew. A rule down one side rings nothing — it
    // is the mark in the margin a blockquote takes, and it reads on the bare
    // page as well as it reads on a fill.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost" bar="left">Card declined.</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:border-l-4 [:where(&amp;)]:border-l-[var(--shape-tone)]');
});

it('holds the ghost bar\'s colour under the pointer, where the border would carry it off', function () {
    // `border` paints `hover:border-` on this variant — the shorthand, which
    // lands after the bar in the cascade and would recolour it with the other
    // three sides. Naming the side again under the pointer is what keeps the
    // bar the one thing on the block that does not change.
    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost" bar="left" border>Card declined.</x-shape::alert>'))
        ->toContain('hover:border-l-[var(--shape-tone)]')
        ->toContain('hover:border-[var(--shape-tone-border-strong)]');
});

it('composes the bar with a border, which is the toast\'s own recipe', function () {
    // A toast is an edge on three sides and a thick tone on the fourth. Asking
    // for both here is how an alert gets there.
    expect(Blade::render('<x-shape::alert tone="danger" bar="left" border>Card declined.</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:border [:where(&amp;)]:border-[var(--shape-tone-border-strong)]')
        ->toContain('[:where(&amp;)]:border-l-4 [:where(&amp;)]:border-l-[var(--shape-tone)]');
});

it('squares only the corners the bar runs between', function (string $side, string $paint) {
    // A radius bends the last few pixels of a four-pixel rule around the block,
    // which reads as a stripe wrapped round a corner rather than a cut down one
    // side. The other two corners stay rounded, so the alert still reads as one
    // of these rather than as a rectangle of tint.
    expect(Blade::render("<x-shape::alert tone=\"warning\" bar=\"{$side}\" bar-square>Check the plan.</x-shape::alert>"))
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->toContain($paint);
})->with([
    ['left', '[:where(&amp;)]:rounded-l-none'],
    ['right', '[:where(&amp;)]:rounded-r-none'],
    ['top', '[:where(&amp;)]:rounded-t-none'],
    ['bottom', '[:where(&amp;)]:rounded-b-none'],
]);

it('leaves the corners alone when there is no bar to straighten', function () {
    // `bar-square` is the bar's own corner and nothing else. Unrounding a block
    // that has no rule to straighten is a decision about the shape of the
    // library, and a call site that wants it says `class="rounded-none"`. The
    // name carries the prop it modifies for the same reason `icon-size` does —
    // and because bare `square` is the button's word for an equal-sided control.
    expect(Blade::render('<x-shape::alert tone="warning" bar-square>Check the plan.</x-shape::alert>'))
        ->toContain('[:where(&amp;)]:rounded-shape')
        ->not->toContain('rounded-l-none')
        ->not->toContain('rounded-r-none')
        ->not->toContain('rounded-t-none')
        ->not->toContain('rounded-b-none');
});

it('draws nothing for a side it does not have', function (mixed $value) {
    // The side is resolved once, before anything paints with it, so a value
    // that is not one of the four words leaves the alert exactly as it was.
    $html = Blade::render('<x-shape::alert tone="danger" :bar="$bar">Card declined.</x-shape::alert>', ['bar' => $value]);

    expect($html)
        ->not->toContain('border-l-4')
        ->not->toContain('border-r-4')
        ->not->toContain('border-t-4')
        ->not->toContain('border-b-4');
})->with([false, null, 'middle']);

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

it('renders an actions row only when the slot is written', function () {
    // The row is guarded rather than rendered-and-hidden, because `actions` is
    // declared in `@props`: the question is the compile-time one `heading`
    // asks — whether the call site passed it — and not the runtime one about
    // what a slot happens to hold.
    $html = Blade::render(<<<'BLADE'
        <x-shape::alert>Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE);

    expect($html)
        ->toContain('data-shape-alert-actions')
        ->toContain('Upgrade');

    expect(Blade::render('<x-shape::alert>Your trial ends on Friday.</x-shape::alert>'))
        ->not->toContain('data-shape-alert-actions');
});

it('keeps the actions out of the paragraph the body is wrapped in', function () {
    // The reason this is a named slot at all. The default slot renders inside
    // an `<x-shape::text>`, so a button written there would be a control in a
    // small muted paragraph; named, the row is the prose's sibling.
    $html = Blade::render(<<<'BLADE'
        <x-shape::alert>Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE);

    preg_match('/<p[^>]*data-shape-text.*?<\/p>/s', $html, $paragraph);

    expect($paragraph[0])->not->toContain('<button');
    expect($html)->toContain('data-shape-alert-actions');
});

it('lets the alert\'s own width place the row, rather than a breakpoint', function () {
    // Nothing else in this library ships a `sm:` or an `md:`: a component
    // cannot see the viewport it landed in, and the same alert in a sidebar
    // and across a page is the same markup at two widths. A container query is
    // the version of that rule a component can keep.
    $html = Blade::render(<<<'BLADE'
        <x-shape::alert>Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE);

    expect($html)
        ->toContain('@container')
        ->toContain('@lg:flex-row')
        ->not->toContain('sm:flex-row')
        ->not->toContain('md:flex-row');
});

it('declares no query container on an alert with no row to move', function () {
    // `container-type: inline-size` takes an element out of intrinsic sizing
    // and brings layout containment with it, so it is declared where it is
    // read and nowhere else — not on every alert in an application.
    expect(Blade::render('<x-shape::alert>Your trial ends on Friday.</x-shape::alert>'))
        ->not->toContain('@container');

    expect(Blade::render(<<<'BLADE'
        <x-shape::alert actions-placement="below">Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE))
        ->not->toContain('@container');
});

it('pins the row where the width is not the thing that should decide', function (string $placement, string $paint, string $absent) {
    // Three buttons should stack whatever the room, and one small control
    // should be able to stay out on the right in a narrow panel.
    $html = Blade::render(<<<BLADE
        <x-shape::alert actions-placement="{$placement}">Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE);

    expect($html)
        ->toContain($paint)
        ->not->toContain($absent);
})->with([
    ['side', 'flex-row items-center justify-between', '@lg:'],
    ['below', 'flex min-w-0 flex-1 flex-col gap-3', '@lg:'],
]);

it('takes the step the row flips at, because what fits beside a message depends on it', function (string $placement, string $step) {
    // One threshold cannot serve a three-word notice with an `Undo` and a
    // heading with a paragraph and three buttons. The prop takes the container
    // size itself for the alerts where the default step is the wrong one.
    $html = Blade::render(<<<BLADE
        <x-shape::alert actions-placement="{$placement}">Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE);

    expect($html)
        ->toContain('@container')
        ->toContain($step.':flex-row')
        ->toContain($step.':items-center')
        ->toContain($step.':justify-between');
})->with([
    ['sm', '@sm'],
    ['md', '@md'],
    ['lg', '@lg'],
    ['xl', '@xl'],
    ['2xl', '@2xl'],
]);

it('falls back to the default step rather than to nothing', function (mixed $value) {
    // There is no `auto`: omitting the prop is how a call site asks for the
    // library's own step, so a value that meant the same thing would be a
    // second spelling of the unset state. Anything unrecognised lands there
    // too, rather than drawing a placement nobody asked for.
    $rendered = $value === null
        ? '<x-shape::alert>Saved.<x-slot:actions><x-shape::button>Undo</x-shape::button></x-slot:actions></x-shape::alert>'
        : "<x-shape::alert actions-placement=\"{$value}\">Saved.<x-slot:actions><x-shape::button>Undo</x-shape::button></x-slot:actions></x-shape::alert>";

    expect(Blade::render($rendered))
        ->toContain('@container')
        ->toContain('@lg:flex-row');
})->with([null, 'lg', 'base', 'auto']);

it('measures the alert and never the viewport, whichever step is named', function (string $placement) {
    // The distinction the prop rests on. `md:` is 768px of viewport; `@md:` is
    // 28rem of the alert. A component cannot see the viewport it landed in, so
    // only one of those is a reading it could honour.
    expect(Blade::render(<<<BLADE
        <x-shape::alert actions-placement="{$placement}">Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE))
        ->not->toMatch('/(?<!@)\b(sm|md|lg|xl):(flex-row|items-center|justify-between)/');
})->with(['sm', 'md', 'lg', 'xl', '2xl']);

it('spells every step out, because Tailwind reads these names as text', function () {
    // `'@'.$step.':flex-row'` would generate no CSS at all, which is the same
    // reason `bar` spells out its four sides rather than composing them.
    $component = (string) file_get_contents(__DIR__.'/../../resources/views/shape/alert/alert.blade.php');

    foreach (['@sm', '@md', '@lg', '@xl', '@2xl'] as $step) {
        expect($component)->toContain($step.':flex-row');
    }
});

it('gives the message the squeeze and the buttons their width', function () {
    // When the two share a line the sentence is the part that gives: `min-w-0`
    // on the message and `shrink-0` on the row, with a wrap underneath for when
    // even that is not enough — the card's footer wraps for the same reason.
    $html = Blade::render(<<<'BLADE'
        <x-shape::alert actions-placement="side">Your trial ends on Friday.<x-slot:actions><x-shape::button>Upgrade</x-shape::button></x-slot:actions></x-shape::alert>
        BLADE);

    expect($html)
        ->toContain('<div class="flex min-w-0 flex-col gap-1">')
        ->toContain('flex shrink-0 flex-wrap items-center gap-3');
});

it('leaves an alert with no actions the column it always had', function () {
    // The row's gap and its container query would both be inert without a row
    // — a gap needs two children, and a container query with no container never
    // matches — but an alert that renders dead utilities is one that has to be
    // explained every time someone reads its output.
    expect(Blade::render('<x-shape::alert>Your trial ends on Friday.</x-shape::alert>'))
        ->toContain('<div class="flex min-w-0 flex-1 flex-col">')
        ->not->toContain('gap-3 @lg:flex-row');
});
