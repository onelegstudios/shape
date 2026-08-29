<x-shape::tabs label="Billing">
    <x-shape::tabs.tab for="plan" selected>Plan</x-shape::tabs.tab>
    <x-shape::tabs.tab for="invoices">Invoices</x-shape::tabs.tab>
</x-shape::tabs>

<x-shape::tabs.panel name="plan" selected>
    <x-shape::text>You are on the yearly plan.</x-shape::text>
</x-shape::tabs.panel>

<x-shape::tabs.panel name="invoices">
    <x-shape::text>Twelve invoices, all paid.</x-shape::text>
</x-shape::tabs.panel>
