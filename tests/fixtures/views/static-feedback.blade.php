<x-shape::alert tone="warning" heading="Your trial ends on Friday">
    <x-shape::text size="sm">Add a payment method to keep your projects.</x-shape::text>
</x-shape::alert>

<x-shape::alert tone="danger" heading="Card declined" dismissible>
    Update the card on file and try again.
</x-shape::alert>

<x-shape::progress :value="42" label="Storage used" />

<x-shape::progress indeterminate size="sm" tone="brand" label="Uploading" />

<x-shape::confirm />
