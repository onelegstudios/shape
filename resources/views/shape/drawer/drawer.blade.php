@blaze(fold: true, safe: ['name', 'heading', 'description'])

{{--
    A panel that arrives from an edge. Structurally the modal: a `<dialog>`
    opened with `showModal()`, so the focus trap, the top layer, Escape and
    inertness are the platform's rather than this package's.

    What differs is placement, and placement is CSS. The side sets a data
    attribute; shape.css pins the dialog to that edge and slides it in from the
    same direction it is pinned to. Nothing here measures anything.

    It shares the modal's trigger, close and footer — `<x-shape::overlay.*>` —
    because they are the same components doing the same job. There is no
    `drawer.trigger` alias, since the thing being triggered is named in `for`
    and a second name for one component is a second thing to keep in sync.

    A drawer holds a form, a filter panel, a cart. Long content scrolls inside
    it, which is why the body is its own scroll container rather than the dialog:
    the heading stays put while the content moves under it.
--}}

@props([
    'name',
    'heading' => null,
    'description' => null,
    'side' => 'right',
    'size' => 'base',
    'dismissible' => true,
])

@php
$classes = Shape::classes()
    ->add('gap-4')
    ->add('[:where(&)]:p-6 [:where(&)]:shadow-xl')
    ->add('[:where(&)]:bg-white dark:[:where(&)]:bg-shape-900')
    ->add('[:where(&)]:text-[color:var(--shape-fg)]')
    ->add('[:where(&)]:border-0')

    // A side drawer is sized across the viewport; a bottom one is sized down it.
    ->add(match ($side) {
        'bottom' => match ($size) {
            'sm' => '[:where(&)]:max-h-[40dvh]',
            'lg' => '[:where(&)]:max-h-[85dvh]',
            default => '[:where(&)]:max-h-[65dvh]',
        },
        default => match ($size) {
            'sm' => 'w-[calc(100vw-3rem)] [:where(&)]:max-w-xs',
            'lg' => 'w-[calc(100vw-3rem)] [:where(&)]:max-w-xl',
            default => 'w-[calc(100vw-3rem)] [:where(&)]:max-w-md',
        },
    })

    ->add(match ($side) {
        'left' => '[:where(&)]:rounded-r-shape-lg',
        'bottom' => '[:where(&)]:rounded-t-shape-lg',
        default => '[:where(&)]:rounded-l-shape-lg',
    });
@endphp

<dialog
    id="{{ $name }}"
    {{ $attributes->class($classes) }}
    closedby="{{ $dismissible ? 'any' : 'none' }}"
    data-shape-drawer
    data-shape-side="{{ $side }}"
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

    <div class="min-h-0 flex-1 overflow-y-auto" data-shape-drawer-body>{{ $slot }}</div>
</dialog>
