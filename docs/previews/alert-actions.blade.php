<x-shape::alert tone="warning" heading="Your card expires this month">
    Update your payment method before 30 September to keep the subscription active.

    <x-slot:actions>
        <x-shape::button size="sm" variant="primary" tone="warning">Update card</x-shape::button>
        <x-shape::button size="sm" variant="ghost" tone="warning">Remind me later</x-shape::button>
    </x-slot:actions>
</x-shape::alert>
