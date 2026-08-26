{{-- Stands in for an application's own component that happens to have a `name`.
     Nothing here knows about Shape; that is the point. --}}
@props(['name' => null])

<section data-panel>{{ $slot }}</section>
