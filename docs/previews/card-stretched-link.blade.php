<x-shape::card>
    <x-shape::card.header>
        <x-shape::heading size="lg">
            <x-shape::card.link href="/invoices/1042">Acme Corp</x-shape::card.link>
        </x-shape::heading>
        <x-shape::text size="sm" variant="muted">Invoice #1042</x-shape::text>

        <x-shape::card.action>
            <x-shape::button variant="ghost" size="sm" square icon="shape-close" aria-label="Dismiss" />
        </x-shape::card.action>
    </x-shape::card.header>

    <x-shape::text>The whole card opens the invoice; the button still dismisses it.</x-shape::text>
</x-shape::card>
