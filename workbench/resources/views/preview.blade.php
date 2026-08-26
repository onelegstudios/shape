<!DOCTYPE html>
<html lang="en" class="bg-shape-50 dark:bg-shape-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shape — component preview</title>
    {{-- Inlined so the preview needs no asset pipeline. Rebuild with `npm run preview`. --}}
    <style>{!! file_get_contents(\Orchestra\Testbench\package_path('workbench/resources/css/preview.css')) !!}</style>
</head>
<body class="text-shape-900 dark:text-shape-100 antialiased">
    <div class="mx-auto max-w-3xl px-6 py-16 space-y-14">

        <header class="space-y-2">
            <h1 class="text-3xl font-semibold tracking-tight">Shape</h1>
            <p class="text-shape-600 dark:text-shape-400">
                Typography and surfaces — headings, body copy, cards, separators, badges and
                empty states, on top of the tokens and the two components from step one.
            </p>
        </header>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Hierarchy</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                One primary action per screen. Everything else recedes.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::button variant="primary">Save changes</x-shape::button>
                <x-shape::button>Cancel</x-shape::button>
                <x-shape::button variant="subtle">Duplicate</x-shape::button>
                <x-shape::button variant="ghost">Discard</x-shape::button>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Semantics</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Colour is a separate prop from hierarchy, so a destructive action can stay quiet
                until the moment it matters.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::button variant="subtle" color="danger" icon="trash">Delete project</x-shape::button>
                <x-shape::button variant="primary" color="danger">Yes, delete it</x-shape::button>
                <x-shape::button variant="primary" color="accent" icon="check">Approve</x-shape::button>
                <x-shape::button variant="subtle" color="success" icon="check-circle">Paid</x-shape::button>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Sizes</h2>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::button size="sm" icon="plus">Small</x-shape::button>
                <x-shape::button icon="plus">Base</x-shape::button>
                <x-shape::button size="lg" icon="plus">Large</x-shape::button>
                <x-shape::button square icon="trash" aria-label="Delete" />
                <x-shape::button square variant="subtle" icon="chevron-down" aria-label="More" />
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Links and trailing icons</h2>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::button as="a" href="#" icon-trailing="arrow-right">Read the docs</x-shape::button>
                <x-shape::button variant="ghost" as="a" href="#" icon-trailing="arrow-right">Skip</x-shape::button>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Overriding</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Shape's own defaults carry zero specificity, so a class passed at the call site wins
                with no <code class="text-xs">!important</code> and no class-merging utility.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::button variant="primary" class="rounded-full">Rounded full</x-shape::button>
                <x-shape::button variant="primary" class="w-full">Full width</x-shape::button>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Icons</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Each variant is a drawing made at its own size. Nothing is scaled.
            </p>
            <div class="flex flex-wrap items-end gap-6 text-shape-700 dark:text-shape-300">
                @foreach (['check', 'check-circle', 'x-mark', 'exclamation-triangle', 'arrow-right', 'plus', 'trash', 'chevron-down', 'loading'] as $icon)
                    <div class="flex flex-col items-center gap-2">
                        <x-shape::icon :name="$icon" />
                        <span class="text-[11px] text-shape-500">{{ $icon }}</span>
                    </div>
                @endforeach
            </div>
            <div class="flex items-end gap-6 pt-2 text-shape-700 dark:text-shape-300">
                <x-shape::icon.check-circle variant="micro" />
                <x-shape::icon.check-circle variant="mini" />
                <x-shape::icon.check-circle variant="solid" />
                <x-shape::icon.check-circle />
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Type scale</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Each size ships its own leading and tracking. Large sizes tighten both; small
                sizes leave them alone.
            </p>
            <div class="space-y-3">
                <x-shape::heading size="2xl">Invoices outstanding</x-shape::heading>
                <x-shape::heading size="xl">Invoices outstanding</x-shape::heading>
                <x-shape::heading size="lg">Invoices outstanding</x-shape::heading>
                <x-shape::heading size="base">Invoices outstanding</x-shape::heading>
                <x-shape::heading size="sm">Invoices outstanding</x-shape::heading>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Level and size disagree</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Document hierarchy and visual hierarchy are separate props, because they
                routinely disagree. Both of these are correct.
            </p>
            <div class="space-y-3">
                <x-shape::heading level="1" size="sm">Billing</x-shape::heading>
                <x-shape::heading level="6" size="2xl">&pound;12,480</x-shape::heading>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Emphasis</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Muted reads the surface's own foreground rather than a fixed grey, and strong
                emphasises with weight rather than colour.
            </p>
            <div class="space-y-2">
                <x-shape::text variant="strong">Payment received in full.</x-shape::text>
                <x-shape::text>Thirty day terms apply to this account.</x-shape::text>
                <x-shape::text variant="muted">Last edited two minutes ago.</x-shape::text>
                <x-shape::text size="sm" variant="muted">Reference 1042-AC.</x-shape::text>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Badges</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Every state resolves a glyph of its own, so a badge stays readable in greyscale.
                Squint, or turn the page monochrome — they still read apart.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::badge label="Paid" color="success" />
                <x-shape::badge label="Overdue" color="danger" />
                <x-shape::badge label="Pending" color="warning" />
                <x-shape::badge label="Trial" color="accent" />
                <x-shape::badge label="Draft" />
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::badge label="Paid" color="success" variant="solid" />
                <x-shape::badge label="Paid" color="success" variant="subtle" />
                <x-shape::badge label="Paid" color="success" variant="outline" />
                <x-shape::badge label="Paid" color="success" :icon="false" />
                <x-shape::badge label="Paid" color="success" size="sm" />
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Cards</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                No border. A card separates itself with a surface shift and a resting shadow,
                and owns the space between its own children.
            </p>
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
                    <x-shape::badge label="Trial" color="accent" size="sm" class="self-start" />
                </x-shape::card>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Separators</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Reach for spacing first. A rule is what you use when spacing genuinely hasn't
                done the job.
            </p>
            <div class="max-w-md space-y-4">
                <x-shape::separator />
                <x-shape::separator label="Archived" />
                <div class="flex items-center gap-3">
                    <x-shape::text size="sm">Draft</x-shape::text>
                    <x-shape::separator orientation="vertical" />
                    <x-shape::text size="sm" variant="muted">Edited 2 minutes ago</x-shape::text>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Empty states</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                The screen someone sees first, and again every time they filter everything away.
                It ships designed rather than left to the application.
            </p>
            <x-shape::card padding="none">
                <x-shape::empty
                    icon="information-circle"
                    heading="No invoices yet"
                    description="Invoices you send will show up here, along with whether they've been paid."
                >
                    <x-shape::button variant="primary" icon="plus">New invoice</x-shape::button>
                    <x-shape::button variant="ghost">Import</x-shape::button>
                </x-shape::empty>
            </x-shape::card>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Elevation</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Tailwind's own scale, used directly. Each step is two parts — a soft cast and a
                tight contact edge — with the light coming from directly above.
            </p>
            <div class="flex flex-wrap gap-4">
                {{-- Written out rather than interpolated: Tailwind scans source text, so a
                     class built from a variable is never generated. --}}
                @foreach (['shadow-xs' => 'xs', 'shadow-sm' => 'sm', 'shadow-md' => 'md', 'shadow-lg' => 'lg', 'shadow-xl' => 'xl'] as $class => $label)
                    <div class="size-20 rounded-shape bg-white dark:bg-shape-900 {{ $class }} flex items-center justify-center text-sm text-shape-500">
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </section>

    </div>
</body>
</html>
