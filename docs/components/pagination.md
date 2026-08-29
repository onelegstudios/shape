# Pagination

Hand it the paginator you already have.

@docs('preview', name: 'pagination', layout: 'stack')

It renders nothing at all when there is only one page.

## Simple

Previous and next only, no numbers:

@docs('preview', name: 'pagination-simple', layout: 'stack')

`simple` is also a fallback rather than only a choice. `linkCollection()` — the
numbered window — belongs to `LengthAwarePaginator` alone, so a pager handed a
`simplePaginate()` or `cursorPaginate()` result renders previous and next
whether or not you asked for it. The alternative is a fatal on a method that
isn't there.

## Labels

@docs('preview', name: 'pagination-labels', layout: 'stack')

Words are translated at the call site. Laravel already ships
`pagination.previous` and `pagination.next`:

```blade
<x-shape::pagination
    :paginator="$invoices"
    :previous-label="__('pagination.previous')"
    :next-label="__('pagination.next')"
/>
```

Nothing here is folded, so a translation helper inside the component would
resolve per request and be perfectly correct — the reason it stays out is that a
component which translates for you is one a caller can't correct.

For the same reason there is no summary line. "Showing 1 to 10 of 120" is a
sentence, and a sentence belongs beside the component:

```blade
<div class="flex items-center justify-between">
    <x-shape::text size="sm" variant="muted">
        {{ __('Showing :first to :last of :total', [
            'first' => $invoices->firstItem(),
            'last' => $invoices->lastItem(),
            'total' => $invoices->total(),
        ]) }}
    </x-shape::text>

    <x-shape::pagination :paginator="$invoices" />
</div>
```

## Livewire

`wire:` attributes go to the links, where the navigation happens; everything
else stays on the `<nav>`:

```blade
<x-shape::pagination :paginator="$invoices" wire:navigate class="justify-end" />
```

One honest limit. This is URL-driven pagination, so it suits a page whose
current page lives in the query string — a Livewire component with a `#[Url]`
property, or a plain controller. A Livewire component paginating in place with
`WithPagination` and no URL state is better served by Livewire's own pagination
view, which posts back rather than navigating.

## It never reads the request

The paginator already knows its own URLs. Handing it over whole means this
component has no reason to look at the current request — which is what makes it
safe to render from anywhere, and what keeps the "no Shape component touches
request state" rule intact for a component whose entire subject is request
state.

## Reference

| Prop | Default | Values |
| --- | --- | --- |
| `paginator` | *required* | a paginator instance |
| `simple` | `false` | previous and next only, no numbers |
| `previous-label` | `Previous` | |
| `next-label` | `Next` | |
| `label` | `Pagination` | the navigation landmark's accessible name |

## Folding

Tier D — a plain `@blaze`, compiled but not folded.

It loops a collection the server produced this request, so there is nothing to
bake; folding it would hold one visitor's page of links in the compiled template
forever. That is also why the links are plain elements rather than nested Shape
components — everywhere else a nested component folds into its parent and costs
nothing, and here nothing folds.

See [Folding](../folding.md) and [Data display](../data.md).
