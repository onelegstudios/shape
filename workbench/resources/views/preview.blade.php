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
                Phase one — design tokens, the class builder, provider wiring, and two components.
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
