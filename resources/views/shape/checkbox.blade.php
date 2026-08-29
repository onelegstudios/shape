@blaze(fold: true)

{{--
    A checkbox and the text that names it, inside one wrapping `<label>`.

    Wrapping is what makes the association: a control inside its own label needs
    no `for` and no `id`, so nothing can drift out of sync and there is nothing
    for a caller to remember. The `id` is still resolved and rendered, because
    `aria-describedby` on the description needs something to point at.

    The box is drawn rather than native — `appearance-none` plus a grid that
    stacks the input and its glyph in one cell, so the tick sits on top of the
    box without absolute positioning or a background-image SVG.

    There is no `indeterminate` prop. Indeterminate is a DOM property, not an
    attribute, so no server-rendered markup can set it; anything claiming to
    would be a prop that quietly does nothing. The glyph and its styling ship
    anyway, so the moment Livewire, Alpine or plain JS sets the property, the
    dash appears.
--}}

@props([
    'label' => null,
    'description' => null,
    'value' => null,
    'color' => null,
    'id' => null,
])

@aware([
    'fieldName' => null,
])

@php
$field = $attributes->get('name') ?? $fieldName ?? $attributes->whereStartsWith('wire:model')->first();

// A group of checkboxes shares one name, so the value is what separates them.
$controlId = $id ?? (filled($field) && filled($value) ? $field.'-'.$value : $field);
$describedBy = filled($description) && filled($controlId) ? $controlId.'-description' : null;

$defaults = array_filter([
    'name' => $field,
    'value' => $value,
    'id' => $controlId,
    'aria-describedby' => $describedBy,
]);

$box = Shape::classes()
    ->add('peer col-start-1 row-start-1 appearance-none')
    ->add('size-4 shrink-0 transition-colors duration-100')
    ->add('[:where(&)]:rounded-[0.25rem]')
    ->add('[:where(&)]:border [:where(&)]:border-shape-300 dark:[:where(&)]:border-shape-600')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('checked:border-transparent checked:bg-[var(--shape-tone)]')
    ->add('indeterminate:border-transparent indeterminate:bg-[var(--shape-tone)]')
    ->add('focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]')
    ->add('disabled:cursor-not-allowed disabled:opacity-50')
    ->add('aria-invalid:border-shape-danger-500');

$glyph = 'col-start-1 row-start-1 pointer-events-none size-3.5 text-[color:var(--shape-tone-fg)] opacity-0';
@endphp

<label
    class="group inline-flex items-start gap-2.5 has-disabled:cursor-not-allowed"
    data-shape-checkbox
    data-shape-tone="{{ $color ?? 'neutral' }}"
>
    <span class="grid place-items-center pt-0.5">
        <input type="checkbox" {{ $attributes->merge($defaults)->class($box) }} data-shape-control />
        <x-shape::icon.check size="xs" class="{{ $glyph }} peer-checked:opacity-100" />
        <x-shape::icon.minus size="xs" class="{{ $glyph }} peer-indeterminate:opacity-100" />
    </span>

    @if (filled($label))
        {{-- `peer-` cannot reach here: the input is nested one level down, and a
             peer has to be a previous sibling. The label is dimmed from the
             wrapper instead. --}}
        <span class="flex flex-col gap-0.5 group-has-disabled:opacity-50">
            <span class="text-sm font-medium text-[color:var(--shape-fg)]">{{ $label }}</span>

            @if (filled($description))
                <span
                    @if ($describedBy) id="{{ $describedBy }}" @endif
                    class="text-sm text-[color:var(--shape-fg-muted)]"
                >{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
