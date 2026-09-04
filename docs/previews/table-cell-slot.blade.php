<x-shape::table>
    <x-shape::table.head>
        <x-shape::table.heading label="Invoice" />
        <x-shape::table.heading label="State" />
        <x-shape::table.heading label="" />
    </x-shape::table.head>

    <x-shape::table.body>
        <x-shape::table.row>
            <x-shape::table.cell value="INV-1042" />
            <x-shape::table.cell>
                <x-shape::badge label="Paid" tone="success" />
            </x-shape::table.cell>
            <x-shape::table.cell align="end">
                <x-shape::button size="sm" variant="ghost" icon="shape-arrow-right" square aria-label="Open invoice" />
            </x-shape::table.cell>
        </x-shape::table.row>
    </x-shape::table.body>
</x-shape::table>
