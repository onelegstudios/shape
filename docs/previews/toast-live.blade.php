{{-- Once, in your layout. --}}
<x-shape::toaster position="bottom-right" />

<x-shape::button
    variant="primary"
    tone="success"
    onclick="dispatchEvent(new CustomEvent('shape:toast', { detail: { toast: { heading: 'Invoice sent', description: 'A copy went to billing@example.com.', tone: 'success' } } }))"
>Send a toast</x-shape::button>
