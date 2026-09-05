{{-- Once, in your layout. --}}
<x-shape::confirm />

<x-shape::button
    variant="subtle"
    tone="danger"
    icon="shape-trash"
    onclick="window.dispatchEvent(new CustomEvent('shape:confirm', { detail: { confirm: { heading: 'Delete project?', message: 'Every invoice attached to it goes too.', accept: 'Delete', tone: 'danger', then: 'deleteProject' } } }))"
>Delete project</x-shape::button>
