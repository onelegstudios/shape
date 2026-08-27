@blaze(fold: true, safe: ['name', 'heading', 'description'])

{{--
    A modal dialog, which is a `<dialog>` and almost nothing else.

    `showModal()` supplies the focus trap, the top layer, Escape, and inertness
    for everything behind it. All four are the parts of a modal that are hard to
    get right, and none of them is this package's code. What is left — a scrim,
    a scroll lock, a heading and a close button — is markup and CSS.

    So there is no backdrop component to compose: the scrim is `::backdrop`,
    styled in shape.css. The draft of this library had one, on the assumption
    that a modal is a stack of divs. It isn't any more.

    `heading` and `description` are props rather than slots, for the reason
    `empty` gives: a question about a prop is answered when the template
    compiles, and the same question asked of a slot is asked at run time and
    costs this component its fold. The slot is the body; actions go in
    `<x-shape::modal.footer>`.

    `name` is required and has no default, which is not an oversight. A folded
    component is pre-rendered once at compile time, so a generated id — `uniqid()`,
    a random string, a counter — would be generated once too, and every instance
    on the page would render the same one. Ids that must be unique per instance
    have to come from the call site.

    `dismissible: false` keeps Escape and the close button away from a dialog
    that has to be answered rather than dismissed. The Escape half is the one
    line of JavaScript a modal needs, because `cancel` is a native event and only
    a listener can prevent it.
--}}

@props([
    'name',
    'heading' => null,
    'description' => null,
    'size' => 'base',
    'dismissible' => true,
])

@php
$classes = Shape::classes()
    ->add('w-[calc(100vw-2rem)] gap-4 overflow-y-auto')
    ->add('[:where(&)]:rounded-shape-lg [:where(&)]:p-6 [:where(&)]:shadow-xl')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')
    ->add('[:where(&)]:border-0')

    ->add(match ($size) {
        'sm' => '[:where(&)]:max-w-sm',
        'lg' => '[:where(&)]:max-w-2xl',
        default => '[:where(&)]:max-w-lg',
    });
@endphp

<dialog
    id="{{ $name }}"
    {{ $attributes->class($classes) }}
    closedby="{{ $dismissible ? 'any' : 'none' }}"
    data-shape-modal
    data-shape-size="{{ $size }}"
    @unless ($dismissible) data-shape-persistent @endunless
    @if ($heading) aria-labelledby="{{ $name }}-heading" @endif
    @if ($description) aria-describedby="{{ $name }}-description" @endif
>
    @if ($heading)
        <div class="flex items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <x-shape::heading :level="2" size="lg" id="{{ $name }}-heading">{{ $heading }}</x-shape::heading>

                @if ($description)
                    <x-shape::text variant="muted" size="sm" id="{{ $name }}-description">{{ $description }}</x-shape::text>
                @endif
            </div>

            @if ($dismissible)
                <x-shape::overlay.close :for="$name" class="-mr-2 -mt-2" />
            @endif
        </div>
    @endif

    {{ $slot }}
</dialog>
