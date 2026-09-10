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

    `size` moves the box, the tick inside it and the text beside it together. A
    box that grew and left its label at 14px would read as one control set next
    to another, rather than as a larger control.
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

// A group of checkboxes shares one name, so the value is what separates them.
$controlId = $id ?? (filled($field) && filled($value) ? $field.'-'.$value : $field);
$describedBy = filled($description) && filled($controlId) ? $controlId.'-description' : null;

$defaults = array_filter([
    'name' => $field,
    'value' => $value,
    'id' => $controlId,
    'aria-describedby' => $describedBy,
]);

// Five boxes, 12px to 24px, with the tick, the gap and the text moving with
// them. The steps are the control's own rather than the button's: a checkbox is
// a square beside a line of text, and what it has to agree with is the line.
//
// The tick is fetched at one size and drawn at another, which is two words for
// two jobs. The icon scale is 16, 20, 24 and the boxes here are not, so `size`
// picks which drawing is fetched — the 16px solid is a different path from the
// 20px one, not the same one scaled — and the class picks the box it is drawn
// in. That works because an icon's own size class carries zero specificity,
// which is what it is for.
//
// The `pt-0.5` under the wrapper is not in here, because it does not move: half
// the difference between the line and the box is two pixels at every step.
[$boxSize, $glyphBox, $glyphDrawing, $gap, $type] = match ($size) {
    'xs' => ['size-3', 'size-2.5', 'xs', 'gap-1.5', 'text-xs'],
    'sm' => ['size-3.5', 'size-3', 'xs', 'gap-2', 'text-sm'],
    'lg' => ['size-5', 'size-4', 'sm', 'gap-3', 'text-base'],
    'xl' => ['size-6', 'size-5', 'sm', 'gap-3.5', 'text-lg'],
    default => ['size-4', 'size-3.5', 'xs', 'gap-2.5', 'text-sm'],
};

$box = Shape::classes()
    ->add('peer col-start-1 row-start-1 appearance-none')
    ->add($boxSize.' shrink-0 transition-colors duration-100')
    ->add('[:where(&)]:rounded-[0.25rem]')
    ->add('[:where(&)]:border [:where(&)]:border-shape-300 dark:[:where(&)]:border-shape-600')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('checked:border-transparent checked:bg-[var(--shape-tone)]')
    ->add('indeterminate:border-transparent indeterminate:bg-[var(--shape-tone)]')
    ->add('focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]')
    ->add('disabled:cursor-not-allowed disabled:opacity-50')
    ->add('aria-invalid:border-shape-danger-500');

$glyph = 'col-start-1 row-start-1 pointer-events-none '.$glyphBox.' text-[color:var(--shape-tone-fg)] opacity-0';
@endphp

<label
    class="group inline-flex items-start {{ $gap }} has-disabled:cursor-not-allowed"
    data-shape-checkbox
    data-shape-size="{{ $size }}"
    data-shape-tone="{{ $tone ?? 'neutral' }}"
>
    <span class="grid place-items-center pt-0.5">
        <input type="checkbox" {{ $attributes->merge($defaults)->class($box) }} data-shape-control />
        <x-shape::icon.shape-checked :size="$glyphDrawing" class="{{ $glyph }} peer-checked:opacity-100" />
        <x-shape::icon.shape-indeterminate :size="$glyphDrawing" class="{{ $glyph }} peer-indeterminate:opacity-100" />
    </span>

    @if (filled($label))
        {{-- `peer-` cannot reach here: the input is nested one level down, and a
             peer has to be a previous sibling. The label is dimmed from the
             wrapper instead. --}}
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
