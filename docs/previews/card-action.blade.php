<x-shape::card>
    <x-shape::card.header>
        <x-shape::heading size="lg">Acme Corp</x-shape::heading>
        <x-shape::text size="sm" variant="muted">Invoice #1042</x-shape::text>

        <x-shape::card.action>
            <x-shape::dropdown.trigger for="invoice-actions" variant="ghost" size="sm" square icon="shape-expand" aria-label="Invoice actions" />
        </x-shape::card.action>
    </x-shape::card.header>

    <x-shape::text>Thirty day terms.</x-shape::text>
</x-shape::card>

<x-shape::dropdown name="invoice-actions">
    <x-shape::dropdown.item icon="shape-checked">Mark as paid</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="shape-trash" tone="danger">Void</x-shape::dropdown.item>
</x-shape::dropdown>
