<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-shape-50 dark:bg-shape-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shape — the component gallery</title>

    {{-- Before the first paint, and deliberately not a module: a deferred script
         would let the page render light and then flip, which is worse than no
         switch at all. Absent or unrecognised means "follow this machine", which
         is what Shape does on its own. --}}
    <script>
        const scheme = localStorage.getItem('shape-scheme')

        if (scheme === 'dark' || scheme === 'light') {
            document.documentElement.dataset.theme = scheme
        }
    </script>

    {{-- Inlined so the preview needs no asset pipeline. Rebuild with `npm run preview`. --}}
    <style>{!! file_get_contents(\Orchestra\Testbench\package_path($stylesheet)) !!}</style>
</head>
<body class="min-h-dvh text-shape-900 antialiased dark:text-shape-100">

    <a href="#foundations" class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-shape focus:bg-shape-900 focus:px-4 focus:py-2 focus:text-sm focus:text-shape-50 dark:focus:bg-shape-50 dark:focus:text-shape-900">
        Skip to the components
    </a>

    {{-- The bar. `backdrop-blur` makes this a containing block for absolutely
         positioned descendants, which is exactly why no overlay is one: a modal,
         a drawer, a dropdown and a tooltip are all in the top layer, above every
         containing block on the page, so this cannot trap them and the z-index
         here cannot out-number them either. --}}
    <header class="sticky top-0 z-20 border-b border-shape-200/80 bg-shape-50/85 backdrop-blur-md dark:border-shape-800/80 dark:bg-shape-950/85">
        <div class="mx-auto flex max-w-6xl items-center gap-3 px-6 py-3">
            <a href="/" class="flex items-center gap-2.5 pr-2">
                <span aria-hidden="true" class="size-6 rounded-[0.4rem] bg-shape-900 dark:bg-shape-50"></span>
                <span class="text-base font-semibold tracking-tight">Shape</span>
            </a>

            <nav aria-label="Sections" class="hidden items-center gap-0.5 lg:flex">
                @foreach ([
                    'foundations' => 'Foundations',
                    'actions' => 'Actions',
                    'surfaces' => 'Surfaces',
                    'forms' => 'Forms',
                    'overlays' => 'Overlays',
                    'feedback' => 'Feedback',
                    'data' => 'Data',
                ] as $anchor => $label)
                    <a href="#{{ $anchor }}" class="rounded-shape px-2.5 py-1.5 text-sm text-shape-600 hover:bg-shape-100 hover:text-shape-900 dark:text-shape-400 dark:hover:bg-shape-900 dark:hover:text-shape-100">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-3">
                <a href="{{ $seeded ? '/' : '/seed' }}" class="hidden text-sm font-medium text-shape-600 underline-offset-4 hover:underline sm:block dark:text-shape-400">
                    {{ $seeded ? 'Default palette' : 'Seed palette' }}
                </a>
                <a href="/docs" class="hidden text-sm font-medium text-shape-600 underline-offset-4 hover:underline sm:block dark:text-shape-400">
                    Documentation
                </a>

                {{-- Three states rather than two, because Shape's own default is
                     the third one: a switch that only offers light and dark takes
                     "whatever this machine says" away from a reader who had it.
                     Native radios, so arrow keys move between them and the choice
                     is announced as one. --}}
                <fieldset id="shape-scheme" class="flex items-center gap-0.5 rounded-full border border-shape-200 bg-white p-0.5 dark:border-shape-800 dark:bg-shape-900">
                    <legend class="sr-only">Colour scheme</legend>

                    <label class="cursor-pointer">
                        <input type="radio" name="shape-scheme" value="system" class="peer sr-only" checked>
                        <span class="sr-only">Follow the system</span>
                        <span class="flex size-7 items-center justify-center rounded-full text-shape-500 peer-checked:bg-shape-100 peer-checked:text-shape-900 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[color:var(--shape-ring)] dark:peer-checked:bg-shape-800 dark:peer-checked:text-shape-50">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>
                            </svg>
                        </span>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="shape-scheme" value="light" class="peer sr-only">
                        <span class="sr-only">Light</span>
                        <span class="flex size-7 items-center justify-center rounded-full text-shape-500 peer-checked:bg-shape-100 peer-checked:text-shape-900 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[color:var(--shape-ring)] dark:peer-checked:bg-shape-800 dark:peer-checked:text-shape-50">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true">
                                <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
                            </svg>
                        </span>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="shape-scheme" value="dark" class="peer sr-only">
                        <span class="sr-only">Dark</span>
                        <span class="flex size-7 items-center justify-center rounded-full text-shape-500 peer-checked:bg-shape-100 peer-checked:text-shape-900 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[color:var(--shape-ring)] dark:peer-checked:bg-shape-800 dark:peer-checked:text-shape-50">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true">
                                <path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/>
                            </svg>
                        </span>
                    </label>
                </fieldset>
            </div>
        </div>
    </header>

    {{-- The splash. --}}
    <section class="relative isolate overflow-hidden border-b border-shape-200 dark:border-shape-800">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10"
             style="background-image: radial-gradient(60rem 34rem at 72% -18%, color-mix(in oklch, var(--color-shape-brand-500) 24%, transparent), transparent)"></div>
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 text-shape-400 opacity-35 dark:text-shape-600"
             style="background-image: radial-gradient(currentColor 1px, transparent 1px); background-size: 1.5rem 1.5rem; mask-image: linear-gradient(to bottom, black, transparent 65%)"></div>

        <div class="mx-auto max-w-6xl px-6 pt-20 pb-16 sm:pt-28 sm:pb-24">
            <p class="flex items-center gap-2 text-xs font-medium tracking-[0.18em] text-shape-500 uppercase">
                <span class="inline-block size-1.5 rounded-full bg-shape-brand-600 dark:bg-shape-brand-500"></span>
                Laravel &middot; Blade &middot; Tailwind v4
            </p>

            <h1 class="mt-6 max-w-3xl text-5xl font-semibold tracking-tighter text-balance sm:text-7xl">
                Every component in the library, on one page.
            </h1>

            <p class="mt-6 max-w-2xl text-lg text-pretty text-shape-600 dark:text-shape-400">
                Tokens, typography and surfaces, forms, overlays, feedback and data display.
                Everything here opens on the platform's own primitives, so try it with the
                keyboard. The documentation site renders the same components beside the prose
                that explains them.
            </p>

            <div class="mt-9 flex flex-wrap items-center gap-3">
                <x-shape::button variant="primary" size="lg" as="a" href="#foundations" icon-trailing="shape-arrow-right">
                    Start the tour
                </x-shape::button>
                <x-shape::button size="lg" as="a" href="/docs">Read the documentation</x-shape::button>
                <x-shape::button variant="ghost" size="lg" as="a" href="{{ $seeded ? '/' : '/seed' }}">
                    {{ $seeded ? 'Default palette' : 'One-colour seed' }}
                </x-shape::button>
            </div>

            {{-- Live, not a screenshot. The splash is made of the same components
                 the rest of the page catalogues, which is the only claim about
                 them worth putting at the top. --}}
            <div class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-shape::card>
                    <x-shape::stat label="Invoices sent" value="1,204" delta="12%" trend="up" />
                    <x-shape::separator />
                    <x-shape::progress :value="42" size="sm" tone="brand" label="Storage used" />
                </x-shape::card>

                <x-shape::card>
                    <x-shape::alert tone="success" heading="Payment received">
                        <x-shape::text size="sm" variant="muted">Invoice #1042 was paid in full.</x-shape::text>
                    </x-shape::alert>
                    <x-shape::card.footer>
                        <x-shape::button variant="primary" size="sm">Send receipt</x-shape::button>
                        <x-shape::button variant="ghost" size="sm">Void</x-shape::button>
                    </x-shape::card.footer>
                </x-shape::card>

                <x-shape::card class="sm:col-span-2 lg:col-span-1">
                    <x-shape::input label="Billing email" value="ada@example.com" size="sm" />
                    <x-shape::switch name="splash-notify" label="Email me about new invoices" checked />
                    <div class="flex items-center gap-3">
                        <x-shape::avatar.group>
                            <x-shape::avatar initials="AL" size="sm" />
                            <x-shape::avatar initials="GH" size="sm" />
                            <x-shape::avatar initials="KJ" size="sm" />
                        </x-shape::avatar.group>
                        <x-shape::badge label="Trial" tone="brand" size="sm" />
                    </div>
                </x-shape::card>
            </div>
        </div>
    </section>

    {{-- The contents. On a page this long the sticky bar is the wayfinding on a
         wide screen and this is the wayfinding on every other one. --}}
    <nav aria-label="Contents" class="mx-auto max-w-6xl px-6 py-12">
        <ol class="grid grid-cols-2 gap-px overflow-hidden rounded-shape-lg border border-shape-200 bg-shape-200 sm:grid-cols-4 dark:border-shape-800 dark:bg-shape-800">
            @foreach ([
                'foundations' => ['01', 'Foundations'],
                'actions' => ['02', 'Actions'],
                'surfaces' => ['03', 'Surfaces'],
                'forms' => ['04', 'Forms'],
                'overlays' => ['05', 'Overlays'],
                'feedback' => ['06', 'Feedback'],
                'data' => ['07', 'Data display'],
                'diagnostics' => ['08', 'Diagnostics'],
            ] as $anchor => [$number, $label])
                <li>
                    <a href="#{{ $anchor }}" class="flex h-full flex-col gap-1 bg-shape-50 px-4 py-3.5 hover:bg-white dark:bg-shape-950 dark:hover:bg-shape-900">
                        <span class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">{{ $number }}</span>
                        <span class="text-sm font-medium">{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- `space-y-24` sets a bottom margin on every child of this element, and the
         toaster and the confirm dialog near the end are two of them — which is
         exactly the bug the `shape-overlay` layer exists to outrank. They are
         here rather than tucked into a corner of the markup for that reason. If
         the dialog stops being centred or the toaster drifts off the corner,
         that layer has been moved. --}}
    <main class="space-y-24 pb-24">

    @if ($seeded)
        <section id="palette" class="scroll-mt-20">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                    <div>
                        <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">00</p>
                        <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Seed palette</h2>
                    </div>
                    <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                        One colour in, a whole interface out. This page is the same markup as the
                        default one, built with one more stylesheet imported.
                    </p>
                </div>

                <div class="mt-8 grid items-start gap-6">
                    <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                        <div class="space-y-1.5 px-6 py-5">
                            <h3 class="font-medium tracking-tight">Six seeds, one markup</h3>
                            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                                This page is built with <code class="text-shape-700 dark:text-shape-300">shape-seed.css</code>
                                imported on top of <code class="text-shape-700 dark:text-shape-300">shape.css</code>; the
                                default page is not. Below, the same markup under six seeds &mdash; each strip
                                sets one colour and nothing else, and the brand ramp, the neutrals behind the
                                text, the tint on the subtle button and the focus ring all derive from it.
                                Every one clears AA without a per-hue exception. The accent badge is the
                                one thing that does not move: it is the colour kept for &ldquo;look
                                here&rdquo;, and an accent that followed the brand around the wheel would
                                stop standing apart from it.
                            </p>
                        </div>
                        <div class="space-y-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                            @foreach ([
                                'cyan (the default)' => 'oklch(52% 0.105 223.128)',
                                'purple' => 'oklch(52% 0.16 300)',
                                'crimson' => 'oklch(52% 0.19 25)',
                                'forest' => 'oklch(52% 0.13 150)',
                                'amber' => 'oklch(52% 0.12 70)',
                                'near-grey' => 'oklch(52% 0.03 260)',
                            ] as $label => $seed)
                                <div data-shape-seed style="--shape-seed: {{ $seed }}"
                                     class="flex flex-wrap items-center gap-3 rounded-shape border border-shape-200 bg-shape-50 p-4 dark:border-shape-800 dark:bg-shape-900">
                                    <span class="w-36 shrink-0 text-xs font-medium text-shape-500">{{ $label }}</span>
                                    <x-shape::button variant="primary" tone="brand">Primary</x-shape::button>
                                    <x-shape::button variant="subtle" tone="brand">Subtle</x-shape::button>
                                    <x-shape::button variant="ghost" tone="brand">Ghost</x-shape::button>
                                    <x-shape::badge label="New" tone="accent" />
                                    <span class="text-sm text-shape-600 dark:text-shape-400">Body copy on the derived neutral.</span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </div>
            </div>
        </section>
    @endif

    <section id="foundations" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">01</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Foundations</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    The type scale, the elevation scale, and the icons Shape resolves on your behalf.
                </p>
            </div>

            <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Icons</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The slots Shape resolves itself, read out of <code class="text-xs">shape.icon_slots</code> &mdash;
                            these are what <code class="text-xs">shape:icon:replace</code> regenerates in your set.
                            Each variant is a drawing made at its own size. Nothing is scaled.
                        </p>
                    </div>
                    <div class="space-y-6 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="flex flex-wrap items-end gap-6 text-shape-700 dark:text-shape-300">
                            @foreach (array_keys(config('shape.icon_slots')) as $icon)
                                <div class="flex flex-col items-center gap-2">
                                    <x-shape::icon :name="$icon" />
                                    <span class="text-[11px] text-shape-500">{{ $icon }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-end gap-6 text-shape-700 dark:text-shape-300">
                            <x-shape::icon.shape-success size="xs" />
                            <x-shape::icon.shape-success size="sm" />
                            <x-shape::icon.shape-success size="base" />
                            <x-shape::icon.shape-success variant="solid" />
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Type scale</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Each size ships its own leading and tracking. Large sizes tighten both; small
                            sizes leave them alone.
                        </p>
                    </div>
                    <div class="space-y-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::heading size="2xl">Invoices outstanding</x-shape::heading>
                        <x-shape::heading size="xl">Invoices outstanding</x-shape::heading>
                        <x-shape::heading size="lg">Invoices outstanding</x-shape::heading>
                        <x-shape::heading size="base">Invoices outstanding</x-shape::heading>
                        <x-shape::heading size="sm">Invoices outstanding</x-shape::heading>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Level and size disagree</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Document hierarchy and visual hierarchy are separate props, because they
                            routinely disagree. Both of these are correct.
                        </p>
                    </div>
                    <div class="space-y-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::heading level="1" size="sm">Billing</x-shape::heading>
                        <x-shape::heading level="6" size="2xl">&pound;12,480</x-shape::heading>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Emphasis</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Muted reads the surface's own foreground rather than a fixed grey, and strong
                            emphasises with weight rather than colour.
                        </p>
                    </div>
                    <div class="space-y-2 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::text variant="strong">Payment received in full.</x-shape::text>
                        <x-shape::text>Thirty day terms apply to this account.</x-shape::text>
                        <x-shape::text variant="muted">Last edited two minutes ago.</x-shape::text>
                        <x-shape::text size="sm" variant="muted">Reference 1042-AC.</x-shape::text>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Elevation</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Tailwind's own scale, used directly. Each step is two parts &mdash; a soft cast and a
                            tight contact edge &mdash; with the light coming from directly above.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-4 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        {{-- Written out rather than interpolated: Tailwind scans source text, so a
                             class built from a variable is never generated. --}}
                        @foreach (['shadow-xs' => 'xs', 'shadow-sm' => 'sm', 'shadow-md' => 'md', 'shadow-lg' => 'lg', 'shadow-xl' => 'xl'] as $class => $label)
                            <div class="size-20 rounded-shape bg-white dark:bg-shape-900 {{ $class }} flex items-center justify-center text-sm text-shape-500">
                                {{ $label }}
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="actions" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">02</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Actions</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    One prop for how loud a button is, another for what it means, and a call site
                    that can still override both.
                </p>
            </div>

            <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Hierarchy</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            One primary action per screen. Everything else recedes.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::button variant="primary">Save changes</x-shape::button>
                        <x-shape::button>Cancel</x-shape::button>
                        <x-shape::button variant="subtle">Duplicate</x-shape::button>
                        <x-shape::button variant="ghost">Discard</x-shape::button>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Semantics</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Tone is a separate prop from hierarchy, so a destructive action can stay quiet
                            until the moment it matters.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::button variant="subtle" tone="danger" icon="shape-trash">Delete project</x-shape::button>
                        <x-shape::button variant="primary" tone="danger">Yes, delete it</x-shape::button>
                        <x-shape::button variant="primary" tone="brand" icon="shape-checked">Approve</x-shape::button>
                        <x-shape::button variant="subtle" tone="success" icon="shape-checked">Paid</x-shape::button>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Sizes</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Three sizes and a square, which is the shape a button takes when its icon is
                            the whole of its label.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::button size="sm" icon="shape-plus">Small</x-shape::button>
                        <x-shape::button icon="shape-plus">Base</x-shape::button>
                        <x-shape::button size="lg" icon="shape-plus">Large</x-shape::button>
                        <x-shape::button square icon="shape-trash" aria-label="Delete" />
                        <x-shape::button square variant="subtle" icon="shape-expand" aria-label="More" />
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Links and trailing icons</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The same component rendered as an anchor. An icon after the label points at
                            where it goes rather than at what it does.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::button as="a" href="#" icon-trailing="shape-arrow-right">Read the docs</x-shape::button>
                        <x-shape::button variant="ghost" as="a" href="#" icon-trailing="shape-arrow-right">Skip</x-shape::button>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Groups</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Related actions joined into one control. The inner corners square off, the
                            borders meet as one seam, and a dropdown trigger groups like the button it is.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-6 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::button.group label="View">
                            <x-shape::button>Day</x-shape::button>
                            <x-shape::button>Week</x-shape::button>
                            <x-shape::button>Month</x-shape::button>
                        </x-shape::button.group>

                        <x-shape::button.group label="Save">
                            <x-shape::button variant="primary" tone="brand" border>Save</x-shape::button>
                            <x-shape::dropdown.trigger
                                for="gallery-save-options"
                                variant="primary"
                                tone="brand"
                                border
                                square
                                icon="shape-expand"
                                aria-label="Save options"
                            />
                        </x-shape::button.group>

                        <x-shape::dropdown name="gallery-save-options">
                            <x-shape::dropdown.item icon="shape-checked">Save and publish</x-shape::dropdown.item>
                            <x-shape::dropdown.item>Save as draft</x-shape::dropdown.item>
                        </x-shape::dropdown>

                        <x-shape::button.group orientation="vertical" label="Sort">
                            <x-shape::button size="sm" icon="shape-trend-up">Newest</x-shape::button>
                            <x-shape::button size="sm" icon="shape-trend-down">Oldest</x-shape::button>
                        </x-shape::button.group>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Overriding</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Shape's own defaults carry zero specificity, so a class passed at the call site wins
                            with no <code class="text-xs">!important</code> and no class-merging utility.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::button variant="primary" class="rounded-full">Rounded full</x-shape::button>
                        <x-shape::button variant="primary" class="w-full">Full width</x-shape::button>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="surfaces" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">03</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Surfaces</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    What content sits on, what labels it, and what the screen says when there is
                    nothing to show yet.
                </p>
            </div>

            <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Badges</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Every state resolves a glyph of its own, so a badge stays readable in greyscale.
                            Squint, or turn the page monochrome &mdash; they still read apart.
                        </p>
                    </div>
                    <div class="space-y-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-shape::badge label="Paid" tone="success" />
                            <x-shape::badge label="Overdue" tone="danger" />
                            <x-shape::badge label="Pending" tone="warning" />
                            <x-shape::badge label="Sent" tone="info" />
                            <x-shape::badge label="Trial" tone="brand" />
                            <x-shape::badge label="New" tone="accent" />
                            <x-shape::badge label="Draft" />
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-shape::badge label="Paid" tone="success" variant="solid" />
                            <x-shape::badge label="Paid" tone="success" variant="subtle" />
                            <x-shape::badge label="Paid" tone="success" variant="outline" />
                            <x-shape::badge label="Paid" tone="success" :icon="false" />
                            <x-shape::badge label="Paid" tone="success" size="sm" />
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-shape::badge label="Overdue" tone="danger" dismissible />
                            <x-shape::badge label="Region: EMEA" dismissible />
                            <x-shape::badge label="Plan: Pro" tone="brand" variant="solid" dismissible />
                            <x-shape::badge label="Unpaid" tone="warning" variant="outline" size="sm" dismissible />
                            <x-shape::badge label="Tag" size="xs" dismissible />
                            <x-shape::badge label="Enterprise" tone="accent" size="lg" dismissible />
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Separators</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Reach for spacing first. A rule is what you use when spacing genuinely hasn't
                            done the job.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="max-w-md space-y-4">
                            <x-shape::separator />
                            <x-shape::separator label="Archived" />
                            <div class="flex items-center gap-3">
                                <x-shape::text size="sm">Draft</x-shape::text>
                                <x-shape::separator orientation="vertical" />
                                <x-shape::text size="sm" variant="muted">Edited 2 minutes ago</x-shape::text>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Cards</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            No border. A card separates itself with a surface shift and a resting shadow,
                            and owns the space between its own children.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-shape::card>
                                <x-shape::card.header>
                                    <x-shape::heading size="lg">Acme Corp</x-shape::heading>
                                    <x-shape::text size="sm" variant="muted">Invoice #1042 &middot; due 1 September</x-shape::text>
                                </x-shape::card.header>

                                <x-shape::separator />

                                <x-shape::text>Thirty day terms.</x-shape::text>

                                <x-shape::card.footer>
                                    <x-shape::button variant="primary">Send receipt</x-shape::button>
                                    <x-shape::button variant="ghost">Void</x-shape::button>
                                </x-shape::card.footer>
                            </x-shape::card>

                            <x-shape::card border padding="sm">
                                <x-shape::card.header>
                                    <x-shape::heading size="base">Bordered, tight</x-shape::heading>
                                    <x-shape::text size="sm" variant="muted">For cards on a surface too close to their own.</x-shape::text>
                                </x-shape::card.header>
                                <x-shape::badge label="Trial" tone="brand" size="sm" class="self-start" />
                            </x-shape::card>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Empty states</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The screen someone sees first, and again every time they filter everything away.
                            It ships designed rather than left to the application.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::card padding="none">
                            <x-shape::empty
                                icon="shape-info"
                                heading="No invoices yet"
                                description="Invoices you send will show up here, along with whether they've been paid."
                            >
                                <x-shape::button variant="primary" icon="shape-plus">New invoice</x-shape::button>
                                <x-shape::button variant="ghost">Import</x-shape::button>
                            </x-shape::empty>
                        </x-shape::card>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="forms" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">04</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Forms</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    One call site writes the whole field. The primitives underneath are there for
                    the times it doesn't.
                </p>
            </div>

            <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Fields</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            One call site writes the whole field: the label wired to the control, the
                            description it points at, the control, and the message for when it fails
                            validation. This is the shape of almost every field you will write.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="max-w-md space-y-5">
                            <x-shape::input label="Full name" placeholder="Alex Lindqvist" wire:model="full_name" />
                            <x-shape::input
                                type="email"
                                label="Billing email"
                                description="Invoices and receipts go here."
                                wire:model="billing_email"
                            />
                            <x-shape::textarea label="Notes" rows="3" placeholder="Anything the accounts team should know" wire:model="notes" />
                            <x-shape::select label="Plan" placeholder="Choose a plan" wire:model="plan">
                                <x-shape::select.option value="monthly" label="Monthly" />
                                <option value="yearly">Yearly &mdash; two months free</option>
                            </x-shape::select>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Breaking it apart</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The same primitives the shorthand assembles, written out. Reach for these when
                            you need a control between the label and the description, two controls in one
                            field, or markup of your own between the pieces &mdash; and note the one thing you
                            take on, <code class="text-xs">aria-describedby</code>, which the shorthand set
                            for you.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="max-w-md space-y-5">
                            <x-shape::field field-name="account_email">
                                <x-shape::label>Email</x-shape::label>
                                <x-shape::description>We'll only use this for receipts.</x-shape::description>
                                <x-shape::input type="email" aria-describedby="account_email-description" placeholder="you@example.com" />
                            </x-shape::field>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Invalid</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            There is no <code class="text-xs">invalid</code> prop. Styling keys off
                            <code class="text-xs">aria-invalid</code>, so what a screen reader announces and
                            what you can see cannot drift apart. The message itself is the one region cut
                            out of the fold.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="max-w-md">
                            <x-shape::field field-name="billing_email">
                                <x-shape::label>Billing email</x-shape::label>
                                <x-shape::input type="email" value="ada@example.com" aria-invalid="true" />
                                <x-shape::error />
                            </x-shape::field>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Sizes and disabled</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            A disabled control takes its label and its description down with it, which is
                            the state a field-shaped component exists to keep in one place.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="max-w-md space-y-3">
                            <x-shape::input size="sm" placeholder="Small" />
                            <x-shape::input placeholder="Base" />
                            <x-shape::input size="lg" placeholder="Large" />
                            <x-shape::field field-name="locked">
                                <x-shape::label>Disabled</x-shape::label>
                                <x-shape::description>The label and this copy dim with the control.</x-shape::description>
                                <x-shape::input value="Not editable" disabled />
                            </x-shape::field>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Switches</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            A native checkbox with <code class="text-xs">role="switch"</code>, moving on
                            <code class="text-xs">:checked</code>. No JavaScript, and it holds still for
                            anyone who asked for reduced motion.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="max-w-md space-y-3">
                            <x-shape::switch name="notify" label="Email me about new invoices" checked />
                            <x-shape::switch name="digest" label="Weekly digest" description="Sent Monday morning." />
                            <x-shape::switch name="sms" label="Text me too" tone="success" checked />
                            <x-shape::switch name="beta" label="Unavailable on your plan" disabled />
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Groups</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            A real <code class="text-xs">&lt;fieldset&gt;</code> with a
                            <code class="text-xs">&lt;legend&gt;</code>, which is the part hand-rolled radio
                            groups almost always miss. Every radio inherits the group's name.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="grid gap-8 sm:grid-cols-2">
                            <x-shape::field as="fieldset" field-name="billing_period" class="gap-3">
                                <x-shape::label as="legend">Billing period</x-shape::label>
                                <x-shape::radio value="monthly" label="Monthly" checked />
                                <x-shape::radio value="yearly" label="Yearly" description="Two months free." />
                                <x-shape::radio value="never" label="Invoice me" disabled />
                            </x-shape::field>

                            <x-shape::field as="fieldset" field-name="reminders" class="gap-3">
                                <x-shape::label as="legend">Send reminders on</x-shape::label>
                                <x-shape::checkbox value="mon" label="Monday" checked />
                                <x-shape::checkbox value="thu" label="Thursday" description="The day most invoices fall due." />
                                <x-shape::checkbox value="sun" label="Sunday" disabled />
                            </x-shape::field>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="overlays" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">05</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Overlays</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    A dialog and the popover attribute. The focus trap, the top layer and Escape are
                    the browser's &mdash; open one and try the keyboard.
                </p>
            </div>

            <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Modal and drawer</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Both are a <code class="text-xs">&lt;dialog&gt;</code>. The focus trap, the top
                            layer, Escape and the inertness of everything behind them are the browser's, not
                            this package's. Open one and try Tab.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::overlay.trigger for="delete-project" variant="subtle" tone="danger" icon="shape-trash">
                            Delete project
                        </x-shape::overlay.trigger>

                        <x-shape::overlay.trigger for="cart" icon="shape-plus">Open cart</x-shape::overlay.trigger>

                        <x-shape::overlay.trigger for="terms" variant="ghost">Terms (no Escape)</x-shape::overlay.trigger>

                        <x-shape::modal name="delete-project" heading="Delete project" description="This cannot be undone.">
                            <x-shape::text size="sm">
                                Every invoice attached to this project is deleted with it.
                            </x-shape::text>

                            <x-shape::overlay.footer>
                                <x-shape::overlay.close for="delete-project" label="Cancel" />
                                <x-shape::button variant="primary" tone="danger" icon="shape-trash">Delete</x-shape::button>
                            </x-shape::overlay.footer>
                        </x-shape::modal>

                        <x-shape::drawer name="cart" heading="Your cart" description="Two items." side="right">
                            <div class="space-y-4">
                                <x-shape::card border padding="sm">
                                    <x-shape::card.header>
                                        <x-shape::heading :level="3" size="sm">Annual plan</x-shape::heading>
                                        <x-shape::text size="sm" variant="muted">Renews 12 March</x-shape::text>
                                    </x-shape::card.header>
                                </x-shape::card>

                                <x-shape::card border padding="sm">
                                    <x-shape::card.header>
                                        <x-shape::heading :level="3" size="sm">Extra seats × 3</x-shape::heading>
                                        <x-shape::text size="sm" variant="muted">Prorated</x-shape::text>
                                    </x-shape::card.header>
                                </x-shape::card>

                                <x-shape::input label="Discount code" name="discount" placeholder="SPRING" />
                            </div>
                        </x-shape::drawer>

                        <x-shape::modal name="terms" heading="Accept the terms" :dismissible="false" size="sm">
                            <x-shape::text size="sm">
                                Escape does nothing here, and there is no close button. A dialog that has to be
                                answered rather than dismissed says so by leaving out both.
                            </x-shape::text>

                            <x-shape::overlay.footer>
                                <x-shape::overlay.close for="terms" label="Decline" />
                                <x-shape::button variant="primary" command="close" commandfor="terms">Accept</x-shape::button>
                            </x-shape::overlay.footer>
                        </x-shape::modal>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Menus and popovers</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The <code class="text-xs">popover</code> attribute supplies light dismiss, Escape
                            and the top layer. Arrow keys move between menu items; the menu sits above this
                            card even though the card clips its own overflow.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="flex flex-wrap items-center gap-3 overflow-hidden rounded-shape border border-shape-200 p-4 dark:border-shape-800">
                            <x-shape::dropdown.trigger for="row-actions" icon-trailing="shape-expand">Actions</x-shape::dropdown.trigger>

                            <x-shape::dropdown name="row-actions">
                                <x-shape::dropdown.item icon="shape-checked">Approve</x-shape::dropdown.item>
                                <x-shape::dropdown.item icon="shape-arrow-right" href="#">Open invoice</x-shape::dropdown.item>
                                <x-shape::separator class="my-1" />
                                <x-shape::dropdown.item icon="shape-trash" tone="danger">Delete</x-shape::dropdown.item>
                            </x-shape::dropdown>

                            <x-shape::popover.trigger for="usage" variant="subtle">Usage</x-shape::popover.trigger>

                            <x-shape::popover name="usage" placement="bottom-end">
                                <x-shape::heading :level="3" size="sm">This month</x-shape::heading>
                                <x-shape::text size="sm" variant="muted">4,210 of 10,000 requests.</x-shape::text>
                                <x-shape::badge label="42%" tone="success" />
                            </x-shape::popover>

                            <x-shape::dropdown.trigger for="more" variant="ghost" square icon="shape-expand" aria-label="More" />

                            <x-shape::dropdown name="more" placement="bottom-end">
                                <x-shape::dropdown.item icon="shape-plus">Duplicate</x-shape::dropdown.item>
                                <x-shape::dropdown.item icon="shape-checked" data-shape-keep-open>Stays open</x-shape::dropdown.item>
                            </x-shape::dropdown>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Tooltips</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Hover, and tab to them as well &mdash; a tooltip that only answers to a pointer is a
                            tooltip half the people using it never see.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::tooltip name="tip-archive" text="Archive this project">
                            <x-shape::button square variant="ghost" icon="shape-checked" aria-label="Archive" />
                        </x-shape::tooltip>

                        <x-shape::tooltip name="tip-delete" text="Delete permanently">
                            <x-shape::button square variant="ghost" icon="shape-trash" aria-label="Delete" />
                        </x-shape::tooltip>

                        <x-shape::tooltip name="tip-side" text="Shown to the right instead" placement="bottom">
                            <x-shape::button variant="subtle">Below</x-shape::button>
                        </x-shape::tooltip>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="feedback" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">06</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Feedback</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    What the interface says back &mdash; on the page, over it, and after a redirect.
                </p>
            </div>

            <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Alerts</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            A message that stays on the page. Every tone resolves a glyph of its own, and the
                            muted line inside each one reads a dialled-back version of the tone rather than a
                            grey &mdash; squint, or turn the colour off, and both still work. <code>variant</code>
                            sets how loud it is; the foreground follows it, so nothing has to be passed down.
                        </p>
                    </div>
                    <div class="space-y-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::alert tone="info" heading="Weekly digest is on">
                            <x-shape::text size="sm" variant="muted">Sent every Monday at 9am, in your timezone.</x-shape::text>
                        </x-shape::alert>

                        <x-shape::alert tone="success" heading="Payment received">
                            <x-shape::text size="sm" variant="muted">Invoice #1042 was paid in full.</x-shape::text>
                        </x-shape::alert>

                        <x-shape::alert tone="warning" heading="Your trial ends on Friday" dismissible>
                            <x-shape::text size="sm" variant="muted">Add a payment method to keep your projects.</x-shape::text>
                        </x-shape::alert>

                        <x-shape::alert tone="danger" heading="Card declined">
                            <x-shape::text size="sm" variant="muted">Update the card on file and try the payment again.</x-shape::text>
                        </x-shape::alert>

                        <x-shape::alert>
                            <x-shape::text size="sm" variant="muted">No tone, no glyph &mdash; the neutral case.</x-shape::text>
                        </x-shape::alert>

                        <x-shape::alert tone="danger" variant="outline" heading="Card declined">
                            <x-shape::text size="sm" variant="muted">Outline: a neutral border, the colour left to the ink and the glyph.</x-shape::text>
                        </x-shape::alert>

                        <x-shape::alert tone="danger" variant="solid" heading="Card declined" dismissible>
                            <x-shape::text size="sm" variant="muted">Solid: the muted line holds its colour on the fill, and so does the dismiss control.</x-shape::text>
                        </x-shape::alert>

                        <x-shape::alert tone="info" icon-placement="inline">
                            <x-shape::text size="sm" variant="muted">Inline: the glyph sits at the head of the first line and the message wraps under it, rather than into a column beside it.</x-shape::text>
                        </x-shape::alert>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Progress</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            A native <code class="text-xs">&lt;progress&gt;</code>, which is what lets a dynamic
                            value keep folding: the browser computes the width, so nothing in the template
                            divides one number by another. The percentage is printed at the call site.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="max-w-md space-y-4">
                            <div class="space-y-1.5">
                                <div class="flex items-baseline justify-between">
                                    <x-shape::text size="sm" id="storage-label">Storage used</x-shape::text>
                                    <x-shape::text size="sm" variant="muted">42%</x-shape::text>
                                </div>
                                <x-shape::progress :value="42" aria-labelledby="storage-label" />
                            </div>

                            <x-shape::progress :value="18" size="sm" tone="warning" label="Seats used" />
                            <x-shape::progress :value="92" size="lg" tone="danger" label="Quota" />
                            <x-shape::progress :value="70" tone="success" label="Onboarding" />
                            <x-shape::progress indeterminate label="Uploading" />
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Toasts</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Sent as a browser event, built by cloning a template the toaster already rendered.
                            Hover one to stop its timer. Open a modal first, then fire one &mdash; it appears above
                            the modal, which is what the top layer buys and what no z-index could.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        {{-- One attribute per field rather than a blob of JSON: an attribute bag
                             escapes a quote as `\"`, which HTML does not unescape, so JSON written
                             here arrives at the script unparseable. --}}
                        <x-shape::button variant="subtle" tone="success" data-toast="Invoice sent" data-toast-description="A copy went to billing@example.com" data-toast-tone="success">Success</x-shape::button>
                        <x-shape::button variant="subtle" tone="danger" data-toast="Card declined" data-toast-description="Announced assertively, unlike the rest." data-toast-tone="danger">Danger</x-shape::button>
                        <x-shape::button variant="subtle" tone="warning" data-toast="Trial ends Friday" data-toast-tone="warning">Warning</x-shape::button>
                        <x-shape::button variant="subtle" tone="info" data-toast="Digest is on" data-toast-tone="info">Info</x-shape::button>
                        <x-shape::button variant="subtle" tone="brand" data-toast="Published" data-toast-tone="brand">Brand</x-shape::button>
                        <x-shape::button variant="subtle" tone="accent" data-toast="Scheduled reports are here" data-toast-tone="accent">Accent</x-shape::button>
                        <x-shape::button variant="subtle" data-toast="Saved">Neutral</x-shape::button>
                        <x-shape::button variant="ghost" data-toast="Uploading" data-toast-description="Stays until dismissed." data-toast-duration="0">Sticky</x-shape::button>
                        <x-shape::button variant="ghost" as="a" href="/flash" icon-trailing="shape-arrow-right">Through the session</x-shape::button>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Confirm</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            One dialog in the layout, filled in per question. Accepting dispatches a window
                            event by whatever name the payload gave &mdash; which is the whole of this library's
                            server integration. Watch the console.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::button variant="subtle" tone="danger" icon="shape-trash" data-confirm="Delete project?" data-confirm-message="Every invoice attached to it goes too." data-confirm-accept="Delete" data-confirm-tone="danger" data-confirm-then="deleteProject">Delete project</x-shape::button>
                        <x-shape::button variant="subtle" data-confirm="Publish now?" data-confirm-message="It goes live immediately." data-confirm-accept="Publish" data-confirm-tone="brand" data-confirm-then="publish">Publish</x-shape::button>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- Direct children of the spaced container above, and deliberately so — see
         the note on `<main>`. --}}
    <x-shape::toaster />
    <x-shape::confirm />

    <section id="data" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">07</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Data display</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    Tables and lists, the numbers above them, and the empty state every one of them
                    ships with.
                </p>
            </div>

            <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Tables</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Content rather than a surface, so it takes its background from the card around it.
                            Rows separate on the body with a rule; the amounts are right-aligned and therefore
                            set in tabular figures. The menu in the last column is a popover in the top layer,
                            which is why it escapes a container that scrolls.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::card padding="none">
                            <x-shape::table>
                                <x-shape::table.head>
                                    <x-shape::table.heading label="Invoice" />
                                    <x-shape::table.heading label="Client" />
                                    <x-shape::table.heading label="State" />
                                    <x-shape::table.heading label="Amount" align="end" />
                                    <x-shape::table.heading label="" align="end" />
                                </x-shape::table.head>

                                <x-shape::table.body>
                                    @foreach ($invoices as $invoice)
                                        <x-shape::table.row>
                                            <x-shape::table.cell :value="$invoice['number']" class="font-medium" />
                                            <x-shape::table.cell :value="$invoice['client']" />
                                            <x-shape::table.cell>
                                                <x-shape::badge :label="$invoice['state']" :tone="$invoice['tone']" />
                                            </x-shape::table.cell>
                                            <x-shape::table.cell :value="$invoice['total']" align="end" />
                                            <x-shape::table.cell align="end">
                                                <x-shape::dropdown.trigger for="row-{{ $loop->index }}" variant="ghost" size="sm" icon="shape-expand">Actions</x-shape::dropdown.trigger>
                                                <x-shape::dropdown name="row-{{ $loop->index }}">
                                                    <x-shape::dropdown.item icon="shape-arrow-right">Open</x-shape::dropdown.item>
                                                    <x-shape::dropdown.item icon="shape-trash" tone="danger">Void</x-shape::dropdown.item>
                                                </x-shape::dropdown>
                                            </x-shape::table.cell>
                                        </x-shape::table.row>
                                    @endforeach
                                </x-shape::table.body>
                            </x-shape::table>
                        </x-shape::card>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Sticky header</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The bound on the height is the precondition, not a decoration: the wrapper scrolls
                            because of it, and a header sticking to the top of a box with no height of its own
                            sticks to nothing. Scroll inside the table &mdash; the rule under the header travels with
                            it, because it is an inset shadow rather than a border.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::card padding="none">
                            <x-shape::table class="max-h-48">
                                <x-shape::table.head sticky>
                                    <x-shape::table.heading label="Invoice" />
                                    <x-shape::table.heading label="Client" />
                                    <x-shape::table.heading label="Amount" align="end" />
                                </x-shape::table.head>

                                <x-shape::table.body>
                                    @foreach ($invoices as $invoice)
                                        @foreach ($invoices as $repeat)
                                            <x-shape::table.row>
                                                <x-shape::table.cell :value="$repeat['number']" />
                                                <x-shape::table.cell :value="$repeat['client']" />
                                                <x-shape::table.cell :value="$repeat['total']" align="end" />
                                            </x-shape::table.row>
                                        @endforeach
                                    @endforeach
                                </x-shape::table.body>
                            </x-shape::table>
                        </x-shape::card>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Avatars</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Initials are stated rather than derived &mdash; a derivation inside a folded component runs
                            once, at compile time. The group overlaps with a negative gap and a ring on each, and
                            stacks in DOM order, because choosing the order would be a z-index.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-6 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::avatar initials="AL" alt="Alex Lindqvist" size="xs" />
                        <x-shape::avatar initials="AL" alt="Alex Lindqvist" size="sm" />
                        <x-shape::avatar initials="AL" alt="Alex Lindqvist" />
                        <x-shape::avatar initials="AL" alt="Alex Lindqvist" size="lg" />

                        <x-shape::avatar initials="AL" alt="Alex Lindqvist, online" badge badge-tone="success" />
                        <x-shape::avatar initials="GH" alt="Gabriel Haas, 3 unread" badge="3" badge-tone="danger" badge-position="top-right" />
                        <x-shape::avatar icon="shape-user" alt="Acme Corp, 12 open issues" badge="12" badge-tone="brand" badge-position="top-right" square />

                        <x-shape::avatar.group>
                            <x-shape::avatar initials="AL" size="sm" />
                            <x-shape::avatar initials="GH" size="sm" />
                            <x-shape::avatar initials="KJ" size="sm" badge badge-tone="success" />
                        </x-shape::avatar.group>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Lists</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The table's answer for records that have one shape rather than several columns.
                            A rule on the list, nothing on the items, and an item is a slot because it almost
                            always holds three things rather than one value.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-shape::card padding="none" class="px-4">
                                <x-shape::list>
                                    @foreach ($people as $person)
                                        <x-shape::list.item>
                                            <x-shape::avatar :initials="$person['initials']" size="sm" />
                                            <x-shape::text class="grow">{{ $person['name'] }}</x-shape::text>
                                            <x-shape::badge :label="$person['role']" />
                                        </x-shape::list.item>
                                    @endforeach
                                </x-shape::list>
                            </x-shape::card>

                            <x-shape::card padding="none" class="px-4">
                                <x-shape::list as="ol">
                                    @foreach ($invoices->take(3) as $invoice)
                                        <x-shape::list.item>
                                            <x-shape::text class="grow">{{ $invoice['client'] }}</x-shape::text>
                                            <x-shape::text variant="muted" size="sm">{{ $invoice['total'] }}</x-shape::text>
                                        </x-shape::list.item>
                                    @endforeach
                                </x-shape::list>
                            </x-shape::card>
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Tabs</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Try the arrow keys, and Home and End. The strip is one tab stop and selection follows
                            focus. Underneath, the same component as navigation: links carry
                            <code class="text-xs">aria-current</code>, the strip carries no tablist role, and nothing
                            there needs the script at all.
                        </p>
                    </div>
                    <div class="space-y-4 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::tabs label="Billing">
                            <x-shape::tabs.tab for="tab-plan" selected>Plan</x-shape::tabs.tab>
                            <x-shape::tabs.tab for="tab-invoices">Invoices</x-shape::tabs.tab>
                            <x-shape::tabs.tab for="tab-usage">Usage</x-shape::tabs.tab>
                        </x-shape::tabs>

                        <x-shape::tabs.panel name="tab-plan" selected>
                            <x-shape::text>The Team plan, renewing on 1 September.</x-shape::text>
                        </x-shape::tabs.panel>
                        <x-shape::tabs.panel name="tab-invoices">
                            <x-shape::text>Five invoices, one of them overdue.</x-shape::text>
                        </x-shape::tabs.panel>
                        <x-shape::tabs.panel name="tab-usage">
                            <x-shape::progress :value="90" label="Storage used" />
                        </x-shape::tabs.panel>

                        <x-shape::tabs as="nav" label="Settings">
                            <x-shape::tabs.tab href="#general" selected>General</x-shape::tabs.tab>
                            <x-shape::tabs.tab href="#members">Members</x-shape::tabs.tab>
                            <x-shape::tabs.tab href="#api">API</x-shape::tabs.tab>
                        </x-shape::tabs>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Pagination</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            It takes the paginator, so it never reads the request &mdash; the route built this one and
                            <code class="text-xs">?page=2</code> moves it. Compiled rather than folded: a folded pager
                            would hold one visitor's page of links forever.
                        </p>
                    </div>
                    <div class="space-y-4 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <x-shape::pagination :paginator="$pages" />
                        <x-shape::pagination :paginator="$pages" simple />
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Stats</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            The value leads and the label recedes, and the direction is drawn as well as tinted &mdash;
                            three different arrows, not one arrow at three angles. The last one is the case that
                            needs the override: up is the wrong way for churn to go.
                        </p>
                    </div>
                    <div class="space-y-6 border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="grid gap-6 sm:grid-cols-4">
                            <x-shape::stat label="Invoices sent" value="1,204" delta="12%" trend="up" />
                            <x-shape::stat label="Outstanding" value="£18,400" delta="4%" trend="down" />
                            <x-shape::stat label="Average days to pay" value="21" delta="0" trend="flat" />
                            <x-shape::stat label="Churn" value="4.1%" delta="0.6pp" trend="up" tone="danger" />
                        </div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <x-shape::stat label="Current plan" value="Team" emphasis="label" description="Renews 1 September" />
                            <x-shape::stat label="Seats" value="12 of 20" description="Eight left before the next tier." />
                        </div>
                    </div>
                </article>

                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white lg:col-span-2 dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Empty by default</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            Nothing was passed to either of these but the copy. The empty state is in the markup
                            of every table and every list; a <code class="text-xs">:has()</code> rule removes it the
                            moment a row appears, which is how the promise is kept without anyone inspecting a slot.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-shape::card padding="none">
                                <x-shape::table empty-icon="shape-info" empty-heading="No invoices yet" empty-description="They will appear here as you raise them." />
                            </x-shape::card>
                            <x-shape::card padding="none">
                                <x-shape::list empty-icon="shape-plus" empty-heading="No teammates yet" empty-description="Invite someone to get started." />
                            </x-shape::card>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="diagnostics" class="scroll-mt-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3 border-b border-shape-200 pb-5 dark:border-shape-800">
                <div>
                    <p class="text-2xs font-medium tracking-[0.18em] text-shape-500 uppercase">08</p>
                    <h2 class="mt-1.5 text-2xl font-semibold tracking-tight">Diagnostics</h2>
                </div>
                <p class="max-w-md text-sm text-shape-600 dark:text-shape-400">
                    Not part of the package. Here so a placement bug is reportable rather than
                    describable.
                </p>
            </div>

            <div class="mt-8">
                <article class="overflow-hidden rounded-shape-lg border border-shape-200 bg-white dark:border-shape-800 dark:bg-shape-900">
                    <div class="space-y-1.5 px-6 py-5">
                        <h3 class="font-medium tracking-tight">Browser support and last placement</h3>
                        <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                            What this browser actually supports, and where the last overlay was put. Open a
                            menu, then open this. Delete the section when the overlays are settled.
                        </p>
                    </div>
                    <div class="border-t border-shape-200 bg-shape-50 p-6 dark:border-shape-800 dark:bg-shape-950">
                        <details class="rounded-shape border border-shape-200 bg-white p-4 text-sm dark:border-shape-800 dark:bg-shape-900">
                            <summary class="cursor-pointer font-medium">Browser support and last placement</summary>
                            <pre id="shape-diagnostics" class="mt-3 overflow-x-auto text-xs leading-6 text-shape-600 dark:text-shape-400"></pre>
                        </details>
                    </div>
                </article>
            </div>
        </div>
    </section>

    </main>

    <footer class="border-t border-shape-200 dark:border-shape-800">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-10 text-sm text-shape-600 dark:text-shape-400">
            <p>
                <span class="font-medium text-shape-900 dark:text-shape-100">Shape</span>
                &mdash; served by the workbench, from
                <code class="text-xs">{{ $seeded ? 'shape.css + shape-seed.css' : 'shape.css' }}</code>.
            </p>
            <div class="flex flex-wrap items-center gap-5">
                <a href="{{ $seeded ? '/' : '/seed' }}" class="underline-offset-4 hover:underline">{{ $seeded ? 'Default palette' : 'Seed palette' }}</a>
                <a href="/docs" class="underline-offset-4 hover:underline">Documentation</a>
                <a href="#foundations" class="underline-offset-4 hover:underline">Back to the top</a>
            </div>
        </div>
    </footer>

    {{-- Inlined for the same reason the stylesheet is: the preview needs no build step.
         There is no Alpine on this page — shape.js imports nothing and depends on nothing. --}}
    <script type="module">{!! file_get_contents(\Orchestra\Testbench\package_path('resources/js/shape.js')) !!}
        shape()

        // The theme switch. Not part of the package — the gallery's own chrome.
        //
        // Writing the attribute is the whole of it: `dark:` and the token blocks
        // in theme.src.css both key off `data-theme`, and "system" is the absence
        // of it rather than a third value, so nothing here has to ask the machine
        // what it prefers.
        const scheme = document.getElementById('shape-scheme')

        scheme.querySelector(`[value="${localStorage.getItem('shape-scheme') ?? 'system'}"]`)?.click()

        scheme.addEventListener('change', (event) => {
            const choice = event.target.value

            if (choice === 'system') {
                delete document.documentElement.dataset.theme
                localStorage.removeItem('shape-scheme')
            } else {
                document.documentElement.dataset.theme = choice
                localStorage.setItem('shape-scheme', choice)
            }
        })

        // Firing feedback. Not part of the package — the preview only. The event
        // is the API, so a demo needs nothing the package doesn't already give a
        // Livewire component.
        document.addEventListener('click', (event) => {
            const toast = event.target.closest?.('[data-toast]')

            if (toast) {
                dispatchEvent(new CustomEvent('shape:toast', { detail: { toast: {
                    heading: toast.dataset.toast,
                    description: toast.dataset.toastDescription ?? null,
                    tone: toast.dataset.toastTone ?? null,
                    duration: toast.dataset.toastDuration === undefined ? undefined : Number(toast.dataset.toastDuration),
                } } }))
            }

            const confirm = event.target.closest?.('[data-confirm]')

            if (confirm) {
                dispatchEvent(new CustomEvent('shape:confirm', { detail: { confirm: {
                    heading: confirm.dataset.confirm,
                    message: confirm.dataset.confirmMessage ?? null,
                    accept: confirm.dataset.confirmAccept ?? null,
                    tone: confirm.dataset.confirmTone ?? null,
                    then: confirm.dataset.confirmThen ?? null,
                    params: [1042],
                } } }))
            }
        })

        // What a Livewire component's `#[On('deleteProject')]` would be doing.
        addEventListener('deleteProject', (event) => {
            console.log('deleteProject', event.detail)
            dispatchEvent(new CustomEvent('shape:toast', { detail: { toast: { heading: 'Project deleted', tone: 'success' } } }))
        })

        addEventListener('publish', (event) => {
            console.log('publish', event.detail)
            dispatchEvent(new CustomEvent('shape:toast', { detail: { toast: { heading: 'Published', tone: 'brand' } } }))
        })

        // Diagnostics. Not part of the package — the preview only.
        const report = () => {
            const supported = (property, value) => {
                try { return CSS.supports(property, value) } catch { return 'threw' }
            }

            const open = document.querySelector('[data-shape-popover]:popover-open')
            const trigger = open ? document.querySelector(`[popovertarget="${open.id}"], [data-shape-tooltip-for="${open.id}"]`) : null
            const box = (el) => {
                if (!el) return 'none open'
                const r = el.getBoundingClientRect()
                return `top ${Math.round(r.top)}, left ${Math.round(r.left)}, ${Math.round(r.width)}x${Math.round(r.height)}`
            }

            document.getElementById('shape-diagnostics').textContent = [
                `userAgent                 ${navigator.userAgent}`,
                `viewport                  ${innerWidth}x${innerHeight}`,
                `colour scheme             ${document.documentElement.dataset.theme ?? 'system'}`,
                `anchor-name               ${supported('anchor-name', '--a')}`,
                `position-area             ${supported('position-area', 'block-end')}`,
                `position-try-fallbacks    ${supported('position-try-fallbacks', 'flip-block')}`,
                `dialog closedby           ${'closedBy' in HTMLDialogElement.prototype}`,
                `command / commandfor      ${'command' in HTMLButtonElement.prototype}`,
                `popover                   ${HTMLElement.prototype.hasOwnProperty('popover')}`,
                `showPopover              ${'showPopover' in HTMLElement.prototype}`,
                '',
                `open overlay              ${open ? open.id : 'none'}`,
                `  placement asked for     ${open ? open.getAttribute('data-shape-placement') : '-'}`,
                `  its trigger             ${box(trigger)}`,
                `  the panel               ${box(open)}`,
                `  computed position       ${open ? getComputedStyle(open).position : '-'}`,
                `  inline top / left       ${open ? `${open.style.top || 'unset'} / ${open.style.left || 'unset'}` : '-'}`,
                `  computed margin         ${open ? getComputedStyle(open).margin : '-'}`,
            ].join('\n')
        }

        report()
        document.addEventListener('toggle', report, true)
        addEventListener('resize', report)
    </script>
</body>
</html>
