{{-- This… --}}
<x-shape::input type="email" label="Email" description="For receipts." wire:model="email" />

{{-- …renders this. --}}
<x-shape::field field-name="email">
    <x-shape::label>Email</x-shape::label>
    <x-shape::description>For receipts.</x-shape::description>
    <x-shape::input type="email" aria-describedby="email-description" wire:model="email" />
    <x-shape::error />
</x-shape::field>
