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
    | Icon Sets
    |--------------------------------------------------------------------------
    |
    | What `shape:icon` needs to know to turn a directory of SVGs into
    | components. Every set is a matrix of styles against sizes, and most of
    | them are sparse: Heroicons draws no outline at 16px or 20px, because a
    | 1.5px stroke does not read at that size. A cell with no drawing of its own
    | borrows the largest one its style has and is scaled down to fit.
    |
    | `prefer` is what a size reaches for when the call site names no style. It
    | is why `<x-shape::icon.check size="sm" />` is a crisp 20px solid drawing
    | rather than a 24px outline squeezed into 20px.
    |
    | None of this is read at run time. It is spent while `shape:icon` writes a
    | component, and every value it decides is a literal in the generated file —
    | which is what keeps those files foldable.
    |
    */

    'icon_sets' => [

        'heroicons' => [
            'notice' => 'Heroicons (https://heroicons.com), MIT licensed.',
            'sizes' => [
                'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
                'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
                'base' => ['class' => 'size-6', 'prefer' => 'outline'],
            ],
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
            'sizes' => [
                'xs' => ['class' => 'size-4'],
                'sm' => ['class' => 'size-5'],
                'base' => ['class' => 'size-6'],
            ],
            'styles' => [
                'outline' => [
                    'base' => '{name}.svg',
                ],
            ],
        ],

    ],

];
