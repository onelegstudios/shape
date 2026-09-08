@php
    // Whatever your controller or Livewire component already has.
    $team = collect(['AL', 'GH', 'KJ', 'MC', 'RS', 'TB']);

    $shown = $team->take(3);
    $more = $team->count() - $shown->count();
@endphp

<x-shape::avatar.group>
    @foreach ($shown as $initials)
        <x-shape::avatar :initials="$initials" size="sm" />
    @endforeach

    @if ($more)
        <x-shape::avatar :initials="'+'.$more" :alt="$more.' more'" size="sm" />
    @endif
</x-shape::avatar.group>
