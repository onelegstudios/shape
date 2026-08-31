<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Ejected Components
    |--------------------------------------------------------------------------
    |
    | Where ejected components live. Shape resolves components from this path
    | before falling back to the ones it ships, so a component published or
    | hand-written here replaces the packaged one without any further wiring.
    |
    */

    'components_path' => resource_path('views/shape'),

    /*
    |--------------------------------------------------------------------------
    | Icon Sizes
    |--------------------------------------------------------------------------
    |
    | The size scale every icon is drawn at, smallest first; the last is the
    | default. It belongs to the library rather than to any one set, so that
    | `size="sm"` means one thing at every call site no matter which set the
    | drawing came from — mixing a supplementary set into the library is the
    | normal case, and two scales in play is the bug that invites.
    |
    | `prefer` is what a size reaches for when the call site names no style. It
    | is why `<x-shape::icon.check size="sm" />` is a crisp 20px solid drawing
    | rather than a 24px outline squeezed into 20px. A set that has no such
    | style ignores it and answers with the one it does have.
    |
    */

    'icon_sizes' => [
        'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
        'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
        'base' => ['class' => 'size-6', 'prefer' => 'outline'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Icon Sets
    |--------------------------------------------------------------------------
    |
    | What `shape:icon` needs to know to turn a directory of SVGs into
    | components. A set says which cells of the matrix above it draws, and most
    | of them are sparse: Heroicons draws no outline at 16px or 20px, because a
    | 1.5px stroke does not read at that size. A cell with no drawing of its own
    | borrows the largest one its style has and is scaled down to fit.
    |
    | `aliases` is the other half of that. A set decides the layout of its files
    | and it also decides their names, and only the first of those generalised:
    | Lucide draws `x` where Heroicons draws `x-mark`. Shape's own components ask
    | for Heroicons' spellings, so without a translation another set can add
    | icons to the library but never replace the ones it draws itself. An alias
    | moves the source file and nothing else — `x-mark` is read from `x.svg` and
    | still written to `x-mark.blade.php` — so the call sites stay as they are
    | and the drawing behind them changes. One direction only.
    |
    | `repo`, `ref` and `path` are where the drawings can be had. Without them a
    | set can only be generated from a directory somebody already has, which
    | made "Regenerate; don't hand-edit" ask for a checkout nobody had been told
    | to make — Heroicons is not a dependency of this package. With them,
    | `shape:icon` fetches the set once, caches it under `storage/framework`, and
    | pins the resolved commit into every file it writes. `--from` still wins
    | when given, and stays the way to read a local folder or a set with no
    | upstream at all.
    |
    | `namespace` gives a set a subdirectory of the components path, and with it
    | a namespace of its own: `icon/lucide/bell.blade.php` is
    | `<x-shape::icon.lucide.bell />`, and a flat `bell` from another set is no
    | longer in its way. Leave it unset for the primary set — flat is the
    | default so that a call site has one spelling for an icon whichever set
    | drew it — and set it on a supplementary set that would otherwise collide.
    | It belongs here rather than only on the command line because where a set
    | lives is true of the set: a `--namespace` flag is remembered for one run,
    | and the next run without it writes a second copy flat.
    |
    | None of this is read at run time. It is spent while `shape:icon` writes a
    | component, and every value it decides is a literal in the generated file —
    | which is what keeps those files foldable.
    |
    */

    'icon_sets' => [

        'heroicons' => [
            'repo' => 'tailwindlabs/heroicons',
            'ref' => 'master',
            'path' => 'optimized',
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
        ],

        'lucide' => [
            // Uncomment to write this set into `icon/lucide/` instead of flat,
            // so it can keep a name Heroicons already spells:
            // `<x-shape::icon.lucide.bell />`. It is off here because this is
            // the worked example of a *replacement* set, and `--replace`
            // writes over Shape's own names, which are flat by definition.
            // 'namespace' => 'lucide',
            'repo' => 'lucide-icons/lucide',
            'ref' => 'main',
            'path' => 'icons',
            'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
            'styles' => [
                'outline' => [
                    'base' => '{name}.svg',
                ],
            ],
            // Enough to cover every icon Shape draws itself, so that
            // `shape:icon --replace --set=lucide` leaves nothing behind. The
            // names on the right are the current release's own files; several
            // were renamed around v0.4xx and the old spellings survive as
            // metadata aliases rather than as SVGs, so `circle-check` is the
            // file and `check-circle` is not. `check`, `minus` and the three
            // chevrons carry over unchanged and need no entry.
            'aliases' => [
                'x-mark' => 'x',
                'check-circle' => 'circle-check',
                'x-circle' => 'circle-x',
                'exclamation-triangle' => 'triangle-alert',
                'information-circle' => 'info',
                'arrow-trending-up' => 'trending-up',
                'arrow-trending-down' => 'trending-down',
            ],
        ],

    ],

];
