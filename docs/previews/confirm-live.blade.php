{{-- Once, in your layout. --}}
<x-shape::confirm />

<x-shape::button
    variant="subtle"
    color="danger"
    icon="trash"
    onclick="dispatchEvent(new CustomEvent('shape:confirm', { detail: { confirm: { heading: 'Delete project?', message: 'Every invoice attached to it goes too.', accept: 'Delete', color: 'danger', then: 'deleteProject' } } }))"
>Delete project</x-shape::button>
