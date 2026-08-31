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
--}}

@props([
    'paginator',
    'simple' => false,
    'previousLabel' => 'Previous',
    'nextLabel' => 'Next',
    'label' => 'Pagination',
])

@php
$windowed = ! $simple && method_exists($paginator, 'linkCollection');

$links = $windowed ? $paginator->linkCollection()->slice(1, -1) : [];

// Every `wire:` attribute belongs on the links, where the navigation happens;
// everything else describes the nav element itself.
$wire = $attributes->whereStartsWith('wire:');

$step = (string) Shape::classes()
    ->add('inline-flex h-9 min-w-9 items-center justify-center gap-1 px-2.5 text-sm font-medium')
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
        {{ $attributes->whereDoesntStartWith('wire:')->class('flex flex-wrap items-center [:where(&)]:gap-1') }}
        role="navigation"
        aria-label="{{ $label }}"
        data-shape-pagination
        data-shape-tone="accent"
    >
        @if ($paginator->previousPageUrl())
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $enabled }}" {{ $wire }} data-shape-pagination-previous>
                <x-shape::icon.shape-prev size="sm" />{{ $previousLabel }}
            </a>
        @else
            <span class="{{ $inert }}" aria-disabled="true" data-shape-pagination-previous>
                <x-shape::icon.shape-prev size="sm" />{{ $previousLabel }}
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
                {{ $nextLabel }}<x-shape::icon.shape-next size="sm" />
            </a>
        @else
            <span class="{{ $inert }}" aria-disabled="true" data-shape-pagination-next>
                {{ $nextLabel }}<x-shape::icon.shape-next size="sm" />
            </span>
        @endif
    </nav>
@endif
