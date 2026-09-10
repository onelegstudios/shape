@blaze

{{--
    The second component in the library that is compiled and not folded, and for
    the same reason the toaster is: there is nothing here to bake. It loops a
    collection the server produced this request, and a folded template would hold
    one visitor's page of links forever.

    It takes the paginator itself rather than a list of links assembled at the
    call site. The paginator already knows its URLs, so this component never
    reads the request — which is the whole reason the URLs are safe to render.

    `linkCollection()` belongs to LengthAwarePaginator alone. A simple paginator
    and a cursor paginator have neither it nor a total, so `simple` is not only a
    mode a caller opts into: it is also the mode this component falls back to
    when handed an object that cannot produce a window. The first and last
    entries of that collection are the framework's own Previous and Next, already
    rendered, so the number loop drops them and this file draws its own.

    Links are plain elements rather than nested Shape components. Everywhere else
    in the library a nested component folds into its parent and costs nothing;
    here nothing folds, so a component per iteration would be a real per-request
    cost in the one place that iterates.

    The labels are props because they are words, and words are translated at the
    call site where the locale is still resolved per request. Nothing in this
    file would break if it called a translation helper — it is not folded — but
    a component that translates for you is one a caller cannot correct. For the
    same reason there is no summary line: "showing 1 to 10 of 120" is a sentence,
    and a sentence belongs beside the component rather than inside it.

    One honest limit for the docs: this is URL-driven pagination. A Livewire
    component paginating in place with `WithPagination` is better served by
    Livewire's own view than by putting `wire:navigate` on these.

    `size` moves every step in the row together — the height, the width one digit
    keeps, the type and the chevrons — because they are one control repeated, and
    a pager is the one place in a library where a step out of proportion is
    repeated fifteen times across the page.

    The heights are the pager's own and not the button's. A page number is a
    smaller target than an action by design: a row of `h-10` steps reads as a
    toolbar under a table rather than as its pagination.
--}}

@props([
    'paginator',
    'simple' => false,
    'previousLabel' => 'Previous',
    'nextLabel' => 'Next',
    'label' => 'Pagination',
    'size' => 'base',
])

@php
$windowed = ! $simple && method_exists($paginator, 'linkCollection');

$links = $windowed ? $paginator->linkCollection()->slice(1, -1) : [];

// Every `wire:` attribute belongs on the links, where the navigation happens;
// everything else describes the nav element itself.
$wire = $attributes->whereStartsWith('wire:');

// The step's box and the type in it, the gap between steps, and the chevrons on
// the two ends. `min-w-*` matches the height at every step, so one digit is a
// square and three are a pill — the badge's arrangement, for the same reason.
[$box, $gap, $chevron] = match ($size) {
    'xs' => ['h-7 min-w-7 gap-0.5 px-1.5 text-xs', '[:where(&)]:gap-0.5', 'xs'],
    'sm' => ['h-8 min-w-8 gap-1 px-2 text-sm', '[:where(&)]:gap-1', 'xs'],
    'lg' => ['h-11 min-w-11 gap-1.5 px-3.5 text-base', '[:where(&)]:gap-1.5', 'sm'],
    'xl' => ['h-12 min-w-12 gap-2 px-4 text-lg', '[:where(&)]:gap-2', 'base'],
    default => ['h-9 min-w-9 gap-1 px-2.5 text-sm', '[:where(&)]:gap-1', 'sm'],
};

$step = (string) Shape::classes()
    ->add('inline-flex items-center justify-center font-medium')
    ->add($box)
    ->add('[:where(&)]:rounded-shape')
    ->add('focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--shape-ring)]');

$enabled = (string) Shape::classes($step)
    ->add('text-[color:var(--shape-fg-muted)]')
    ->add('hover:bg-[var(--shape-tone-tint)] hover:text-[color:var(--shape-tone-ink)]');

$inert = (string) Shape::classes($step)
    ->add('pointer-events-none text-[color:var(--shape-fg-muted)] opacity-50');

$current = (string) Shape::classes($step)
    ->add('bg-[var(--shape-tone-tint)] text-[color:var(--shape-tone-ink)]');
@endphp

@if ($paginator->hasPages())
    <nav
        {{ $attributes->whereDoesntStartWith('wire:')->class(Shape::classes('flex flex-wrap items-center')->add($gap)) }}
        role="navigation"
        aria-label="{{ $label }}"
        data-shape-pagination
        data-shape-size="{{ $size }}"
        data-shape-tone="brand"
    >
        @if ($paginator->previousPageUrl())
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $enabled }}" {{ $wire }} data-shape-pagination-previous>
                <x-shape::icon.shape-prev :size="$chevron" />{{ $previousLabel }}
            </a>
        @else
            <span class="{{ $inert }}" aria-disabled="true" data-shape-pagination-previous>
                <x-shape::icon.shape-prev :size="$chevron" />{{ $previousLabel }}
            </span>
        @endif

        @foreach ($links as $link)
            @if ($link['url'] === null)
                {{-- The gap the framework wrote into the window, not a page. --}}
                <span class="{{ $inert }}" aria-hidden="true">{{ $link['label'] }}</span>
            @elseif ($link['active'])
                <span class="{{ $current }}" aria-current="page" data-shape-pagination-current>{{ $link['label'] }}</span>
            @else
                <a href="{{ $link['url'] }}" class="{{ $enabled }}" {{ $wire }}>{{ $link['label'] }}</a>
            @endif
        @endforeach

        @if ($paginator->nextPageUrl())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $enabled }}" {{ $wire }} data-shape-pagination-next>
                {{ $nextLabel }}<x-shape::icon.shape-next :size="$chevron" />
            </a>
        @else
            <span class="{{ $inert }}" aria-disabled="true" data-shape-pagination-next>
                {{ $nextLabel }}<x-shape::icon.shape-next :size="$chevron" />
            </span>
        @endif
    </nav>
@endif
