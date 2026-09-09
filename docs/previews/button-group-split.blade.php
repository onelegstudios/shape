<x-shape::button.group label="Save">
    <x-shape::button variant="primary" tone="brand" border>Save</x-shape::button>
    <x-shape::dropdown.trigger
        for="save-options"
        variant="primary"
        tone="brand"
        border
        square
        icon="shape-expand"
        aria-label="Save options"
    />
</x-shape::button.group>

<x-shape::dropdown name="save-options">
    <x-shape::dropdown.item icon="shape-checked">Save and publish</x-shape::dropdown.item>
    <x-shape::dropdown.item>Save as draft</x-shape::dropdown.item>
</x-shape::dropdown>
