<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('is a dialog, so the focus trap and the top layer are the platform\'s', function () {
    $html = Blade::render('<x-shape::modal name="confirm">Body</x-shape::modal>');

    expect($html)
        ->toContain('<dialog')
        ->toContain('id="confirm"')
        ->toContain('data-shape-modal');
});

it('names itself with its heading rather than repeating it in an aria-label', function () {
    $html = Blade::render('<x-shape::modal name="confirm" heading="Delete project">Body</x-shape::modal>');

    expect($html)
        ->toContain('aria-labelledby="confirm-heading"')
        ->toContain('id="confirm-heading"')
        ->toContain('Delete project')
        // Named by the heading that is already on screen, not by a duplicate of
        // it hidden in an attribute.
        ->not->toContain('aria-label="Delete project"');
});

it('describes itself with its description, and stays quiet without one', function () {
    expect(Blade::render('<x-shape::modal name="c" heading="H" description="Why">Body</x-shape::modal>'))
        ->toContain('aria-describedby="c-description"')
        ->toContain('id="c-description"');

    expect(Blade::render('<x-shape::modal name="c" heading="H">Body</x-shape::modal>'))
        ->not->toContain('aria-describedby');
});

it('closes on a click outside, which a dialog does not do on its own', function () {
    // `closedby="any"` is the platform's light dismiss. Without it a modal
    // `<dialog>` closes on Escape and on an explicit close and nothing else —
    // clicking the backdrop does nothing, because `::backdrop` is painted by the
    // dialog rather than being an element that can be clicked.
    expect(Blade::render('<x-shape::modal name="c">Body</x-shape::modal>'))
        ->toContain('closedby="any"');

    expect(Blade::render('<x-shape::drawer name="c">Body</x-shape::drawer>'))
        ->toContain('closedby="any"');
});

it('declares itself unclosable when it must be answered', function () {
    // `closedby="none"` turns off Escape and the outside click together, which
    // is the whole of `dismissible: false` on a browser that reads it.
    expect(Blade::render('<x-shape::modal name="c" :dismissible="false">Body</x-shape::modal>'))
        ->toContain('closedby="none"');

    expect(Blade::render('<x-shape::drawer name="c" :dismissible="false">Body</x-shape::drawer>'))
        ->toContain('closedby="none"');
});

it('renders a close button only when it can be dismissed', function () {
    expect(Blade::render('<x-shape::modal name="c" heading="H">Body</x-shape::modal>'))
        ->toContain('data-shape-overlay-close')
        ->not->toContain('data-shape-persistent');

    expect(Blade::render('<x-shape::modal name="c" heading="H" :dismissible="false">Body</x-shape::modal>'))
        ->toContain('data-shape-persistent')
        ->not->toContain('data-shape-overlay-close');
});

it('has no backdrop element, because the scrim is a pseudo-element', function () {
    // The draft of this library composed a `modal.backdrop` component. There is
    // nothing for it to do: `::backdrop` is styled in shape.css and arrives with
    // the dialog.
    expect(Blade::render('<x-shape::modal name="c">Body</x-shape::modal>'))
        ->not->toContain('data-shape-backdrop');
});

it('sets no z-index anywhere, because the top layer is above every stacking context', function () {
    expect(Blade::render('<x-shape::modal name="c" heading="H">Body</x-shape::modal>'))
        ->not->toContain('z-');
});

it('sizes itself from a key rather than from a value', function (string $size, string $class) {
    expect(Blade::render("<x-shape::modal name=\"c\" size=\"{$size}\">Body</x-shape::modal>"))
        ->toContain($class)
        ->toContain("data-shape-size=\"{$size}\"");
})->with([
    ['sm', '[:where(&amp;)]:max-w-sm'],
    ['base', '[:where(&amp;)]:max-w-lg'],
    ['lg', '[:where(&amp;)]:max-w-2xl'],
]);

it('yields to a class passed at the call site', function () {
    expect(Blade::render('<x-shape::modal name="c" class="max-w-4xl">Body</x-shape::modal>'))
        ->toContain('[:where(&amp;)]:max-w-lg')
        ->toContain('max-w-4xl');
});

it('opens from a command rather than from a script', function () {
    $html = Blade::render('<x-shape::overlay.trigger for="confirm">Open</x-shape::overlay.trigger>');

    expect($html)
        ->toContain('command="show-modal"')
        ->toContain('commandfor="confirm"')
        ->toContain('data-shape-overlay-trigger')
        ->not->toContain('onclick');
});

it('closes from a command, and not from a nested form', function () {
    // A modal very often contains a form, and `<form method="dialog">` inside
    // one is invalid HTML that browsers resolve by dropping a form.
    $html = Blade::render('<x-shape::overlay.close for="confirm" />');

    expect($html)
        ->toContain('command="close"')
        ->toContain('commandfor="confirm"')
        ->not->toContain('<form');
});

it('closes as an icon button with a label, or as an ordinary button with one', function () {
    expect(Blade::render('<x-shape::overlay.close for="c" />'))
        ->toContain('aria-label="Close"')
        ->toContain('data-shape-icon');

    expect(Blade::render('<x-shape::overlay.close for="c" label="Cancel" />'))
        ->toContain('Cancel')
        ->not->toContain('aria-label="Close"');
});

it('lets an application translate the close label at the call site', function () {
    // Not `__()` inside the component: a folded component resolves it once, at
    // compile time, and serves that one locale to everybody.
    expect(Blade::render('<x-shape::overlay.close for="c" :label="__(\'Avbryt\')" />'))
        ->toContain('Avbryt');
});

it('stacks its actions in a footer that never asks whether it has content', function () {
    expect(Blade::render('<x-shape::overlay.footer><x-shape::button>Go</x-shape::button></x-shape::overlay.footer>'))
        ->toContain('data-shape-overlay-footer')
        ->toContain('justify-end');
});
