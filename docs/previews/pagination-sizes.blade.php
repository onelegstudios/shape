@php
    $invoices = new Illuminate\Pagination\LengthAwarePaginator([], 120, 10, 3, ['path' => '/invoices']);
@endphp

<x-shape::pagination :paginator="$invoices" size="xs" label="Extra small" />
<x-shape::pagination :paginator="$invoices" size="sm" label="Small" />
<x-shape::pagination :paginator="$invoices" label="Base" />
<x-shape::pagination :paginator="$invoices" size="lg" label="Large" />
<x-shape::pagination :paginator="$invoices" size="xl" label="Extra large" />
