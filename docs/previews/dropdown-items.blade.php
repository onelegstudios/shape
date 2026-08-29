<x-shape::dropdown.trigger for="item-shapes" icon-trailing="chevron-down">Actions</x-shape::dropdown.trigger>

<x-shape::dropdown name="item-shapes">
    <x-shape::dropdown.item icon="check">With an icon</x-shape::dropdown.item>
    <x-shape::dropdown.item>Without one</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="arrow-right" href="/invoices/1">A link</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="plus" disabled>Disabled</x-shape::dropdown.item>
    <x-shape::separator class="my-1" />
    <x-shape::dropdown.item icon="trash" color="danger">Delete</x-shape::dropdown.item>
</x-shape::dropdown>
