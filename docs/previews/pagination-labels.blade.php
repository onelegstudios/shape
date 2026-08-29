@php
    $invoices = new Illuminate\Pagination\LengthAwarePaginator([], 120, 10, 3, ['path' => '/invoices']);
@endphp

<x-shape::pagination
    :paginator="$invoices"
    simple
    previous-label="Newer"
    next-label="Older"
/>
