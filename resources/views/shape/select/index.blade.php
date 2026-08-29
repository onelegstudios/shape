@blaze(fold: true)

{{--
    A native `<select>`, restyled.

    Options are children, not an `:options` array. A slot rules memoization out,
    but a select is not a per-row component so memo was never available to it
    anyway — and children compose with `@foreach`, with `<optgroup>`, and with a
    caller's own markup, none of which an array prop does without inventing a
    convention for label and value keys.

    `appearance-none` removes the platform arrow, so the component draws its own
    and reserves the space for it. The icon is `aria-hidden` and the select keeps
    its native keyboard behaviour, which is the whole reason for not rebuilding
    this control out of divs.

    `placeholder` is a disabled, selected, hidden first option — the only way to
    show prompt text in a native select without it being choosable.
--}}

@props([
    'size' => 'base',
    'placeholder' => null,
    'label' => null,
    'description' => null,
    'id' => null,
])

@aware([
    'fieldName' => null,
])

@php
$field = $attributes->get('name') ?? $fieldName ?? $attributes->whereStartsWith('wire:model')->first();
$controlId = $id ?? $field;
$describedBy = filled($description) && filled($controlId) ? $controlId.'-description' : null;

$defaults = array_filter([
    'name' => $field,
    'id' => $controlId,
    'aria-describedby' => $describedBy,
]);

$classes = Shape::classes()
    ->add('block w-full min-w-0 appearance-none')
    ->add('transition-colors duration-100')

    ->add('[:where(&)]:rounded-shape [:where(&)]:shadow-sm')
    ->add('[:where(&)]:border [:where(&)]:border-shape-300 dark:[:where(&)]:border-shape-700')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')

    // Room for the arrow this component draws itself.
    ->add(match ($size) {
        'sm' => '[:where(&)]:h-8 [:where(&)]:pl-2.5 [:where(&)]:pr-8 [:where(&)]:text-sm',
        'lg' => '[:where(&)]:h-12 [:where(&)]:pl-4 [:where(&)]:pr-11 [:where(&)]:text-base',
        default => '[:where(&)]:h-10 [:where(&)]:pl-3 [:where(&)]:pr-10 [:where(&)]:text-sm',
    })

    ->add('focus-visible:outline-2 focus-visible:outline-offset-0 focus-visible:outline-[var(--shape-ring)]')
    ->add('focus-visible:border-[var(--shape-ring)]')
    ->add('disabled:cursor-not-allowed disabled:opacity-50')
    ->add('aria-invalid:border-shape-danger-500 aria-invalid:focus-visible:outline-shape-danger-500');

// The wrapper positions the arrow. `has-disabled:` dims the arrow along with the
// control, so the two never disagree about whether this thing is interactive.
$wrapper = 'relative block w-full has-disabled:opacity-50';

$arrow = match ($size) {
    'lg' => 'pointer-events-none absolute inset-y-0 right-4 flex items-center text-[color:var(--shape-fg-muted)]',
    default => 'pointer-events-none absolute inset-y-0 right-3 flex items-center text-[color:var(--shape-fg-muted)]',
};
@endphp

@if (filled($label))
    <x-shape::field :field-name="$controlId">
        <x-shape::label>{{ $label }}</x-shape::label>

        @if (filled($description))
            <x-shape::description>{{ $description }}</x-shape::description>
        @endif

        <span class="{{ $wrapper }}" data-shape-select>
            <select {{ $attributes->merge($defaults)->class($classes) }} data-shape-control>
                @if (filled($placeholder))
                    <option value="" disabled selected hidden>{{ $placeholder }}</option>
                @endif
                {{ $slot }}
            </select>
            <span class="{{ $arrow }}"><x-shape::icon.chevron-down size="sm" /></span>
        </span>

        <x-shape::error />
    </x-shape::field>
@else
    <span class="{{ $wrapper }}" data-shape-select>
        <select {{ $attributes->merge($defaults)->class($classes) }} data-shape-control>
            @if (filled($placeholder))
                <option value="" disabled selected hidden>{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        <span class="{{ $arrow }}"><x-shape::icon.chevron-down size="sm" /></span>
    </span>
@endif
