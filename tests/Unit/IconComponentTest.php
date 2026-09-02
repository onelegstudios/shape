<?php

declare(strict_types=1);

use Onelegstudios\Shape\Icons\Component;
use Onelegstudios\Shape\Icons\IconSource;
use Onelegstudios\Shape\IconSet;
use Onelegstudios\Shape\IconSlots;

/**
 * The library's scale, which every set below is measured against.
 *
 * @return array<string, array{class: string, prefer?: string}>
 */
function renderScale(): array
{
    return [
        'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
        'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
        'base' => ['class' => 'size-6', 'prefer' => 'outline'],
    ];
}

/**
 * A source that is a map of paths to drawings, and a revision or none.
 *
 * Which is all this class asks of one: it never lists a directory and never
 * decides what to read, because deciding is the command's job and it has
 * already been done by the time cells arrive here.
 *
 * @param  array<string, string>  $drawings
 */
function drawnFrom(array $drawings, ?string $revision = null): IconSource
{
    return new class($drawings, $revision) implements IconSource
    {
        /**
         * @param  array<string, string>  $drawings
         */
        public function __construct(
            private readonly array $drawings,
            private readonly ?string $revision,
        ) {}

        public function has(string $path): bool
        {
            return array_key_exists($path, $this->drawings);
        }

        public function get(string $path): string
        {
            return $this->drawings[$path];
        }

        /**
         * @return list<string>
         */
        public function names(string $directory): array
        {
            return [];
        }

        public function revision(): ?string
        {
            return $this->revision;
        }
    };
}

/**
 * Heroicons' shape: two styles over three sizes, with three cells empty.
 */
function twoStyleSet(): IconSet
{
    return IconSet::fromArray('hero', [
        'repo' => 'tailwindlabs/heroicons',
        'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
        'styles' => [
            'solid' => [
                'xs' => '16/solid/{name}.svg',
                'sm' => '20/solid/{name}.svg',
                'base' => '24/solid/{name}.svg',
            ],
            'outline' => [
                'base' => '24/outline/{name}.svg',
            ],
        ],
    ], renderScale());
}

/**
 * Lucide's shape: one style, one size, one flat directory.
 */
function oneStyleSet(): IconSet
{
    return IconSet::fromArray('lucide', [
        'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
        'styles' => ['outline' => ['base' => '{name}.svg']],
    ], renderScale());
}

function slots(): IconSlots
{
    return IconSlots::fromArray([
        'shape-checked' => [],
        'shape-loading' => ['class' => 'animate-spin', 'packaged' => true],
    ]);
}

/**
 * The four cells Heroicons fills for one name, over four separate drawings.
 *
 * @return array<string, string>
 */
function fourCells(string $name): array
{
    return [
        'solid:xs' => "16/solid/{$name}.svg",
        'solid:sm' => "20/solid/{$name}.svg",
        'solid:base' => "24/solid/{$name}.svg",
        'outline:base' => "24/outline/{$name}.svg",
    ];
}

it('drops the attributes that describe how a drawing is used', function () {
    // `width`, `height` and `class` are this library's business, and they arrive
    // through the attribute bag rather than from the file the drawing was made
    // in. What describes the drawing itself is kept, in the source's own order.
    $component = new Component(oneStyleSet(), slots(), drawnFrom([
        'bell.svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" '
            .'viewBox="0 0 24 24" fill="none" stroke="currentColor" class="lucide" '
            .'aria-hidden="true" data-slot="icon" focusable="false" role="img">'
            .'<path d="M6 8" /></svg>',
    ]));

    $blade = $component->render('bell', ['outline:base' => 'bell.svg']);

    expect($blade)
        ->not->toContain('width="24"')
        ->not->toContain('height="24"')
        ->not->toContain('class="lucide"')
        ->not->toContain('data-slot')
        ->not->toContain('focusable')
        ->not->toContain('role="img"')
        ->toContain('viewBox="0 0 24 24" fill="none" stroke="currentColor"')
        ->toContain('$attributes->merge([\'aria-hidden\' => \'true\'])->class($classes)');
});

it('writes no switch for a name that resolved to one drawing', function () {
    // A `switch` with a single arm asks the reader to work out that it never
    // branches. The shortcut is over distinct drawings rather than over cells,
    // so a set whose cells all resolve to the same file takes it too.
    $component = new Component(twoStyleSet(), slots(), drawnFrom([
        '24/outline/bell.svg' => '<svg viewBox="0 0 24 24"><path d="M6 8" /></svg>',
    ]));

    $cells = ['solid:base' => '24/outline/bell.svg', 'outline:base' => '24/outline/bell.svg'];

    expect($component->render('bell', $cells))->not->toContain('switch');
});

