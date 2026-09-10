@blaze(fold: true)

{{--
    One option out of several. Structurally the checkbox, with a round box and a
    dot instead of a tick.

    A radio only means anything as part of a group, and a group only has an
    accessible name if it is a real `<fieldset>` with a `<legend>`. That is what
    `<x-shape::field as="fieldset">` is for — and the shared `name` every radio
    in the group needs comes from that field, so no call site repeats it.

    The dot is a plain span rather than an icon: it is a filled circle, and
    asking an icon component for a filled circle would be more machinery than
    drawing it. Which also means the dot follows the box on the size scale by
    arithmetic rather than by picking a drawing — the checkbox's tick has to do
    both.

    `size` is the checkbox's, step for step, because a group of radios and a
    group of checkboxes in the same form are the same control to everyone
    looking at them.
--}}

@props([
    'label' => null,
    'description' => null,
    'value' => null,
    'tone' => null,
    'size' => 'base',
    'id' => null,
])

@aware([
    'fieldName' => null,
])

@php
$field = $attributes->get('name') ?? $fieldName ?? $attributes->whereStartsWith('wire:model')->first();
$controlId = $id ?? (filled($field) && filled($value) ? $field.'-'.$value : $field);
$describedBy = filled($description) && filled($controlId) ? $controlId.'-description' : null;

$defaults = array_filter([
    'name' => $field,
    'value' => $value,
    'id' => $controlId,
    'aria-describedby' => $describedBy,
]);

// The checkbox's five boxes, and the dot at roughly a third of each — the
// proportion the 16px box was drawn at, held across the scale.
[$boxSize, $dotSize, $gap, $type] = match ($size) {
    'xs' => ['size-3', 'size-1', 'gap-1.5', 'text-xs'],
    'sm' => ['size-3.5', 'size-1.5', 'gap-2', 'text-sm'],
    'lg' => ['size-5', 'size-2', 'gap-3', 'text-base'],
    'xl' => ['size-6', 'size-2.5', 'gap-3.5', 'text-lg'],
    default => ['size-4', 'size-1.5', 'gap-2.5', 'text-sm'],
};

$box = Shape::classes()
    ->add('peer col-start-1 row-start-1 appearance-none')
    ->add($boxSize.' shrink-0 transition-colors duration-100')
    ->add('[:where(&)]:rounded-full')
    ->add('[:where(&)]:border [:where(&)]:border-shape-300 dark:[:where(&)]:border-shape-600')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('checked:border-transparent checked:bg-[var(--shape-tone)]')
    ->add('focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]')
    ->add('disabled:cursor-not-allowed disabled:opacity-50')
    ->add('aria-invalid:border-shape-danger-500');
@endphp

<label
    class="group inline-flex items-start {{ $gap }} has-disabled:cursor-not-allowed"
    data-shape-radio
    data-shape-size="{{ $size }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
>
    <span class="grid place-items-center pt-0.5">
        <input type="radio" {{ $attributes->merge($defaults)->class($box) }} data-shape-control />
        <span class="col-start-1 row-start-1 pointer-events-none {{ $dotSize }} rounded-full bg-[var(--shape-tone-fg)] opacity-0 peer-checked:opacity-100"></span>
    </span>

    @if (filled($label))
        <span class="flex flex-col gap-0.5 group-has-disabled:opacity-50">
            <span class="{{ $type }} font-medium text-[color:var(--shape-fg)]">{{ $label }}</span>

            @if (filled($description))
                <span
                    @if ($describedBy) id="{{ $describedBy }}" @endif
                    class="{{ $type }} text-[color:var(--shape-fg-muted)]"
                >{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
