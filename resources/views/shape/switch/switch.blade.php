@blaze(fold: true)

{{--
    A checkbox that reads as a switch.

    `role="switch"` on a real checkbox is the whole accessibility story: screen
    readers announce on and off instead of checked and unchecked, and every
    native keyboard and form behaviour is kept. Rebuilding this out of a button
    and some JS would trade all of that for a visual.

    No JavaScript. The knob moves on `:checked`, which the browser handles, so
    this component works before the Alpine layer exists and keeps working if it
    is never loaded.

    A switch takes effect immediately — that is what separates it from a
    checkbox, which waits for a submit. Reach for a checkbox inside a form.

    `size` moves the track, the knob and the distance the knob travels, and the
    three are one number: the track is two knobs plus the four pixels of inset
    it keeps at every step, and the travel is one knob. Setting them apart is
    how a switch ends up with its knob stopping short of the end.
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

// Five tracks, and the knob and its travel with them. The `left-0.5` the knob
// sits at is the one measurement that holds: two pixels of inset above, below
// and at both ends, at every step, which is what keeps the travel equal to the
// knob's own width.
[$trackSize, $knob, $gap, $type] = match ($size) {
    'xs' => ['h-3.5 w-6', 'size-2.5 peer-checked:translate-x-2.5', 'gap-1.5', 'text-xs'],
    'sm' => ['h-4 w-7', 'size-3 peer-checked:translate-x-3', 'gap-2', 'text-sm'],
    'lg' => ['h-6 w-11', 'size-5 peer-checked:translate-x-5', 'gap-3', 'text-base'],
    'xl' => ['h-7 w-13', 'size-6 peer-checked:translate-x-6', 'gap-3.5', 'text-lg'],
    default => ['h-5 w-9', 'size-4 peer-checked:translate-x-4', 'gap-2.5', 'text-sm'],
};

$track = Shape::classes()
    ->add('peer appearance-none')
    ->add($trackSize.' shrink-0 transition-colors duration-150')
    ->add('[:where(&)]:rounded-full')
    ->add('[:where(&)]:bg-shape-300 dark:[:where(&)]:bg-shape-700')
    ->add('checked:bg-[var(--shape-tone)]')
    ->add('focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]')
    ->add('disabled:cursor-not-allowed disabled:opacity-50')
    ->add('motion-reduce:transition-none');
@endphp

<label
    class="group inline-flex items-start {{ $gap }} has-disabled:cursor-not-allowed"
    data-shape-switch
    data-shape-size="{{ $size }}"
    data-shape-tone="{{ $tone ?? 'brand' }}"
>
    <span class="relative inline-flex shrink-0 items-center">
        <input type="checkbox" role="switch" {{ $attributes->merge($defaults)->class($track) }} data-shape-control />
        <span class="pointer-events-none absolute left-0.5 {{ $knob }} rounded-full bg-white shadow-sm transition-transform duration-150 motion-reduce:transition-none"></span>
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
