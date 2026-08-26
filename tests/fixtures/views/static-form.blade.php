<x-shape::field name="email">
    <x-shape::label>Email</x-shape::label>
    <x-shape::description>We'll only use this for receipts.</x-shape::description>
    <x-shape::input type="email" aria-describedby="email-description" wire:model="email" />
    <x-shape::error />
</x-shape::field>

<x-shape::input type="text" label="Company" description="As it appears on the invoice." wire:model="company" />

<x-shape::textarea label="Notes" wire:model="notes" />

<x-shape::select label="Plan" placeholder="Choose a plan" wire:model="plan">
    <x-shape::select.option value="monthly" label="Monthly" />
    <option value="yearly">Yearly</option>
</x-shape::select>

<x-shape::field as="fieldset" name="billing">
    <x-shape::label as="legend">Billing period</x-shape::label>
    <x-shape::radio value="monthly" label="Monthly" />
    <x-shape::radio value="yearly" label="Yearly" description="Two months free." />
    <x-shape::error />
</x-shape::field>

<x-shape::checkbox name="terms" value="1" label="I agree to the terms" />
<x-shape::switch name="notify" label="Email me about new invoices" />
