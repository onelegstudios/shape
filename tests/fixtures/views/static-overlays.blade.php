<x-shape::overlay.trigger for="delete-project" variant="subtle" color="danger" icon="shape-trash">
    Delete project
</x-shape::overlay.trigger>

<x-shape::modal name="delete-project" heading="Delete project" description="This cannot be undone.">
    <x-shape::text size="sm">Every invoice attached to it goes too.</x-shape::text>

    <x-shape::overlay.footer>
        <x-shape::overlay.close for="delete-project" label="Cancel" />
        <x-shape::button variant="primary" color="danger">Delete</x-shape::button>
    </x-shape::overlay.footer>
</x-shape::modal>

<x-shape::overlay.trigger for="cart">Cart</x-shape::overlay.trigger>

<x-shape::drawer name="cart" heading="Your cart" side="right">
    <x-shape::text size="sm">Two items.</x-shape::text>
</x-shape::drawer>

<x-shape::dropdown.trigger for="row-actions" variant="ghost" square icon="shape-expand" aria-label="Actions" />

<x-shape::dropdown name="row-actions">
    <x-shape::dropdown.item icon="shape-checked">Approve</x-shape::dropdown.item>
    <x-shape::dropdown.item href="/invoices/1">Open</x-shape::dropdown.item>
    <x-shape::dropdown.item icon="shape-trash" color="danger">Delete</x-shape::dropdown.item>
</x-shape::dropdown>

<x-shape::popover.trigger for="usage">Usage</x-shape::popover.trigger>

<x-shape::popover name="usage" placement="bottom-end">
    <x-shape::text size="sm" variant="muted">4,210 of 10,000 requests.</x-shape::text>
</x-shape::popover>

<x-shape::tooltip name="archive-tip" text="Archive this project">
    <x-shape::button square variant="ghost" icon="shape-checked" aria-label="Archive" />
</x-shape::tooltip>
