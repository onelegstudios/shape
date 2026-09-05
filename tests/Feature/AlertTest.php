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

it('moves the foreground contract with the variant, not just the background', function () {
    // The failure this prevents is the one the tint surface was built for, a
    // step louder: a solid alert fills with the tone, so the readable
    // foreground is the tone's own `-fg` rather than its ink. Publishing `tint`
    // here would put dark ink on a saturated fill.
    expect(Blade::render('<x-shape::alert tone="danger" variant="solid">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-surface="solid"');

    // Outline and ghost paint no background, so the ink contract still applies.
    expect(Blade::render('<x-shape::alert tone="danger" variant="outline">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-surface="tint"');

    expect(Blade::render('<x-shape::alert tone="danger" variant="ghost">Card declined.</x-shape::alert>'))
        ->toContain('data-shape-surface="tint"');
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

it('publishes a surface the dismiss control can be corrected against', function (string $variant) {
    // The Blade half of a fix that finishes in CSS. The rule at the foot of
    // shape.css is `[data-shape-surface] [data-shape-dismiss]`, so both halves
    // have to be in the markup or the close button silently goes back to
    // resolving the neutral ink from the `data-shape-tone` a button always
    // declares for itself.
    $html = Blade::render("<x-shape::alert tone=\"danger\" variant=\"{$variant}\" dismissible>Card declined.</x-shape::alert>");

    expect($html)
        ->toContain('data-shape-surface="'.($variant === 'solid' ? 'solid' : 'tint').'"')
        ->toContain('data-shape-dismiss');
})->with(['subtle', 'outline', 'ghost', 'solid']);

it('leaves the dismiss control untoned, because the surface is what it reads', function () {
    // Passing `:tone="$tone"` down would fix the tints and break `solid` —
    // danger-800 ink on a danger-700 fill — and it is the thing the surface
    // contract exists so that no component has to do. The control stays neutral
    // in the markup and is corrected by the surface it is standing on.
    $html = Blade::render('<x-shape::alert tone="danger" dismissible>Card declined.</x-shape::alert>');

    expect($html)->toContain('data-shape-tone="neutral"');
});
