@blaze(fold: true)

{{--
    Multi-line text. The same chrome as the input, minus the fixed height, plus
    a `rows` floor and vertical-only resizing — horizontal resizing lets a user
    drag a control out of the layout it was placed in.

    Name resolution, the shorthand and the `aria-describedby` caveat all work
    exactly as they do on the input; the reasoning is written out there.
--}}

@props([
    'size' => 'base',
    'rows' => 3,
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
    'rows' => $rows,
    'name' => $field,
    'id' => $controlId,
    'aria-describedby' => $describedBy,
]);

$classes = Shape::classes()
    ->add('block w-full min-w-0')
    ->add('transition-colors duration-100')

    // Vertical only. A textarea that can be dragged wider breaks out of
    // whatever laid it out.
    ->add('[:where(&)]:resize-y')

    ->add('[:where(&)]:rounded-shape [:where(&)]:shadow-sm')
    ->add('[:where(&)]:border [:where(&)]:border-shape-300 dark:[:where(&)]:border-shape-700')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')
    ->add('placeholder:text-[color:var(--shape-fg-muted)]')

    ->add(match ($size) {
        'sm' => '[:where(&)]:px-2.5 [:where(&)]:py-1.5 [:where(&)]:text-sm',
        'lg' => '[:where(&)]:px-4 [:where(&)]:py-3 [:where(&)]:text-base',
        default => '[:where(&)]:px-3 [:where(&)]:py-2 [:where(&)]:text-sm',
    })

    ->add('focus-visible:outline-2 focus-visible:outline-offset-0 focus-visible:outline-[var(--shape-ring)]')
    ->add('focus-visible:border-[var(--shape-ring)]')
    ->add('disabled:cursor-not-allowed disabled:opacity-50')
    ->add('aria-invalid:border-shape-danger-500 aria-invalid:focus-visible:outline-shape-danger-500');
@endphp

@if (filled($label))
    <x-shape::field :field-name="$controlId">
        <x-shape::label>{{ $label }}</x-shape::label>

        @if (filled($description))
            <x-shape::description>{{ $description }}</x-shape::description>
        @endif

        <textarea {{ $attributes->merge($defaults)->class($classes) }} data-shape-control data-shape-textarea>{{ $slot }}</textarea>

        <x-shape::error />
    </x-shape::field>
@else
    <textarea {{ $attributes->merge($defaults)->class($classes) }} data-shape-control data-shape-textarea>{{ $slot }}</textarea>
@endif
