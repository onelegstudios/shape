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
                Overlays — modal, drawer, dropdown, popover and tooltip — on top of the forms,
                typography, surfaces and tokens from the first three steps. Everything here opens
                on the platform's own primitives, so try it with the keyboard.
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
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Fields</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                One call site writes the whole field: the label wired to the control, the
                description it points at, the control, and the message for when it fails
                validation. This is the shape of almost every field you will write.
            </p>
            <div class="max-w-md space-y-5">
                <x-shape::input label="Full name" placeholder="Ada Lovelace" wire:model="full_name" />
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
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Breaking it apart</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                The same primitives the shorthand assembles, written out. Reach for these when
                you need a control between the label and the description, two controls in one
                field, or markup of your own between the pieces — and note the one thing you
                take on, <code class="text-xs">aria-describedby</code>, which the shorthand set
                for you.
            </p>
            <div class="max-w-md space-y-5">
                <x-shape::field field-name="account_email">
                    <x-shape::label>Email</x-shape::label>
                    <x-shape::description>We'll only use this for receipts.</x-shape::description>
                    <x-shape::input type="email" aria-describedby="account_email-description" placeholder="you@example.com" />
                </x-shape::field>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Invalid</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                There is no <code class="text-xs">invalid</code> prop. Styling keys off
                <code class="text-xs">aria-invalid</code>, so what a screen reader announces and
                what you can see cannot drift apart. The message itself is the one region cut
                out of the fold.
            </p>
            <div class="max-w-md">
                <x-shape::field field-name="billing_email">
                    <x-shape::label>Billing email</x-shape::label>
                    <x-shape::input type="email" value="ada@example.com" aria-invalid="true" />
                    <x-shape::error />
                </x-shape::field>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Sizes and disabled</h2>
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
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Groups</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                A real <code class="text-xs">&lt;fieldset&gt;</code> with a
                <code class="text-xs">&lt;legend&gt;</code>, which is the part hand-rolled radio
                groups almost always miss. Every radio inherits the group's name.
            </p>
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
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Switches</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                A native checkbox with <code class="text-xs">role="switch"</code>, moving on
                <code class="text-xs">:checked</code>. No JavaScript, and it holds still for
                anyone who asked for reduced motion.
            </p>
            <div class="max-w-md space-y-3">
                <x-shape::switch name="notify" label="Email me about new invoices" checked />
                <x-shape::switch name="digest" label="Weekly digest" description="Sent Monday morning." />
                <x-shape::switch name="sms" label="Text me too" color="success" checked />
                <x-shape::switch name="beta" label="Unavailable on your plan" disabled />
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Modal and drawer</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Both are a <code class="text-xs">&lt;dialog&gt;</code>. The focus trap, the top
                layer, Escape and the inertness of everything behind them are the browser's, not
                this package's. Open one and try Tab.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::overlay.trigger for="delete-project" variant="subtle" color="danger" icon="trash">
                    Delete project
                </x-shape::overlay.trigger>

                <x-shape::overlay.trigger for="cart" icon="plus">Open cart</x-shape::overlay.trigger>

                <x-shape::overlay.trigger for="terms" variant="ghost">Terms (no Escape)</x-shape::overlay.trigger>
            </div>

            <x-shape::modal name="delete-project" heading="Delete project" description="This cannot be undone.">
                <x-shape::text size="sm">
                    Every invoice attached to this project is deleted with it.
                </x-shape::text>

                <x-shape::overlay.footer>
                    <x-shape::overlay.close for="delete-project" label="Cancel" />
                    <x-shape::button variant="primary" color="danger" icon="trash">Delete</x-shape::button>
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
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Menus and popovers</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                The <code class="text-xs">popover</code> attribute supplies light dismiss, Escape
                and the top layer. Arrow keys move between menu items; the menu sits above this
                card even though the card clips its own overflow.
            </p>
            <div class="flex flex-wrap items-center gap-3 overflow-hidden rounded-shape border border-shape-200 p-4 dark:border-shape-800">
                <x-shape::dropdown.trigger for="row-actions" icon-trailing="chevron-down">Actions</x-shape::dropdown.trigger>

                <x-shape::dropdown name="row-actions">
                    <x-shape::dropdown.item icon="check">Approve</x-shape::dropdown.item>
                    <x-shape::dropdown.item icon="arrow-right" href="#">Open invoice</x-shape::dropdown.item>
                    <x-shape::separator class="my-1" />
                    <x-shape::dropdown.item icon="trash" color="danger">Delete</x-shape::dropdown.item>
                </x-shape::dropdown>

                <x-shape::popover.trigger for="usage" variant="subtle">Usage</x-shape::popover.trigger>

                <x-shape::popover name="usage" placement="bottom-end">
                    <x-shape::heading :level="3" size="sm">This month</x-shape::heading>
                    <x-shape::text size="sm" variant="muted">4,210 of 10,000 requests.</x-shape::text>
                    <x-shape::badge label="42%" color="success" />
                </x-shape::popover>

                <x-shape::dropdown.trigger for="more" variant="ghost" square icon="chevron-down" aria-label="More" />

                <x-shape::dropdown name="more" placement="bottom-end">
                    <x-shape::dropdown.item icon="plus">Duplicate</x-shape::dropdown.item>
                    <x-shape::dropdown.item icon="check" data-shape-keep-open>Stays open</x-shape::dropdown.item>
                </x-shape::dropdown>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Tooltips</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Hover, and tab to them as well — a tooltip that only answers to a pointer is a
                tooltip half the people using it never see.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::tooltip name="tip-archive" text="Archive this project">
                    <x-shape::button square variant="ghost" icon="check" aria-label="Archive" />
                </x-shape::tooltip>

                <x-shape::tooltip name="tip-delete" text="Delete permanently">
                    <x-shape::button square variant="ghost" icon="trash" aria-label="Delete" />
                </x-shape::tooltip>

                <x-shape::tooltip name="tip-side" text="Shown to the right instead" placement="bottom">
                    <x-shape::button variant="subtle">Below</x-shape::button>
                </x-shape::tooltip>
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

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Diagnostics</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                What this browser actually supports, and where the last overlay was put. Here to
                make a placement bug reportable instead of describable — open a menu, then open
                this. Delete the section when the overlays are settled.
            </p>
            <details class="rounded-shape border border-shape-200 p-4 text-sm dark:border-shape-800">
                <summary class="cursor-pointer font-medium">Browser support and last placement</summary>
                <pre id="shape-diagnostics" class="mt-3 overflow-x-auto text-xs leading-6 text-shape-600 dark:text-shape-400"></pre>
            </details>
        </section>

    </div>

    {{-- Inlined for the same reason the stylesheet is: the preview needs no build step.
         There is no Alpine on this page — shape.js imports nothing and depends on nothing. --}}
    <script type="module">{!! file_get_contents(\Orchestra\Testbench\package_path('resources/js/shape.js')) !!}
        shape()

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
                `anchor-name               ${supported('anchor-name', '--a')}`,
                `position-area             ${supported('position-area', 'block-end')}`,
                `position-try-fallbacks    ${supported('position-try-fallbacks', 'flip-block')}`,
                `dialog closedby           ${'closedBy' in HTMLDialogElement.prototype}`,
                `command / commandfor      ${'command' in HTMLButtonElement.prototype}`,
                `popover                   ${HTMLElement.prototype.hasOwnProperty('popover')}`,
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
