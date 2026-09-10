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
    | `default` is the size a call site gets when it names none, and it is
    | stated rather than inferred: the scale runs `xs` to `xl` like every other
    | scale in the library, so the middle of it is the answer and the end of it
    | is not. A scale that marks none keeps the older rule and defaults to the
    | last — which is what a config published before this key existed means.
    |
    | Above `base` the drawings stop and only the box grows: an icon set draws
    | at 16, 20 and 24, so `lg` and `xl` render the 24px artwork larger. That is
    | the right trade at these sizes — the alternative is no large icon at all —
    | and it is why `prefer` stays with the stroked drawing up there, which is
    | the one that survives being scaled.
    |
    */

    'icon_sizes' => [
        'xs' => ['class' => 'size-4', 'prefer' => 'solid'],
        'sm' => ['class' => 'size-5', 'prefer' => 'solid'],
        'base' => ['class' => 'size-6', 'prefer' => 'outline', 'default' => true],
        'lg' => ['class' => 'size-8', 'prefer' => 'outline'],
        'xl' => ['class' => 'size-10', 'prefer' => 'outline'],
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
    | Not everything this package ships under `icon/` is here. Four drawings —
    | `shape-arrow-right`, `shape-plus`, `shape-trash` and `shape-user` — exist
    | so that the README and the previews render for somebody who has configured
    | nothing. They are not part of the library's vocabulary: nothing resolves
    | them, they are not documented as a catalogue to pick from, and
    | `shape:icon:replace` leaves them alone. They carry the prefix anyway, so
    | that `trash` and `plus` stay free for the icons you generate yourself —
    | reaching for a bare `trash` you never generated is a "component not found"
    | rather than a Heroicon nobody chose.
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
    | Which of the sets below the icon commands read when a run does not say. It
    | is `heroicons` because that is what the icons this package ships were drawn
    | from; an application that has moved the library onto another set answers
    | this question once here rather than in every command it ever types.
    |
    | It belongs here for the reason `namespace` does, one entry down: which set
    | is yours is true of the application, not of a run. `--set` typed on one
    | run and forgotten on the next is how a components directory ends up
    | holding two vendors, and the icons that name a slot say nothing about
    | which drew them. Set this, and `shape:icon:replace` stays a replacement.
    |
    | `--set` is still the override, and reading a supplementary set is exactly
    | the one-off run it is for. A set read that way is written into a
    | subdirectory named after it rather than flat, so a one-off run cannot land
    | on top of the icons the library is wearing; `namespace`, below, is how a
    | set asks for a different one.
    |
    */

    'icon_set' => 'hero',

    /*
    |--------------------------------------------------------------------------
    | Icon Sets
    |--------------------------------------------------------------------------
    |
    | What the icon commands need to know to turn a directory of SVGs into
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
    | to make — Heroicons is not a dependency of this package. With them, a run
    | fetches the set once, caches it under `storage/framework`, and pins the
    | resolved commit into every file it writes. `--from` still wins when given,
    | and stays the way to read a local folder or a set with no upstream at all.
    |
    | `flatten` is for a set whose drawings are filed under something their names
    | do not say. A pattern places a name into a path, which covers every layout
    | that is a fact about the style and the size — but Remix Icon nests by
    | category, so `close-line` is under `System` and nothing about the name says
    | so. A set that declares this is unpacked into one directory by filename,
    | and what the patterns then read is an ordinary flat set. The cost is that a
    | single drawing can no longer be fetched on its own, so such a set always
    | pulls the whole of itself; and its filenames have to be unique across its
    | directories, which the generator checks rather than assumes.
    |
    | `namespace` gives a set a subdirectory of the components path, and with it
    | a namespace of its own: `icon/lucide/bell.blade.php` is
    | `<x-shape::icon.lucide.bell />`, and a flat `bell` from another set is no
    | longer in its way. Most sets need not name one. The set `icon_set` names is
    | written flat, so that a call site has one spelling for an icon whichever
    | set drew it; every other set is a supplementary one, read for what that set
    | has not got, and is written under its own name already — because flat is
    | exactly where it would collide. Name a subdirectory here for a set that
    | should be found under something other than its own name.
    |
    | It belongs here rather than only on the command line because where a set
    | lives is true of the set: a `--namespace` flag is remembered for one run,
    | and the next run without it writes a second copy flat. `--namespace=`,
    | with nothing after it, is still how a run says it is flat out loud.
    |
    | None of this is read at run time. It is spent while a component is being
    | written, and every value it decides is a literal in the generated file —
    | which is what keeps those files foldable.
    |
    */

    'icon_sets' => [

        'hero' => [
            // The package publishes the `optimized/` drawings at its root, byte
            // for byte, which is why there is no `path` here and why the icons
            // this library ships regenerate identically from it.
            'npm' => 'heroicons',
            'version' => 'latest',
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
            // The one set still read from its repository. `lucide-static` ships
            // fonts and a sprite sheet beside the drawings, which makes it the
            // only package here larger than the repository behind it — 6.2MB
            // against 5.0MB — so there is nothing to move it for.
            //
            // No `namespace`, and none needed: while `icon_set` is `hero` this
            // is a supplementary set, so it is written into `icon/lucide/`
            // under its own name and keeps a spelling Heroicons already has —
            // `<x-shape::icon.lucide.bell />`. Naming one here would only move
            // it somewhere else. `shape:icon:replace --set=lucide` is flat
            // regardless, because a replacement writes over Shape's own names.
            'repo' => 'lucide-icons/lucide',
            'ref' => 'main',
            'path' => 'icons',
            'notice' => 'Lucide (https://lucide.dev), ISC licensed.',
            'styles' => [
                'outline' => [
                    'base' => '{name}.svg',
                ],
            ],
            // Every slot, so that `shape:icon:replace --set=lucide` leaves
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
            // The package rather than the repository: 1.2MB against 32MB, for
            // the same drawings. It carries a second copy of the outline set
            // filed by category, which `path` drops on the way in — the flat
            // `icons/` tree beside it is the one the patterns below read.
            'npm' => '@tabler/icons',
            'version' => 'latest',
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
            'npm' => '@phosphor-icons/core',
            'version' => 'latest',
            'path' => 'assets',
            'notice' => 'Phosphor Icons (https://phosphoricons.com), MIT licensed.',
            // Six weights upstream, and the two the scale asks for are `regular`
            // and `fill`. The other four are a weight axis the library has no
            // word for; a set that wanted `thin` everywhere would swap it in
            // here rather than have Shape grow a third axis to hold it.
            //
            // Phosphor puts the weight in the filename as well as the directory,
            // which is why a pattern is a path and not a directory:
            // `shape:icon:all` runs it backwards to read `heart-fill.svg` as the
            // fill drawing of `heart` rather than as an icon called
            // `heart-fill`.
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

        'bootstrap' => [
            'npm' => 'bootstrap-icons',
            'version' => 'latest',
            'path' => 'icons',
            'notice' => 'Bootstrap Icons (https://icons.getbootstrap.com), MIT licensed.',
            // A style by suffix in one flat directory: `check-circle-fill.svg`
            // sits beside `check-circle.svg`, so the pattern says so and
            // `shape:icon:all` reads the pair as one icon in two styles.
            //
            // Half the set — 702 of 1,374 names — has a fill. Most of what does
            // not is line art with no interior to fill (every arrow, chevron and
            // sort glyph) or a brand logo that is already solid, where the
            // fallback to the outline drawing at every size is not a compromise
            // but the same picture. The rest genuinely has no fill drawn, and
            // renders its outline small, which is the sparse matrix again.
            //
            // `person-lines-fill` is the one fill with no plain drawing behind
            // it, so `shape:icon:all` writes it under `person-lines`, a name
            // Bootstrap has not got. It is still reachable by its own spelling,
            // because `{name}.svg` matches a fill name too:
            // `shape:icon person-lines-fill` generates it.
            'styles' => [
                'outline' => [
                    'base' => '{name}.svg',
                ],
                'solid' => [
                    'base' => [
                        '{name}-fill.svg',
                        // Bootstrap hangs a badge off a glyph — `person-x`,
                        // `shield-check`, `building-down` — and fills the glyph
                        // rather than the badge, so on thirty-five names the
                        // marker lands before the last segment instead of after
                        // it. Without this they arrive as icons named
                        // `person-fill-x`, whose outline cell holds a filled
                        // drawing: the fill leaking into the outline style under
                        // a name that says so.
                        //
                        // Second, because three names have both files and the
                        // suffix is the right one of the two: `person-check-fill`
                        // is filled through, where `person-fill-check` is a
                        // filled person wearing an outline tick.
                        '{head}-fill-{tail}.svg',
                    ],
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
                // so `shape:icon:replace` onto Bootstrap keeps Shape's own
                // spinner and says so.
                'shape-loading' => null,
            ],
        ],

        'remix' => [
            'npm' => 'remixicon',
            'version' => 'latest',
            'path' => 'icons',
            // Filed by category — `close-line.svg` is under `System` and
            // `user-line.svg` under `User & Faces` — which is a layout no
            // pattern can place a name into, because the name does not say
            // which of the twenty folders it is in. Flattened on the way in, so
            // what the patterns below read is an ordinary flat set. The 3,229
            // drawings have no filename in common across those folders, which is
            // what makes that safe; the generator checks rather than assumes it.
            'flatten' => true,
            // Not Apache 2.0, as it was until January 2026. The current licence
            // asks for no attribution at all — Shape states it anyway, the way
            // it states every other set's — and restricts selling the drawings
            // on as an icon pack, which a component in an application is not.
            'notice' => 'Remix Icon (https://remixicon.com), Remix Icon License v1.0.',
            // The most symmetrical set here: 1,539 names drawn both ways, with
            // nothing drawn one way only, so the sparse matrix never fires.
            //
            // The bare spelling is the second candidate because 151 drawings —
            // every one of them an editor glyph, `bold` and `italic` and the
            // alignments — carry no marker at all, having no interior to fill.
            // Without it they are unreachable: `shape:icon bold` would look for
            // `bold-line.svg` and find nothing. It costs the suffixed names
            // nothing, since the shortest reading of a listed file wins and
            // `{name}-line.svg` reads `check-line.svg` as `check` where the bare
            // pattern reads it as `check-line`.
            'styles' => [
                'outline' => [
                    'base' => [
                        '{name}-line.svg',
                        // `ai-generate-2` is the one name drawn both ways round:
                        // this reaches the bare file for the other 150, and for
                        // that one the `-line` drawing above answers first.
                        '{name}.svg',
                    ],
                ],
                'solid' => [
                    'base' => '{name}-fill.svg',
                ],
            ],
            'slots' => [
                'shape-checked' => 'check',
                'shape-indeterminate' => 'subtract',
                // Remix draws no chevron either. The small arrows — `-s`, for
                // the size rather than for a direction — are the glyph the other
                // sets spell `chevron`.
                'shape-prev' => 'arrow-left-s',
                'shape-next' => 'arrow-right-s',
                'shape-expand' => 'arrow-down-s',
                'shape-close' => 'close',
                'shape-success' => 'checkbox-circle',
                'shape-danger' => 'close-circle',
                // The triangle. `error-warning` is Remix's circled exclamation,
                // which is the danger glyph's shape wearing the warning's
                // meaning — two tones would then differ only in colour.
                'shape-warning' => 'alert',
                'shape-info' => 'information',
                // Remix draws no trend line, so the trio is three arrows at
                // three angles rather than two arrows and a dash: read together
                // in a row of stats, they are one family.
                'shape-trend-up' => 'arrow-right-up',
                'shape-trend-down' => 'arrow-right-down',
                'shape-trend-flat' => 'arrow-right',
                // Ten loaders here, and this is the arc: `loader` and `loader-3`
                // are spoked dials, which strobe rather than turn when the slot
                // spins them.
                'shape-loading' => 'loader-4',
            ],
        ],

        'material' => [
            // Read from the published package rather than from Google's own
            // repository, which files every symbol as a directory of 168
            // variants — four optical sizes against fill, three grades and seven
            // weights — three times over for outlined, rounded and sharp. The
            // name is a directory there, which no pattern can place a name into,
            // and the whole of it is five gigabytes.
            //
            // The package is the same Apache 2.0 drawings with none of that:
            // 1.8MB against 2.8GB for the mirror repository it is built from,
            // because a published package holds the drawings and not the project
            // that produces them. It carries its own LICENSE, so the notice
            // below is still the upstream's own words.
            'npm' => '@material-symbols/svg-400',
            // A dist-tag, resolved to the release behind it and pinned as that:
            // a generated component records `0.47.0`, not the word `latest`.
            // Name an exact version here to hold a set still.
            'version' => 'latest',
            // Weight 400, outlined. `rounded` and `sharp` sit beside it inside
            // the same package, and the other weights are packages of their own
            // — `@material-symbols/svg-200` and so on — so a library that wants
            // a lighter or rounder Material changes these two lines.
            'path' => 'outlined',
            'notice' => 'Material Symbols (https://fonts.google.com/icons), Apache 2.0 licensed.',
            // 3,903 names, each drawn both ways, so the sparse matrix never
            // fires. Material spells them with underscores, which the generated
            // components keep: `<x-shape::icon.check_circle />`.
            'styles' => [
                'outline' => [
                    'base' => '{name}.svg',
                ],
                'solid' => [
                    'base' => '{name}-fill.svg',
                ],
            ],
            'slots' => [
                'shape-checked' => 'check',
                'shape-indeterminate' => 'remove',
                // All three from one family. Material draws no `chevron_down`
                // to go with `chevron_left` and `chevron_right`, and the glyph
                // that answers for it is spelled `keyboard_arrow_down` — so the
                // trio is taken from there rather than matching two of the three
                // and hoping the third sits at the same weight.
                'shape-prev' => 'keyboard_arrow_left',
                'shape-next' => 'keyboard_arrow_right',
                'shape-expand' => 'keyboard_arrow_down',
                'shape-close' => 'close',
                'shape-success' => 'check_circle',
                // The crossed circle. `error` is Material's circled exclamation,
                // which is the warning glyph's meaning in the danger glyph's
                // shape.
                'shape-danger' => 'cancel',
                'shape-warning' => 'warning',
                'shape-info' => 'info',
                'shape-trend-up' => 'trending_up',
                'shape-trend-down' => 'trending_down',
                'shape-trend-flat' => 'trending_flat',
                // Material's own spinner, and the one its guidelines reach for:
                // an arc, which is what reads as motion when the slot spins it.
                'shape-loading' => 'progress_activity',
            ],
        ],

    ],

];
