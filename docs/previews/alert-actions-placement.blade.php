<x-shape::alert tone="danger" heading="Three invoices failed" actions-placement="below">
    Retry them together, or open the billing log to see which cards were declined.

    <x-slot:actions>
        <x-shape::button size="sm" variant="primary" tone="danger">Retry all</x-shape::button>
        <x-shape::button size="sm" tone="danger">Open billing log</x-shape::button>
    </x-slot:actions>
</x-shape::alert>

<div class="max-w-sm">
    <x-shape::alert tone="brand" heading="You're on the beta" actions-placement="side" :icon="false">
        <x-slot:actions>
            <x-shape::button size="sm" variant="ghost">Leave</x-shape::button>
        </x-slot:actions>
    </x-shape::alert>
</div>
