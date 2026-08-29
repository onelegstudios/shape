<!DOCTYPE html>
<html lang="en" class="bg-shape-50 dark:bg-shape-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shape — component preview</title>
    {{-- Inlined so the preview needs no asset pipeline. Rebuild with `npm run preview`. --}}
    <style>{!! file_get_contents(\Orchestra\Testbench\package_path($stylesheet)) !!}</style>
</head>
<body class="text-shape-900 dark:text-shape-100 antialiased">
    <div class="mx-auto max-w-3xl px-6 py-16 space-y-14">

        <header class="space-y-2">
            <div class="flex items-baseline justify-between gap-4">
                <h1 class="text-3xl font-semibold tracking-tight">Shape</h1>
                <div class="flex items-baseline gap-4">
                    <a href="{{ $seeded ? '/' : '/seed' }}" class="text-sm font-medium text-shape-600 underline-offset-4 hover:underline dark:text-shape-400">{{ $seeded ? 'Default palette' : 'Seed palette' }} &rarr;</a>
                    <a href="/docs" class="text-sm font-medium text-shape-600 underline-offset-4 hover:underline dark:text-shape-400">Documentation &rarr;</a>
                </div>
            </div>
            <p class="text-shape-600 dark:text-shape-400">
                Every component in the library, on one page: tokens, typography and surfaces,
                forms, overlays, feedback and data display. Everything here opens on the
                platform's own primitives, so try it with the keyboard. The documentation
                site renders the same components beside the prose that explains them.
            </p>
        </header>

        @if ($seeded)
            <section class="space-y-4">
                <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Seed palette</h2>
                <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                    This page is built with <code class="text-shape-700 dark:text-shape-300">shape-seed.css</code>
                    imported on top of <code class="text-shape-700 dark:text-shape-300">shape.css</code>; the
                    default page is not. Below, the same markup under six seeds &mdash; each strip
                    sets one colour and nothing else, and the accent ramp, the neutrals behind the
                    text, the tint on the subtle button and the focus ring all derive from it.
                    Every one clears AA without a per-hue exception.
                </p>
                <div class="space-y-3">
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
                            <x-shape::button variant="primary" color="accent">Primary</x-shape::button>
                            <x-shape::button variant="subtle" color="accent">Subtle</x-shape::button>
                            <x-shape::button variant="ghost" color="accent">Ghost</x-shape::button>
                            <span class="text-sm text-shape-600 dark:text-shape-400">Body copy on the derived neutral.</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        </section>

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
                <x-shape::icon.check-circle size="xs" />
                <x-shape::icon.check-circle size="sm" />
                <x-shape::icon.check-circle size="base" />
                <x-shape::icon.check-circle variant="solid" />
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
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Alerts</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                A message that stays on the page. Every tone resolves a glyph of its own, and the
                muted line inside each one reads a dialled-back version of the tone rather than a
                grey — squint, or turn the colour off, and both still work.
            </p>
            <div class="space-y-3">
                <x-shape::alert color="accent" heading="Weekly digest is on">
                    <x-shape::text size="sm" variant="muted">Sent every Monday at 9am, in your timezone.</x-shape::text>
                </x-shape::alert>

                <x-shape::alert color="success" heading="Payment received">
                    <x-shape::text size="sm" variant="muted">Invoice #1042 was paid in full.</x-shape::text>
                </x-shape::alert>

                <x-shape::alert color="warning" heading="Your trial ends on Friday" dismissible>
                    <x-shape::text size="sm" variant="muted">Add a payment method to keep your projects.</x-shape::text>
                </x-shape::alert>

                <x-shape::alert color="danger" heading="Card declined">
                    <x-shape::text size="sm" variant="muted">Update the card on file and try the payment again.</x-shape::text>
                </x-shape::alert>

                <x-shape::alert>
                    <x-shape::text size="sm" variant="muted">No tone, no glyph — the neutral case.</x-shape::text>
                </x-shape::alert>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Progress</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                A native <code class="text-xs">&lt;progress&gt;</code>, which is what lets a dynamic
                value keep folding: the browser computes the width, so nothing in the template
                divides one number by another. The percentage is printed at the call site.
            </p>
            <div class="max-w-md space-y-4">
                <div class="space-y-1.5">
                    <div class="flex items-baseline justify-between">
                        <x-shape::text size="sm" id="storage-label">Storage used</x-shape::text>
                        <x-shape::text size="sm" variant="muted">42%</x-shape::text>
                    </div>
                    <x-shape::progress :value="42" aria-labelledby="storage-label" />
                </div>

                <x-shape::progress :value="18" size="sm" color="warning" label="Seats used" />
                <x-shape::progress :value="92" size="lg" color="danger" label="Quota" />
                <x-shape::progress :value="70" color="success" label="Onboarding" />
                <x-shape::progress indeterminate label="Uploading" />
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Toasts</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Sent as a browser event, built by cloning a template the toaster already rendered.
                Hover one to stop its timer. Open a modal first, then fire one — it appears above
                the modal, which is what the top layer buys and what no z-index could.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                {{-- One attribute per field rather than a blob of JSON: an attribute bag
                     escapes a quote as `\"`, which HTML does not unescape, so JSON written
                     here arrives at the script unparseable. --}}
                <x-shape::button variant="subtle" color="success" data-toast="Invoice sent" data-toast-description="A copy went to billing@example.com" data-toast-color="success">Success</x-shape::button>
                <x-shape::button variant="subtle" color="danger" data-toast="Card declined" data-toast-description="Announced assertively, unlike the rest." data-toast-color="danger">Danger</x-shape::button>
                <x-shape::button variant="subtle" color="warning" data-toast="Trial ends Friday" data-toast-color="warning">Warning</x-shape::button>
                <x-shape::button variant="subtle" color="accent" data-toast="Digest is on" data-toast-color="accent">Accent</x-shape::button>
                <x-shape::button variant="subtle" data-toast="Saved">Neutral</x-shape::button>
                <x-shape::button variant="ghost" data-toast="Uploading" data-toast-description="Stays until dismissed." data-toast-duration="0">Sticky</x-shape::button>
                <x-shape::button variant="ghost" as="a" href="/flash" icon-trailing="arrow-right">Through the session</x-shape::button>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Confirm</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                One dialog in the layout, filled in per question. Accepting dispatches a window
                event by whatever name the payload gave — which is the whole of this library's
                server integration. Watch the console.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <x-shape::button variant="subtle" color="danger" icon="trash" data-confirm="Delete project?" data-confirm-message="Every invoice attached to it goes too." data-confirm-accept="Delete" data-confirm-color="danger" data-confirm-then="deleteProject">Delete project</x-shape::button>
                <x-shape::button variant="subtle" data-confirm="Publish now?" data-confirm-message="It goes live immediately." data-confirm-accept="Publish" data-confirm-color="accent" data-confirm-then="publish">Publish</x-shape::button>
            </div>
        </section>

        {{-- Deliberately inside the spaced container. `space-y-14` sets a bottom margin on
             every child, including these two — which is exactly the bug the `shape-overlay`
             layer exists to outrank. If the dialog stops being centred or the toaster drifts
             off the corner, that layer has been moved. --}}
        <x-shape::toaster />
        <x-shape::confirm />

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Tables</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Content rather than a surface, so it takes its background from the card around it.
                Rows separate on the body with a rule; the amounts are right-aligned and therefore
                set in tabular figures. The menu in the last column is a popover in the top layer,
                which is why it escapes a container that scrolls.
            </p>
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
                                    <x-shape::badge :label="$invoice['state']" :color="$invoice['tone']" />
                                </x-shape::table.cell>
                                <x-shape::table.cell :value="$invoice['total']" align="end" />
                                <x-shape::table.cell align="end">
                                    <x-shape::dropdown.trigger for="row-{{ $loop->index }}" variant="ghost" size="sm" icon="chevron-down">Actions</x-shape::dropdown.trigger>
                                    <x-shape::dropdown name="row-{{ $loop->index }}">
                                        <x-shape::dropdown.item icon="arrow-right">Open</x-shape::dropdown.item>
                                        <x-shape::dropdown.item icon="trash" color="danger">Void</x-shape::dropdown.item>
                                    </x-shape::dropdown>
                                </x-shape::table.cell>
                            </x-shape::table.row>
                        @endforeach
                    </x-shape::table.body>
                </x-shape::table>
            </x-shape::card>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Empty by default</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Nothing was passed to either of these but the copy. The empty state is in the markup
                of every table and every list; a <code>:has()</code> rule removes it the moment a row
                appears, which is how the promise is kept without anyone inspecting a slot.
            </p>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-shape::card padding="none">
                    <x-shape::table empty-icon="information-circle" empty-heading="No invoices yet" empty-description="They will appear here as you raise them." />
                </x-shape::card>
                <x-shape::card padding="none">
                    <x-shape::list empty-icon="plus" empty-heading="No teammates yet" empty-description="Invite someone to get started." />
                </x-shape::card>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Sticky header</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                The bound on the height is the precondition, not a decoration: the wrapper scrolls
                because of it, and a header sticking to the top of a box with no height of its own
                sticks to nothing. Scroll inside the table — the rule under the header travels with
                it, because it is an inset shadow rather than a border.
            </p>
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
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Lists</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                The table's answer for records that have one shape rather than several columns.
                A rule on the list, nothing on the items, and an item is a slot because it almost
                always holds three things rather than one value.
            </p>
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
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Stats</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                The value leads and the label recedes, and the direction is drawn as well as tinted —
                three different arrows, not one arrow at three angles. The last one is the case that
                needs the override: up is the wrong way for churn to go.
            </p>
            <div class="grid gap-6 sm:grid-cols-4">
                <x-shape::stat label="Invoices sent" value="1,204" delta="12%" trend="up" />
                <x-shape::stat label="Outstanding" value="£18,400" delta="4%" trend="down" />
                <x-shape::stat label="Average days to pay" value="21" delta="0" trend="flat" />
                <x-shape::stat label="Churn" value="4.1%" delta="0.6pp" trend="up" color="danger" />
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <x-shape::stat label="Current plan" value="Team" emphasis="label" description="Renews 1 September" />
                <x-shape::stat label="Seats" value="12 of 20" description="Eight left before the next tier." />
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Avatars</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Initials are stated rather than derived — a derivation inside a folded component runs
                once, at compile time. The group overlaps with a negative gap and a ring on each, and
                stacks in DOM order, because choosing the order would be a z-index.
            </p>
            <div class="flex flex-wrap items-center gap-6">
                <x-shape::avatar initials="AL" alt="Ada Lovelace" size="xs" />
                <x-shape::avatar initials="AL" alt="Ada Lovelace" size="sm" />
                <x-shape::avatar initials="AL" alt="Ada Lovelace" />
                <x-shape::avatar initials="AL" alt="Ada Lovelace" size="lg" />

                <x-shape::avatar.group>
                    <x-shape::avatar initials="AL" size="sm" />
                    <x-shape::avatar initials="GH" size="sm" />
                    <x-shape::avatar initials="KJ" size="sm" />
                </x-shape::avatar.group>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Tabs</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                Try the arrow keys, and Home and End. The strip is one tab stop and selection follows
                focus. Underneath, the same component as navigation: links carry
                <code>aria-current</code>, the strip carries no tablist role, and nothing there needs
                the script at all.
            </p>
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
        </section>

        <section class="space-y-4">
            <h2 class="text-sm font-medium uppercase tracking-wider text-shape-500">Pagination</h2>
            <p class="max-w-prose text-sm text-shape-600 dark:text-shape-400">
                It takes the paginator, so it never reads the request — the route built this one and
                <code>?page=2</code> moves it. Compiled rather than folded: a folded pager would hold
                one visitor's page of links forever.
            </p>
            <x-shape::pagination :paginator="$pages" />
            <x-shape::pagination :paginator="$pages" simple />
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

        // Firing feedback. Not part of the package — the preview only. The event
        // is the API, so a demo needs nothing the package doesn't already give a
        // Livewire component.
        document.addEventListener('click', (event) => {
            const toast = event.target.closest?.('[data-toast]')

            if (toast) {
                dispatchEvent(new CustomEvent('shape:toast', { detail: { toast: {
                    heading: toast.dataset.toast,
                    description: toast.dataset.toastDescription ?? null,
                    color: toast.dataset.toastColor ?? null,
                    duration: toast.dataset.toastDuration === undefined ? undefined : Number(toast.dataset.toastDuration),
                } } }))
            }

            const confirm = event.target.closest?.('[data-confirm]')

            if (confirm) {
                dispatchEvent(new CustomEvent('shape:confirm', { detail: { confirm: {
                    heading: confirm.dataset.confirm,
                    message: confirm.dataset.confirmMessage ?? null,
                    accept: confirm.dataset.confirmAccept ?? null,
                    color: confirm.dataset.confirmColor ?? null,
                    then: confirm.dataset.confirmThen ?? null,
                    params: [1042],
                } } }))
            }
        })

        // What a Livewire component's `#[On('deleteProject')]` would be doing.
        addEventListener('deleteProject', (event) => {
            console.log('deleteProject', event.detail)
            dispatchEvent(new CustomEvent('shape:toast', { detail: { toast: { heading: 'Project deleted', color: 'success' } } }))
        })

        addEventListener('publish', (event) => {
            console.log('publish', event.detail)
            dispatchEvent(new CustomEvent('shape:toast', { detail: { toast: { heading: 'Published', color: 'accent' } } }))
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
