<x-shape::heading level="1" size="xl">Invoices</x-shape::heading>
<x-shape::text variant="muted">Everything you have sent this month.</x-shape::text>

<x-shape::card>
    <x-shape::card.header>
        <x-shape::heading size="lg">Acme Corp</x-shape::heading>
        <x-shape::text size="sm" variant="muted">Due 1 September</x-shape::text>
    </x-shape::card.header>

    <x-shape::separator />

    <x-shape::badge label="Paid" tone="success" />

    <x-shape::card.footer>
        <x-shape::button variant="primary">Send receipt</x-shape::button>
    </x-shape::card.footer>
</x-shape::card>

<x-shape::empty icon="shape-info" heading="No invoices yet" description="They will show up here.">
    <x-shape::button>New invoice</x-shape::button>
</x-shape::empty>
