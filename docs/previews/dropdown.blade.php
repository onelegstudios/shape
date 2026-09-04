<x-shape::dropdown.trigger for="row-actions" icon-trailing="shape-expand">Actions</x-shape::dropdown.trigger>

<x-shape::dropdown name="row-actions">
    <x-shape::dropdown.item icon="shape-checked">Approve</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="shape-arrow-right" href="/invoices/1">Open invoice</x-shape::dropdown.item>
    <x-shape::separator class="my-1" />
    <x-shape::dropdown.item icon="shape-trash" tone="danger">Delete</x-shape::dropdown.item>
</x-shape::dropdown>
