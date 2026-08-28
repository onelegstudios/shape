# Pagination

```blade
<x-shape::pagination :paginator="$invoices" />
```

## It takes the paginator, so it never reads the request

The paginator already knows its own URLs. Handing it over whole means this
component has no reason to look at the current request — which is what makes it
safe to render from anywhere, and what keeps the "no Shape component touches
request state" rule intact for a component whose entire subject is request
state.

It renders nothing at all when there is only one page.

| Prop | Default | Values |
| --- | --- | --- |
| `paginator` | required | a paginator instance |
| `simple` | `false` | previous and next only, no numbers |
| `previous-label` | `Previous` | |
| `next-label` | `Next` | |
| `label` | `Pagination` | the navigation landmark's accessible name |

## `simple` is also a fallback, not only a choice

`linkCollection()` — the numbered window — belongs to `LengthAwarePaginator`
alone. `simplePaginate()` and `cursorPaginate()` return objects that have
neither it nor a total, so a pager handed one of those renders previous and next
whether or not you asked for `simple`. That is deliberate: the alternative is a
fatal on a method that isn't there.

The framework's own Previous and Next are the first and last entries of
`linkCollection()`, already rendered and already translated. The number loop
drops them and this component draws its own, so nothing prints twice.

## Words are translated at the call site

```blade
<x-shape::pagination
    :paginator="$invoices"
    :previous-label="__('pagination.previous')"
    :next-label="__('pagination.next')"
/>
```

This is the one component in the library where the rule is a choice rather than
a consequence. Nothing here is folded, so a translation helper inside the
component would resolve per request and be perfectly correct — the reason it
stays out is that a component which translates for you is one a caller can't
correct. Laravel ships `pagination.previous` and `pagination.next`, so the call
above needs no new strings.

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

## Folding

Tier D — a plain `@blaze`, compiled but not folded.

It loops a collection the server produced this request, so there is nothing to
bake; folding it would hold one visitor's page of links in the compiled template
forever. It is the second component in the library annotated this way, after the
[toaster](toast.md).

That is also why the links are plain elements rather than nested Shape
components. Everywhere else a nested component folds into its parent and costs
nothing at render time; here nothing folds, so a component per iteration would
be a real cost in the one place that iterates.

See [Folding](../folding.md) and [Data display](../data.md).
