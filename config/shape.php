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
    | is why `<x-shape::icon.shape-checked size="sm" />` is a crisp 20px solid
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
    | Icon Slots
    |--------------------------------------------------------------------------
    |
    | The icons Shape resolves on your behalf. A slot is a role — the checkbox's
    | tick, the pager's chevrons, the glyph a `warning` tone reaches for — and it
    | is named for that role rather than for whatever the set that drew it calls
    | it. `shape-warning` is a slot Shape fills; `triangle-alert` is an icon you
    | place. They may render the same paths and they are free to diverge, so
    | redrawing your own `check` cannot silently change what a checkbox draws.
    |
    | Declared here rather than scanned out of the markup, for the reason the
    | size scale is: a slot is a fact about the library. Every set answers the
    | same list, and a set that cannot answer one is a coverage error rather than
    | a file that quietly never appears. It also lets a slot exist that no
    | component draws — `shape-loading` is one, and no scan would ever find it.
    |
    | `class` joins the utilities every generated icon carries. The spin belongs
    | to the slot and not to the set, because every set's loader spins; stating
    | it here once is what keeps it true of whichever drawing fills the slot.
    |
    | `packaged` says Shape ships a drawing for this slot and no set has to
    | supply one. `shape-loading` is the only one: Heroicons' `arrow-path` is a
    | circular arrow rather than a loader, so generating from it would be worse
    | than the spinner this package already draws. A set that names a drawing
    | shadows that spinner; a set that says nothing, or says `null`, falls back
    | to it — which for a packaged slot is the designed outcome rather than a
    | hole.
    |
    | Not everything this package ships under `icon/` is here. Three drawings —
    | `shape-arrow-right`, `shape-plus` and `shape-trash` — exist so that the
    | README and the previews render for somebody who has configured nothing.
    | They are not part of the library's vocabulary: nothing resolves them, they
    | are not documented as a catalogue to pick from, and `--replace` leaves them
    | alone. They carry the prefix anyway, so that `trash` and `plus` stay free
    | for the icons you generate yourself — reaching for a bare `trash` you never
    | generated is a "component not found" rather than a Heroicon nobody chose.
    |
    */

    'icon_slots' => [
        'shape-checked' => [],
        'shape-indeterminate' => [],
        'shape-prev' => [],
        'shape-next' => [],
        'shape-expand' => [],
        'shape-close' => [],
        'shape-success' => [],
        'shape-danger' => [],
        'shape-warning' => [],
        'shape-info' => [],
        'shape-trend-up' => [],
        'shape-trend-down' => [],
        'shape-trend-flat' => [],
        'shape-loading' => ['class' => 'animate-spin', 'packaged' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Icon Set
    |--------------------------------------------------------------------------
    |
    | Which of the sets below `shape:icon` reads when a run does not say. It is
    | `heroicons` because that is what the icons this package ships were drawn
    | from; an application that has moved the library onto another set answers
    | this question once here rather than in every command it ever types.
    |
    | It belongs here for the reason `namespace` does, one entry down: which set
    | is yours is true of the application, not of a run. `--set` typed on one
    | run and forgotten on the next is how a components directory ends up
    | holding two vendors, and the icons that name a slot say nothing about
    | which drew them. Set this, and `shape:icon --replace` stays a replacement.
    |
    | `--set` is still the override, and reading a supplementary set is exactly
    | the one-off run it is for.
    |
    */

    'icon_set' => 'heroicons',

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
    | `slots` is the other half of that. A set decides the layout of its files
    | and it also decides their names, and only the first of those generalised:
    | Lucide draws `x` where Heroicons draws `x-mark`. So Shape asks every set
    | the same question instead of either set's — which of your drawings fills
    | `shape-close`? — and writes the answer to `shape-close.blade.php`. The
    | filename says the role, the header says the vendor, and neither has to
    | impersonate the other. A slot a set has nothing for is `null`, which is a
    | set saying so rather than an entry somebody forgot.
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
            // Heroicons answers the same question every other set answers.
            // Losing its privileged status is the point: the library used to
            // ask for its spellings by name, and now it asks for roles.
            'slots' => [
                'shape-checked' => 'check',
                'shape-indeterminate' => 'minus',
                'shape-prev' => 'chevron-left',
                'shape-next' => 'chevron-right',
                'shape-expand' => 'chevron-down',
                'shape-close' => 'x-mark',
                'shape-success' => 'check-circle',
                'shape-danger' => 'x-circle',
                'shape-warning' => 'exclamation-triangle',
                'shape-info' => 'information-circle',
                'shape-trend-up' => 'arrow-trending-up',
                'shape-trend-down' => 'arrow-trending-down',
                'shape-trend-flat' => 'minus',
                // No `shape-loading`: `arrow-path` is a circular arrow rather
                // than a loader, and it does not read as one spinning. The slot
                // is packaged, so falling back to Shape's own spinner is the
                // answer here rather than a gap.
                'shape-loading' => null,
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
            // Every slot, so that `shape:icon --replace --set=lucide` leaves
            // nothing behind. The names on the right are the current release's
            // own files; several were renamed around v0.4xx and the old
            // spellings survive as metadata aliases rather than as SVGs, so
            // `circle-check` is the file and `check-circle` is not.
            'slots' => [
                'shape-checked' => 'check',
                'shape-indeterminate' => 'minus',
                'shape-prev' => 'chevron-left',
                'shape-next' => 'chevron-right',
                'shape-expand' => 'chevron-down',
                'shape-close' => 'x',
                'shape-success' => 'circle-check',
                'shape-danger' => 'circle-x',
                'shape-warning' => 'triangle-alert',
                'shape-info' => 'info',
                'shape-trend-up' => 'trending-up',
                'shape-trend-down' => 'trending-down',
                'shape-trend-flat' => 'minus',
                // A dashed ring, which is what a loader wants to be. Worth
                // looking at spinning before keeping it: a set whose loader is
                // an hourglass or an arrow reads wrong in motion, and saying
                // `null` there is better than shadowing Shape's own spinner.
                'shape-loading' => 'loader-circle',
            ],
        ],

        'tabler' => [
            'repo' => 'tabler/tabler-icons',
            'ref' => 'main',
            'path' => 'icons',
            'notice' => 'Tabler Icons (https://tabler.io/icons), MIT licensed.',
            'styles' => [
                'outline' => [
                    'base' => 'outline/{name}.svg',
                ],
                // Upstream calls this directory `filled`. The style is named for
                // what the scale asks for rather than for what the directory is
                // called, because `prefer => 'solid'` is answered by a style
                // spelled `solid` and by nothing else. It draws a fifth of what
                // `outline` draws — no `minus`, and of the chevrons only two of
                // the four — and a name it has nothing for falls back to the
                // outline drawing at every size, which is the sparse matrix
                // doing its job rather than a gap. The two are close enough that
                // it does not show: Tabler's filled chevron is the outline one
                // with its 2px stroke converted to a path, and only the glyphs
                // that can be solid, like `circle-check`, are drawn solid.
                'solid' => [
                    'base' => 'filled/{name}.svg',
                ],
            ],
            'slots' => [
                'shape-checked' => 'check',
                'shape-indeterminate' => 'minus',
                'shape-prev' => 'chevron-left',
                'shape-next' => 'chevron-right',
                'shape-expand' => 'chevron-down',
                'shape-close' => 'x',
                'shape-success' => 'circle-check',
                'shape-danger' => 'circle-x',
                'shape-warning' => 'alert-triangle',
                'shape-info' => 'info-circle',
                'shape-trend-up' => 'trending-up',
                'shape-trend-down' => 'trending-down',
                'shape-trend-flat' => 'minus',
                // Of the five drawings Tabler calls a loader, this is the one
                // that is an arc rather than a dial or a dashed disc, so it is
                // the one that reads as motion when the slot spins it.
                'shape-loading' => 'loader-2',
            ],
        ],

        'phosphor' => [
            'repo' => 'phosphor-icons/core',
            'ref' => 'main',
            'path' => 'assets',
            'notice' => 'Phosphor Icons (https://phosphoricons.com), MIT licensed.',
            // Six weights upstream, and the two the scale asks for are `regular`
            // and `fill`. The other four are a weight axis the library has no
            // word for; a set that wanted `thin` everywhere would swap it in
            // here rather than have Shape grow a third axis to hold it.
            //
            // Phosphor puts the weight in the filename as well as the directory,
            // which is why a pattern is a path and not a directory: `--all` runs
            // it backwards to read `heart-fill.svg` as the fill drawing of
            // `heart` rather than as an icon called `heart-fill`.
            'styles' => [
                'outline' => [
                    'base' => 'regular/{name}.svg',
                ],
                'solid' => [
                    'base' => 'fill/{name}-fill.svg',
                ],
            ],
            'slots' => [
                'shape-checked' => 'check',
                'shape-indeterminate' => 'minus',
                // Phosphor draws no `chevron`; the glyph other sets spell that
                // way is a `caret` here, and it is the same drawing.
                'shape-prev' => 'caret-left',
                'shape-next' => 'caret-right',
                'shape-expand' => 'caret-down',
                'shape-close' => 'x',
                'shape-success' => 'check-circle',
                'shape-danger' => 'x-circle',
                'shape-warning' => 'warning',
                'shape-info' => 'info',
                'shape-trend-up' => 'trend-up',
                'shape-trend-down' => 'trend-down',
                'shape-trend-flat' => 'minus',
                'shape-loading' => 'spinner-gap',
            ],
        ],

        'bootstrap-icons' => [
            'repo' => 'twbs/icons',
            'ref' => 'main',
            'path' => 'icons',
            'notice' => 'Bootstrap Icons (https://icons.getbootstrap.com), MIT licensed.',
            // A style by suffix in one flat directory: `check-circle-fill.svg`
            // sits beside `check-circle.svg`, so the pattern says so and
            // `--all` reads the pair as one icon in two styles.
            //
            // Half the set — 701 of 1,408 names — has a fill. Most of what does
            // not is line art with no interior to fill (every arrow, chevron and
            // sort glyph) or a brand logo that is already solid, where the
            // fallback to the outline drawing at every size is not a compromise
            // but the same picture. The rest genuinely has no fill drawn, and
            // renders its outline small, which is the sparse matrix again.
            //
            // Two things this spelling cannot say. Bootstrap fills a compound
            // name in the middle — `building-fill-down` is the fill of
            // `building-down` — and those thirty-five arrive as names of their
            // own instead of as cells. And `person-lines-fill` is the one fill
            // with no plain drawing behind it, so `--all` writes it under
            // `person-lines`, a name Bootstrap has not got. Both are reachable
            // by their own spelling: `{name}.svg` matches a fill name too, so
            // `shape:icon check-circle-fill` still generates it.
            'styles' => [
                'outline' => [
                    'base' => '{name}.svg',
                ],
                'solid' => [
                    'base' => '{name}-fill.svg',
                ],
            ],
            'slots' => [
                // Bootstrap draws the bare glyphs twice, inset and full-bleed.
                // The inset ones sit in about two thirds of the box, which looks
                // shy beside every other set at the same size, so the tick, the
                // dash and the dismiss glyph are all the `-lg` drawing.
                'shape-checked' => 'check-lg',
                'shape-indeterminate' => 'dash-lg',
                'shape-prev' => 'chevron-left',
                'shape-next' => 'chevron-right',
                'shape-expand' => 'chevron-down',
                'shape-close' => 'x-lg',
                'shape-success' => 'check-circle',
                'shape-danger' => 'x-circle',
                'shape-warning' => 'exclamation-triangle',
                'shape-info' => 'info-circle',
                'shape-trend-up' => 'graph-up-arrow',
                'shape-trend-down' => 'graph-down-arrow',
                'shape-trend-flat' => 'dash-lg',
                // Bootstrap's spinners are CSS rather than drawings, so there is
                // no ring to spin here. This is the `null` case for real: the
                // set has been asked and has nothing, and the slot is packaged,
                // so a `--replace` onto Bootstrap keeps Shape's own spinner and
                // says so.
                'shape-loading' => null,
            ],
        ],

    ],

];
