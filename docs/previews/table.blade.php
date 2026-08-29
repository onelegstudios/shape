<x-shape::table>
    <x-shape::table.head>
        <x-shape::table.heading label="Invoice" />
        <x-shape::table.heading label="Client" />
        <x-shape::table.heading label="Total" align="end" />
    </x-shape::table.head>

    <x-shape::table.body>
        <x-shape::table.row>
            <x-shape::table.cell value="INV-1042" />
            <x-shape::table.cell value="Acme Corp" />
            <x-shape::table.cell value="£240.00" align="end" />
        </x-shape::table.row>
        <x-shape::table.row>
            <x-shape::table.cell value="INV-1041" />
            <x-shape::table.cell value="Globex" />
            <x-shape::table.cell value="£1,180.00" align="end" />
        </x-shape::table.row>
    </x-shape::table.body>
</x-shape::table>
