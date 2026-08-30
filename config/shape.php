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
    | None of this is read at run time. It is spent while `shape:icon` writes a
    | component, and every value it decides is a literal in the generated file —
    | which is what keeps those files foldable.
    |
    */

    'icon_sets' => [

        'heroicons' => [
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
            'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
            'styles' => [
                'outline' => [
                    'base' => '{name}.svg',
                ],
            ],
        ],

    ],

];
