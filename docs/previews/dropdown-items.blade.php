<x-shape::dropdown.trigger for="item-shapes" icon-trailing="shape-expand">Actions</x-shape::dropdown.trigger>

<x-shape::dropdown name="item-shapes">
    <x-shape::dropdown.item icon="shape-checked">With an icon</x-shape::dropdown.item>
    <x-shape::dropdown.item>Without one</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="shape-arrow-right" href="/invoices/1">A link</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="shape-plus" disabled>Disabled</x-shape::dropdown.item>
    <x-shape::separator class="my-1" />
    <x-shape::dropdown.item icon="shape-trash" color="danger">Delete</x-shape::dropdown.item>
</x-shape::dropdown>
