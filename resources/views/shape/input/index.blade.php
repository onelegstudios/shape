@blaze(fold: true)

{{--
    A text control, and — when you give it a `label` — the whole field around it.

    Two layers, one call site. The composed primitives are the real API; this
    shorthand is those primitives already assembled:

        <x-shape::input type="email" label="Email" wire:model="email" />

    The `@if` that chooses between them asks about a prop, and a question about a
    prop is answered when the template compiles. Asking the same question of a
    slot would be a runtime question, and would cost this component its fold.

    The control never has to be told its name. It resolves one, most specific
    first: an explicit `name`, then the name of the field it sits in, then the
    Livewire binding. So `wire:model="email"` alone wires up the label's `for`,
    this element's `id` and `name`, and the key the error message looks up.

    Everything the element needs goes through one merge array rather than a row
    of `@if` attributes, which is what keeps the two branches below to a line
    each instead of two copies of the same nine.
--}}

@props([
    'type' => 'text',
    'size' => 'base',
    'label' => null,
    'description' => null,
    'id' => null,
])

@aware([
    'fieldName' => null,
])

@php
// Most specific wins. Reading the bag is interpolation, not a branch, so it
// costs nothing at fold time.
$field = $attributes->get('name') ?? $fieldName ?? $attributes->whereStartsWith('wire:model')->first();
$controlId = $id ?? $field;

// Only claimed when this component renders the description itself. In the
// composed form the description is a sibling this control cannot see, so it
// stays quiet rather than pointing `aria-describedby` at an id that may not
// exist — a dangling reference is worse than an absent one. Composed call sites
// pass `aria-describedby="{name}-description"`; the shorthand never has to.
$describedBy = filled($description) && filled($controlId) ? $controlId.'-description' : null;

// `merge` treats these as defaults, so anything the caller passed still wins.
// Nulls are dropped rather than rendered as bare attributes.
$defaults = array_filter([
    'type' => $type,
    'name' => $field,
    'id' => $controlId,
    'aria-describedby' => $describedBy,
]);

$classes = Shape::classes()
    ->add('block w-full min-w-0')
    ->add('transition-colors duration-100')

    ->add('[:where(&)]:rounded-shape [:where(&)]:shadow-sm')
    ->add('[:where(&)]:border [:where(&)]:border-shape-300 dark:[:where(&)]:border-shape-700')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')
    ->add('placeholder:text-[color:var(--shape-fg-muted)]')

    ->add(match ($size) {
        'sm' => '[:where(&)]:h-8 [:where(&)]:px-2.5 [:where(&)]:text-sm',
        'lg' => '[:where(&)]:h-12 [:where(&)]:px-4 [:where(&)]:text-base',
        default => '[:where(&)]:h-10 [:where(&)]:px-3 [:where(&)]:text-sm',
    })

    // The ring is the same variable the button focuses with, so everything
    // focusable agrees without any of them knowing about the others.
    ->add('focus-visible:outline-2 focus-visible:outline-offset-0 focus-visible:outline-[var(--shape-ring)]')
    ->add('focus-visible:border-[var(--shape-ring)]')

    ->add('disabled:cursor-not-allowed disabled:opacity-50')

    // Invalid is styling driven by the aria state rather than by a second prop,
    // so what a screen reader announces and what a sighted user sees cannot
    // drift apart.
    ->add('aria-invalid:border-shape-danger-500 aria-invalid:focus-visible:outline-shape-danger-500');
@endphp

@if (filled($label))
    <x-shape::field :field-name="$controlId">
        <x-shape::label>{{ $label }}</x-shape::label>

        @if (filled($description))
            <x-shape::description>{{ $description }}</x-shape::description>
        @endif

        <input {{ $attributes->merge($defaults)->class($classes) }} data-shape-control data-shape-input />

        <x-shape::error />
    </x-shape::field>
@else
    <input {{ $attributes->merge($defaults)->class($classes) }} data-shape-control data-shape-input />
@endif