it('falls through to the drawing the default cell resolved to', function () {
    // The default arm is the one a call site that names neither prop gets, so it
    // has to be the drawing the default size and its preferred style point at —
    // `outline` at `base` here, not whichever cell happened to be read first.
    $component = new Component(twoStyleSet(), slots(), drawnFrom([
        '16/solid/check.svg' => '<svg viewBox="0 0 16 16"><path d="M0 16" /></svg>',
        '20/solid/check.svg' => '<svg viewBox="0 0 20 20"><path d="M0 20" /></svg>',
        '24/solid/check.svg' => '<svg viewBox="0 0 24 24"><path d="M0 24" /></svg>',
        '24/outline/check.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
    ]));

    $blade = $component->render('shape-checked', fourCells('check'));

    expect($blade)
        ->toContain("<?php switch (\$variant.':'.\$size): case ('solid:xs'): ?>")
        ->toContain("<?php break; case ('solid:sm'): ?>")
        ->toContain("<?php break; case ('solid:base'): ?>")
        ->toContain('<?php endswitch; ?>');

    // The fallthrough holds the outline drawing, and no case labels it.
    expect($blade)->toContain("<?php break; default: ?>\n<svg {{ \$attributes")
        ->and(substr_count($blade, 'd="M0 0"'))->toBe(1)
        ->and($blade)->not->toContain("case ('outline:base')");
});

it('declares variant even for a set that draws one style', function () {
    // So that a `variant` passed by a shared call site is ignored rather than
    // falling through the attribute bag and rendering itself on the `<svg>`.
    $blade = (new Component(oneStyleSet(), slots(), drawnFrom([
        'bell.svg' => '<svg viewBox="0 0 24 24"><path d="M6 8" /></svg>',
    ])))->render('bell', ['outline:base' => 'bell.svg']);

    expect($blade)
        ->toContain("'variant' => 'outline',")
        ->toContain("'size' => 'base',")
        // One style is nothing to resolve, so no size chooses one.
        ->not->toContain('$variant ??= match');
});

it('lets a size choose a style when there is a choice to make', function () {
    $blade = (new Component(twoStyleSet(), slots(), drawnFrom([
        '16/solid/check.svg' => '<svg viewBox="0 0 16 16"><path d="M0 16" /></svg>',
        '20/solid/check.svg' => '<svg viewBox="0 0 20 20"><path d="M0 20" /></svg>',
        '24/solid/check.svg' => '<svg viewBox="0 0 24 24"><path d="M0 24" /></svg>',
        '24/outline/check.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>',
    ])))->render('shape-checked', fourCells('check'));

    expect($blade)
        ->toContain("'variant' => null,")
        ->toContain("\$variant ??= match (\$size) {\n    'xs', 'sm' => 'solid',\n    default => 'outline',\n};");
});

it('states the licence, and the commit the drawing was read at', function () {
    // A consumer redistributing generated components inherits the attribution
    // the set's licence asks for, so the notice travels in the file. The commit
    // joins it because a ref is usually a branch, and recording `master` would
    // say nothing about which drawing ended up here.
    $blade = (new Component(twoStyleSet(), slots(), drawnFrom(
        ['24/outline/check.svg' => '<svg viewBox="0 0 24 24"><path d="M0 0" /></svg>'],
        'a7a54a5f8e0b1c2d3e4f5061728394a5b6c7d8e9',
    )))->render('shape-checked', ['outline:base' => '24/outline/check.svg']);

    // Abbreviated, because forty characters says nothing a reader can hold.
    expect($blade)->toContain(
        "{{-- Heroicons (https://heroicons.com), MIT licensed. tailwindlabs/heroicons@a7a54a5f8e0b. Regenerate; don't hand-edit. --}}",
    );
});

it('states nothing about a revision for a set read from a directory', function () {
    // Nobody can say what a folder of SVGs is a revision of, so the header says
    // the licence and stops rather than inventing a provenance for it.
    $blade = (new Component(oneStyleSet(), slots(), drawnFrom([
        'bell.svg' => '<svg viewBox="0 0 24 24"><path d="M6 8" /></svg>',
    ])))->render('bell', ['outline:base' => 'bell.svg']);

    // The whole comment, so that a revision appearing in it would fail here.
    expect($blade)->toContain("{{-- Lucide (https://lucide.dev), ISC licensed. Regenerate; don't hand-edit. --}}");
});

it('bakes in the utilities the slot declares, not the ones the set does', function () {
    // Every set's loader spins, so the spin belongs to `shape-loading` rather
    // than to whichever set drew it — stated once on the slot and baked in here.
    $component = new Component(oneStyleSet(), slots(), drawnFrom([
        'loader-circle.svg' => '<svg viewBox="0 0 24 24"><path d="M6 8" /></svg>',
    ]));

    expect($component->render('shape-loading', ['outline:base' => 'loader-circle.svg']))
        ->toContain("Shape::classes('shrink-0 animate-spin')")
        ->and($component->render('bell', ['outline:base' => 'loader-circle.svg']))
        ->toContain("Shape::classes('shrink-0')");
});

it('normalises an element written across three lines', function () {
    // The same drawing formatted two ways has to produce the same component, or
    // `shape:icon:status` reports a redraw every time a set reformats its files.
    $across = new Component(oneStyleSet(), slots(), drawnFrom([
        'bell.svg' => "<svg viewBox=\"0 0 24 24\">\n  <path\n    d=\"M6 8\"\n    stroke=\"currentColor\"\n  />\n</svg>",
    ]));

    $inline = new Component(oneStyleSet(), slots(), drawnFrom([
        'bell.svg' => '<svg viewBox="0 0 24 24"><path d="M6 8" stroke="currentColor" /></svg>',
    ]));

    expect($across->render('bell', ['outline:base' => 'bell.svg']))
        ->toBe($inline->render('bell', ['outline:base' => 'bell.svg']))
        ->toContain('    <path d="M6 8" stroke="currentColor"/>');
});
