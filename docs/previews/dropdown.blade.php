<x-shape::dropdown.trigger for="row-actions" icon-trailing="chevron-down">Actions</x-shape::dropdown.trigger>

<x-shape::dropdown name="row-actions">
    <x-shape::dropdown.item icon="check">Approve</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="arrow-right" href="/invoices/1">Open invoice</x-shape::dropdown.item>
    <x-shape::separator class="my-1" />
    <x-shape::dropdown.item icon="trash" color="danger">Delete</x-shape::dropdown.item>
</x-shape::dropdown>
