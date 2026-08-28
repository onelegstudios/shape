<x-shape::table>
    <x-shape::table.head sticky>
        <x-shape::table.heading label="Invoice" />
        <x-shape::table.heading label="Amount" align="end" />
    </x-shape::table.head>

    <x-shape::table.body>
        <x-shape::table.row>
            <x-shape::table.cell value="INV-1042" />
            <x-shape::table.cell value="£240.00" align="end" />
        </x-shape::table.row>
    </x-shape::table.body>
</x-shape::table>

<x-shape::list>
    <x-shape::list.item>
        <x-shape::avatar initials="AL" alt="Ada Lovelace" />
        Ada Lovelace
    </x-shape::list.item>
    <x-shape::list.item>
        <x-shape::avatar initials="GH" alt="Grace Hopper" size="sm" />
        Grace Hopper
    </x-shape::list.item>
</x-shape::list>

<x-shape::stat label="Invoices sent" value="1,204" delta="12%" trend="up" />

<x-shape::avatar.group>
    <x-shape::avatar initials="AL" />
</x-shape::avatar.group>

<x-shape::tabs label="Billing">
    <x-shape::tabs.tab for="plan" selected>Plan</x-shape::tabs.tab>
    <x-shape::tabs.tab for="usage">Usage</x-shape::tabs.tab>
</x-shape::tabs>

<x-shape::tabs.panel name="plan" selected>The Team plan.</x-shape::tabs.panel>
<x-shape::tabs.panel name="usage">Ninety per cent.</x-shape::tabs.panel>
