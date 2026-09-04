<x-shape::overlay.trigger for="delete-project" variant="subtle" tone="danger">
    Delete project
</x-shape::overlay.trigger>

<x-shape::modal name="delete-project" heading="Delete project" description="This cannot be undone.">
    <x-shape::text size="sm">Every invoice attached to it goes too.</x-shape::text>

    <x-shape::overlay.footer>
        <x-shape::overlay.close for="delete-project" label="Cancel" />
        <x-shape::button variant="primary" tone="danger">Delete</x-shape::button>
    </x-shape::overlay.footer>
</x-shape::modal>
